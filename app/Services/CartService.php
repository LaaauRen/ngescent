<?php

namespace App\Services;

use App\Models\ProductVariant;
use DomainException;

/**
 * Keranjang disimpan di SESSION server: hanya [variant_id => qty].
 * Harga, judul, gambar & stok SELALU diambil ulang dari database.
 */
class CartService
{
    private const KEY = 'cart';

    public function raw(): array
    {
        return session(self::KEY, []);
    }

    public function clear(): void
    {
        session()->forget(self::KEY);
    }

    public function add(int $variantId, int $qty = 1): void
    {
        $variant = ProductVariant::with('product')->find($variantId);
        if (!$variant || !$variant->product || !$variant->product->is_active) {
            throw new DomainException('Produk ini sudah tidak tersedia.');
        }

        $cart = $this->raw();
        $new = ($cart[$variantId] ?? 0) + $qty;

        if ($new > $variant->stock) {
            throw new DomainException($variant->stock > 0
                ? "Stok {$variant->label} tersisa {$variant->stock}."
                : 'Maaf, stok sudah habis.');
        }

        $cart[$variantId] = $new;
        session([self::KEY => $cart]);
    }

    /** Ubah jumlah dengan delta (+1 / -1). Qty <= 0 menghapus item. */
    public function change(int $variantId, int $delta): void
    {
        $cart = $this->raw();
        if (!isset($cart[$variantId])) return;

        if ($delta > 0) {
            $this->add($variantId, $delta);
            return;
        }

        $cart[$variantId] += $delta;
        if ($cart[$variantId] <= 0) unset($cart[$variantId]);
        session([self::KEY => $cart]);
    }

    public function remove(int $variantId): void
    {
        $cart = $this->raw();
        unset($cart[$variantId]);
        session([self::KEY => $cart]);
    }

    /** Gabungkan item (dipakai "Beli Lagi"); item yang tak tersedia dilewati. */
    public function merge(array $items): void
    {
        foreach ($items as $variantId => $qty) {
            try {
                $this->add((int) $variantId, (int) $qty);
            } catch (DomainException $e) {
                // dilewati; summary() akan memberi tahu sisanya
            }
        }
    }

    /**
     * Ringkasan keranjang terbaru. Item yang sudah tidak valid dibuang / disesuaikan
     * dan alasannya dikembalikan di 'notices' (menggantikan syncCartWithCatalog()).
     */
    public function summary(): array
    {
        $cart = $this->raw();
        $variants = ProductVariant::with('product')->whereIn('id', array_keys($cart))->get()->keyBy('id');

        $items = [];
        $notices = [];
        $clean = [];

        foreach ($cart as $variantId => $qty) {
            $v = $variants->get($variantId);
            $p = $v?->product;

            if (!$v || !$p || !$p->is_active) {
                $notices[] = 'Satu produk sudah tidak tersedia dan dihapus dari keranjang.';
                continue;
            }
            $title = "{$p->name} ({$p->brand})";

            if ($v->stock <= 0) {
                $notices[] = "{$title} ({$v->label}) stok habis dan dihapus dari keranjang.";
                continue;
            }
            if ($qty > $v->stock) {
                $notices[] = "Jumlah {$title} ({$v->label}) disesuaikan dengan stok tersisa ({$v->stock}).";
                $qty = $v->stock;
            }

            $clean[$variantId] = $qty;
            $items[] = [
                'variant_id' => $v->id,
                'product_id' => $p->id,
                'title'      => $title,
                'size'       => $v->label,
                'price'      => $v->price,
                'image'      => $p->image_src,
                'qty'        => $qty,
                'stock'      => $v->stock,
                'subtotal'   => $v->price * $qty,
            ];
        }

        if ($clean !== $cart) session([self::KEY => $clean]);

        $subtotal = array_sum(array_column($items, 'subtotal'));
        $threshold = config('shop.free_shipping_threshold');
        $shipping = ($subtotal <= 0 || $subtotal >= $threshold) ? 0 : config('shop.shipping_fee');

        return [
            'items'     => $items,
            'count'     => array_sum(array_column($items, 'qty')),
            'subtotal'  => $subtotal,
            'shipping'  => $shipping,
            'total'     => $subtotal + $shipping,
            'threshold' => $threshold,
            'notices'   => array_values(array_unique($notices)),
        ];
    }
}
