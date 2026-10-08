@extends('layouts.app')
@section('title', 'Pesanan Berhasil — NgeScent.')
@section('content')
@php $rp = fn ($n) => 'Rp ' . number_format($n, 0, ',', '.'); @endphp

<header class="navbar simple-nav">
  <div class="nav-container">
    <a href="{{ url('/') }}" class="logo">Nge<span>Scent</span>.</a>
    <a href="{{ route('dashboard') }}" class="link-back">Dashboard Saya</a>
  </div>
</header>

<main class="checkout-page-container">
  <div class="checkout-box" style="max-width:640px;margin:0 auto;text-align:center;">
    <span class="eyebrow">PESANAN DITERIMA</span>
    <h2>Terima kasih, {{ $order->recipient_name }}!</h2>
    <p style="margin:.6rem 0 1.4rem;color:var(--text-muted);">
      Nomor pesananmu <strong>#{{ $order->order_code }}</strong>. Kirim rincian pesanan ke admin via WhatsApp agar segera diproses.
    </p>

    <div class="order-items-mini" style="text-align:left;">
      @foreach($order->items as $item)
        <div class="mini-item">
          <img src="{{ $item->image }}" alt="{{ $item->product_title }}">
          <div><strong>{{ $item->product_title }}</strong><p>{{ $item->variant_label }} &bull; {{ $item->qty }}x &bull; {{ $rp($item->unit_price) }}</p></div>
        </div>
      @endforeach
      <div class="order-ship-row"><span>Ongkos Kirim</span><span>{{ $order->shipping_fee ? $rp($order->shipping_fee) : 'GRATIS' }}</span></div>
      <div class="order-total-row"><span>Total Tagihan ({{ $order->paymentMethod->name }})</span><strong>{{ $rp($order->total) }}</strong></div>
    </div>

    <div style="display:flex;gap:.8rem;justify-content:center;flex-wrap:wrap;margin-top:1.6rem;">
      <a class="btn btn-primary" target="_blank" rel="noopener" href="{{ $order->waNewOrderUrl() }}"><i class="fa-brands fa-whatsapp"></i> KIRIM KE WHATSAPP ADMIN</a>
      <a class="btn btn-secondary" href="{{ route('dashboard', ['order' => $order->order_code]) }}">LACAK PESANAN</a>
    </div>
  </div>
</main>
@endsection
