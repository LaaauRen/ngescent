<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Services\OrderService;
use DomainException;
use Illuminate\Http\Request;

class OrderController extends Controller
{
    public function __construct(private OrderService $orders) {}

    public function updateStatus(Request $request, Order $order)
    {
        $data = $request->validate(['status' => ['required', 'integer', 'between:0,4']]);

        try {
            $order = $this->orders->updateStatus($order, (int) $data['status'], $request->user());
        } catch (DomainException $e) {
            return back()->with('error', $e->getMessage());
        }
        return back()->with('success', "Status #{$order->order_code} diubah menjadi \"" . Order::LABELS[$order->status] . '".');
    }

    public function updateResi(Request $request, Order $order)
    {
        $data = $request->validate(['tracking_number' => ['nullable', 'string', 'max:40']]);
        $order->update(['tracking_number' => trim($data['tracking_number'] ?? '') ?: null]);
        return back()->with('success', "Resi #{$order->order_code} diperbarui.");
    }

    public function destroy(Request $request, Order $order)
    {
        $this->orders->delete($order, $request->user());
        return back()->with('success', "Pesanan #{$order->order_code} dihapus.");
    }

    public function export()
    {
        $orders = Order::with(['items', 'paymentMethod'])->orderBy('id')->get();
        $filename = 'pesanan-ngescent-' . now()->format('Y-m-d') . '.csv';

        return response()->streamDownload(function () use ($orders) {
            $out = fopen('php://output', 'w');
            fwrite($out, "\xEF\xBB\xBF");   // BOM agar Excel membaca UTF-8
            fputcsv($out, ['ID', 'Tanggal', 'Nama', 'Email', 'WhatsApp', 'Alamat', 'Item', 'Metode Bayar', 'Resi', 'Status', 'Ongkir', 'Total']);
            foreach ($orders as $o) {
                fputcsv($out, [
                    $o->order_code, $o->date_label, $o->recipient_name, $o->recipient_email, '0' . $o->recipient_phone,
                    $o->shipping_address, $o->items_summary, $o->paymentMethod->name ?? '', $o->tracking_number,
                    $o->status_label, $o->shipping_fee, $o->total,
                ]);
            }
            fclose($out);
        }, $filename, ['Content-Type' => 'text/csv; charset=UTF-8']);
    }
}
