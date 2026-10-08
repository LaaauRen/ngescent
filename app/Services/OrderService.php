<?php

namespace App\Services;

use App\Models\Order;
use App\Models\OrderStatusLog;
use App\Models\ProductVariant;
use App\Models\StockMovement;
use App\Models\User;
use DomainException;
use Illuminate\Support\Facades\DB;

/**
 * Semua perubahan pesanan + stok terjadi di sini dalam SATU transaksi.
 * Menggantikan applyStock()/stockShortage() di script.js dan sp_update_order_status di database.sql.
 */
class OrderService
{
    /**
     * Buat pesanan dari keranjang ([variant_id => qty]).
     * Harga & stok dibaca ulang dari DB dengan lockForUpdate -> aman dari 2 pembeli bersamaan.
     *
     * @throws DomainException bila stok / produk tidak valid
     */
    public function place(User $user, array $data, array $items): Order
    {
        if (empty($items)) {
            throw new DomainException('Keranjang belanja kosong.');
        }

        return DB::transaction(function () use ($user, $data, $items) {
            $variants = ProductVariant::with('product')
                ->whereIn('id', array_keys($items))
                ->orderBy('id')
                ->lockForUpdate()
                ->get()
                ->keyBy('id');

            $subtotal = 0;
            $lines = [];

            foreach ($items as $variantId => $qty) {
                $v = $variants->get($variantId);
                if (!$v || !$v->product || !$v->product->is_active) {
                    throw new DomainException('Ada produk di keranjang yang sudah tidak tersedia.');
                }
                if ($v->stock < $qty) {
                    throw new DomainException("Stok {$v->product->name} ({$v->label}) tersisa {$v->stock}.");
                }
                $subtotal += $v->price * $qty;
                $lines[] = [$v, (int) $qty];
            }

            $shipping = $subtotal >= config('shop.free_shipping_threshold') ? 0 : config('shop.shipping_fee');

            $order = Order::create([
                'order_code'        => Order::generateCode(),
                'user_id'           => $user->id,
                'recipient_name'    => $data['name'],
                'recipient_phone'   => $data['phone'],
                'recipient_email'   => $user->email,
                'shipping_address'  => $data['address'],
                'payment_method_id' => $data['payment_method_id'],
                'status'            => Order::CONFIRMED,
                'courier'           => config('shop.courier'),
                'subtotal'          => $subtotal,
                'shipping_fee'      => $shipping,
            ]);

            foreach ($lines as [$v, $qty]) {
                $v->decrement('stock', $qty);

                $order->items()->create([
                    'product_id'    => $v->product_id,
                    'variant_id'    => $v->id,
                    'product_title' => "{$v->product->name} ({$v->product->brand})",
                    'variant_label' => $v->label,
                    'image'         => $v->product->image_src,
                    'unit_price'    => $v->price,
                    'qty'           => $qty,
                ]);

                StockMovement::create([
                    'variant_id' => $v->id, 'order_id' => $order->id,
                    'change_qty' => -$qty, 'reason' => 'order', 'created_by' => $user->id,
                ]);
            }

            OrderStatusLog::create([
                'order_id' => $order->id, 'old_status' => null,
                'new_status' => Order::CONFIRMED, 'changed_by' => $user->id,
            ]);

            return $order->load(['items', 'paymentMethod']);
        });
    }

    /**
     * Ubah status. Aturan stok (sama dengan versi frontend & stored procedure):
     *  - aktif -> dibatalkan : stok dikembalikan
     *  - dibatalkan -> aktif : stok harus cukup, lalu dipotong lagi
     *  - perpindahan lain (mis. ke Selesai) tidak menyentuh stok
     *
     * @throws DomainException bila stok tidak cukup saat mengaktifkan kembali
     */
    public function updateStatus(Order $order, int $newStatus, ?User $actor = null): Order
    {
        if ($newStatus < 0 || $newStatus > 4) {
            throw new DomainException('Status tidak valid.');
        }

        return DB::transaction(function () use ($order, $newStatus, $actor) {
            $order = Order::whereKey($order->id)->lockForUpdate()->firstOrFail();
            $old = (int) $order->status;
            if ($old === $newStatus) return $order;

            if ($old === Order::CANCELLED && $newStatus !== Order::CANCELLED) {
                $this->moveStock($order, -1, 'reactivate', $actor);
            }
            if ($old !== Order::CANCELLED && $newStatus === Order::CANCELLED) {
                $this->moveStock($order, +1, 'cancel', $actor);
            }

            $order->update([
                'status'       => $newStatus,
                'cancelled_at' => $newStatus === Order::CANCELLED ? now() : null,
                'completed_at' => $newStatus === Order::DONE ? now() : null,
            ]);

            OrderStatusLog::create([
                'order_id' => $order->id, 'old_status' => $old,
                'new_status' => $newStatus, 'changed_by' => $actor?->id,
            ]);

            return $order;
        });
    }

    /** Pelanggan membatalkan pesanannya sendiri (hanya status 1-2). */
    public function cancelByCustomer(Order $order, User $customer): Order
    {
        if ($order->user_id !== $customer->id) {
            throw new DomainException('Pesanan tidak ditemukan.');
        }
        if (!$order->can_be_cancelled) {
            throw new DomainException('Pesanan sudah dikirim / selesai, tidak bisa dibatalkan.');
        }
        return $this->updateStatus($order, Order::CANCELLED, $customer);
    }

    /** Hapus permanen; stok dikembalikan bila pesanan belum selesai/dibatalkan. */
    public function delete(Order $order, ?User $actor = null): void
    {
        DB::transaction(function () use ($order, $actor) {
            $order = Order::whereKey($order->id)->lockForUpdate()->firstOrFail();
            if (in_array($order->status, [Order::CONFIRMED, Order::PACKING, Order::SHIPPED], true)) {
                $this->moveStock($order, +1, 'cancel', $actor);
            }
            $order->delete();
        });
    }

    private function moveStock(Order $order, int $sign, string $reason, ?User $actor): void
    {
        $items = $order->items()->whereNotNull('variant_id')->orderBy('variant_id')->get();

        foreach ($items as $item) {
            $variant = ProductVariant::lockForUpdate()->find($item->variant_id);
            if (!$variant) continue;

            $new = $variant->stock + $sign * $item->qty;
            if ($new < 0) {
                throw new DomainException("Stok tidak cukup untuk mengaktifkan lagi: {$item->product_title} ({$item->variant_label}).");
            }
            $variant->update(['stock' => $new]);

            StockMovement::create([
                'variant_id' => $variant->id, 'order_id' => $order->id,
                'change_qty' => $sign * $item->qty, 'reason' => $reason, 'created_by' => $actor?->id,
            ]);
        }
    }
}
