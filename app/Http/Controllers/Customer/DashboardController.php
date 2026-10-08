<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Services\CartService;
use App\Services\OrderService;
use DomainException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class DashboardController extends Controller
{
    public function __construct(private OrderService $orders, private CartService $cart) {}

    public function index(Request $request)
    {
        $user = $request->user();
        if ($user->isAdmin()) return redirect()->route('admin.index');

        $all = $user->orders()->with(['items', 'paymentMethod'])->latest('id')->get();

        // pesanan yang dilacak: pilihan user, atau yang aktif terbaru, atau yang terbaru
        $tracked = $all->firstWhere('order_code', $request->query('order'))
            ?? $all->first(fn ($o) => $o->is_active)
            ?? $all->first();

        $filter = $request->query('status', 'all');
        $history = match (true) {
            $filter === 'active'       => $all->filter(fn ($o) => $o->is_active),
            is_numeric($filter)        => $all->where('status', (int) $filter),
            default                    => $all,
        };

        $valid = $all->where('status', '!=', Order::CANCELLED);

        return view('customer.dashboard', [
            'user'    => $user,
            'orders'  => $all,
            'tracked' => $tracked,
            'history' => $history,
            'filter'  => $filter,
            'stats'   => [
                'orders' => $all->count(),
                'spent'  => $valid->sum(fn ($o) => $o->total),
                'active' => $all->filter(fn ($o) => $o->is_active)->count(),
            ],
        ]);
    }

    public function cancel(Request $request, Order $order)
    {
        try {
            $this->orders->cancelByCustomer($order, $request->user());
        } catch (DomainException $e) {
            return back()->with('error', $e->getMessage());
        }
        return back()->with('success', "Pesanan #{$order->order_code} dibatalkan.");
    }

    public function reorder(Request $request, Order $order)
    {
        abort_unless($order->user_id === $request->user()->id, 403);

        $this->cart->merge($order->items->whereNotNull('variant_id')->pluck('qty', 'variant_id')->all());
        $summary = $this->cart->summary();

        if ($summary['count'] === 0) {
            return back()->with('error', $summary['notices'][0] ?? 'Produk sudah tidak tersedia.');
        }
        return redirect()->route('checkout');
    }

    public function updateProfile(Request $request)
    {
        $data = $request->validate([
            'name'    => ['required', 'string', 'max:100'],
            'phone'   => ['required', 'regex:/^(\+?62|0)?\d{8,13}$/'],
            'address' => ['nullable', 'string', 'max:1000'],
        ], ['phone.regex' => 'Nomor WhatsApp tidak valid.']);

        $request->user()->update($data);
        return back()->with('success', 'Profil berhasil diperbarui.');
    }

    public function updatePassword(Request $request)
    {
        $request->validate([
            'current_password' => ['required', 'string'],
            'password'         => ['required', 'string', 'min:6'],
        ]);

        if (!Hash::check($request->current_password, $request->user()->password)) {
            return back()->with('error', 'Password lama salah.');
        }

        $request->user()->update(['password' => $request->password]);   // cast "hashed" otomatis
        return back()->with('success', 'Password berhasil diperbarui.');
    }
}
