<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\PaymentMethod;
use App\Services\CartService;
use App\Services\OrderService;
use DomainException;
use Illuminate\Http\Request;

class CheckoutController extends Controller
{
    public function __construct(private CartService $cart, private OrderService $orders) {}

    public function show(Request $request)
    {
        // Belum login -> arahkan ke tab DAFTAR, lalu kembali ke checkout setelah selesai
        if (!$request->user()) {
            session(['url.intended' => route('checkout')]);
            return redirect()->route('login', ['mode' => 'register', 'from' => 'checkout']);
        }

        $summary = $this->cart->summary();
        if ($summary['count'] === 0) {
            return redirect(url('/') . '#koleksi')
                ->with('error', $summary['notices'][0] ?? 'Keranjang belanja kamu masih kosong. Silakan pilih parfum terlebih dahulu.');
        }

        if ($summary['notices']) {
            session()->now('error', $summary['notices'][0]);   // tampil sebagai toast di halaman ini saja
        }

        return view('checkout.show', [
            'cart'     => $summary,
            'user'     => $request->user(),
            'payments' => PaymentMethod::where('is_active', true)->orderBy('id')->get(),
        ]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name'              => ['required', 'string', 'max:100'],
            'phone'             => ['required', 'regex:/^(\+?62|0)?\d{8,13}$/'],
            'address'           => ['required', 'string', 'min:10'],
            'payment_method_id' => ['required', 'exists:payment_methods,id'],
        ], ['phone.regex' => 'Nomor WhatsApp tidak valid.', 'address.min' => 'Alamat pengiriman terlalu singkat.']);

        $summary = $this->cart->summary();   // validasi ulang harga & stok
        if ($summary['count'] === 0) {
            return redirect(url('/') . '#koleksi')->with('error', $summary['notices'][0] ?? 'Keranjang kosong.');
        }
        if ($summary['notices']) {
            return redirect()->route('checkout')
                ->with('error', 'Keranjang diperbarui: ' . $summary['notices'][0] . ' Periksa kembali lalu proses ulang.');
        }

        $user = $request->user();

        try {
            $order = $this->orders->place($user, $data, $this->cart->raw());
        } catch (DomainException $e) {
            return redirect()->route('checkout')->with('error', $e->getMessage());
        }

        // simpan data terbaru sebagai default untuk checkout berikutnya
        $user->update(['name' => $data['name'], 'phone' => $data['phone'], 'address' => $data['address']]);
        $this->cart->clear();

        return redirect()->route('checkout.success', $order);
    }

    public function success(Request $request, Order $order)
    {
        abort_unless($order->user_id === $request->user()->id, 403);
        return view('checkout.success', ['order' => $order->load(['items', 'paymentMethod'])]);
    }
}
