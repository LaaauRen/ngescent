<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PanelController extends Controller
{
    public function index(Request $request)
    {
        $low = config('shop.low_stock_limit');

        // ---- Pesanan (filter: q, status) ----
        $ordersQuery = Order::with(['items', 'paymentMethod'])->latest('id');
        if (($sf = $request->query('status', 'all')) !== 'all') {
            $ordersQuery->where('status', (int) $sf);
        }
        if ($q = trim((string) $request->query('q'))) {
            $ordersQuery->where(fn ($w) => $w
                ->where('order_code', 'like', "%{$q}%")
                ->orWhere('recipient_name', 'like', "%{$q}%")
                ->orWhere('recipient_email', 'like', "%{$q}%")
                ->orWhere('tracking_number', 'like', "%{$q}%"));
        }

        // ---- Produk (filter: pq, cat) ----
        $productsQuery = Product::with(['variants', 'category'])->latest('id');
        if ($pq = trim((string) $request->query('pq'))) {
            $productsQuery->where(fn ($w) => $w
                ->where('name', 'like', "%{$pq}%")->orWhere('brand', 'like', "%{$pq}%")->orWhere('notes', 'like', "%{$pq}%"));
        }
        if (($cat = $request->query('cat', 'all')) !== 'all') {
            $productsQuery->whereHas('category', fn ($c) => $c->where('slug', $cat));
        }

        // ---- Statistik ----
        $revenue = Order::where('status', '!=', Order::CANCELLED)->sum(DB::raw('subtotal + shipping_fee'));
        $lowStockProducts = Product::whereHas('variants', fn ($v) => $v->where('stock', '<=', $low))->count();

        // ---- Laporan ----
        $topProducts = OrderItem::join('orders', 'orders.id', '=', 'order_items.order_id')
            ->where('orders.status', '!=', Order::CANCELLED)
            ->groupBy('order_items.product_title')
            ->selectRaw('order_items.product_title as label, SUM(order_items.qty) as total')
            ->orderByDesc('total')->limit(6)->get();

        $payments = Order::join('payment_methods', 'payment_methods.id', '=', 'orders.payment_method_id')
            ->where('orders.status', '!=', Order::CANCELLED)
            ->groupBy('payment_methods.name')
            ->selectRaw('payment_methods.name as label, SUM(orders.subtotal + orders.shipping_fee) as total')
            ->orderByDesc('total')->get();

        $statuses = Order::selectRaw('status, COUNT(*) as total')->groupBy('status')->get()
            ->map(fn ($r) => (object) ['label' => Order::LABELS[$r->status], 'total' => $r->total]);

        $lowStock = ProductVariant::with('product')->where('stock', '<=', $low)
            ->whereHas('product')->orderBy('stock')->limit(8)->get();

        $customers = User::customers()->with('orders:id,user_id,status,subtotal,shipping_fee')->latest('id')->get();

        return view('admin.index', [
            'orders'      => $ordersQuery->get(),
            'products'    => $productsQuery->get(),
            'categories'  => Category::orderBy('sort_order')->get(),
            'customers'   => $customers,
            'stats'       => [
                'orders'    => Order::count(),
                'revenue'   => (int) $revenue,
                'pending'   => Order::whereBetween('status', [1, 3])->count(),
                'customers' => $customers->count(),
                'products'  => Product::count(),
                'low'       => $lowStockProducts,
            ],
            'report'      => compact('topProducts', 'payments', 'statuses', 'lowStock'),
            'filters'     => [
                'q' => $q, 'status' => $sf, 'pq' => $pq, 'cat' => $cat,
            ],
        ]);
    }
}
