@extends('layouts.app')
@section('title', 'Checkout Pesanan — NgeScent.')
@section('content')
@php
  $desc = [
    'QRIS' => ['QRIS (BCA Mobile, GoPay, ShopeePay, Dana)', 'Scan barcode langsung dari HP Anda. Terverifikasi instan tanpa cek mutasi.', 'Bebas Biaya Admin'],
    'Transfer Virtual Account' => ['Transfer Virtual Account (BCA, Mandiri, BRI, BNI)', 'Kode pembayaran otomatis terhubung 24 jam tanpa perlu kirim bukti transfer.', null],
    'COD (Bayar di Tempat)' => ['COD (Bayar Tunai ke Kurir)', 'Bayar tunai ke kurir saat paket parfum sampai di rumah. Wajib nomor WA aktif.', null],
  ];
  $rp = fn ($n) => 'Rp ' . number_format($n, 0, ',', '.');
  $selected = (int) old('payment_method_id', $payments->first()->id ?? 0);
@endphp

<header class="navbar simple-nav">
  <div class="nav-container">
    <a href="{{ url('/') }}" class="logo">Nge<span>Scent</span>.</a>
    <a href="{{ url('/') }}" class="link-back"><i class="fa-solid fa-arrow-left"></i> Lanjut Belanja</a>
  </div>
</header>

<main class="checkout-page-container">
  <div class="checkout-layout">

    <section class="checkout-form-col">
      <div class="checkout-box">
        <span class="eyebrow">LANGKAH 1 DARI 2</span>
        <h2>Informasi Pengiriman & Akun</h2>

        <div class="google-auth-notice">
          <i class="fa-regular fa-circle-user"></i>
          <div><span>Akun terhubung: <strong>{{ $user->email }}</strong>@if($user->phone) (+62 {{ $user->phone }})@endif</span></div>
        </div>

        <form id="checkoutForm" method="POST" action="{{ route('checkout.store') }}">
          @csrf
          <div class="form-grid">
            <div class="form-group">
              <label>Nama Lengkap Penerima</label>
              <input type="text" name="name" value="{{ old('name', $user->name) }}" placeholder="Contoh: Budi Santoso" required>
            </div>
            <div class="form-group">
              <label>Nomor WhatsApp Aktif</label>
              <input type="tel" name="phone" value="{{ old('phone', $user->phone ? '0'.$user->phone : '') }}" placeholder="08xxxxxxxxxx" required>
            </div>
          </div>

          <div class="form-group">
            <label>Alamat Lengkap Pengiriman</label>
            <textarea name="address" rows="3" placeholder="Nama jalan, nomor rumah, RT/RW, kelurahan, kecamatan, kota/kabupaten, kode pos" required>{{ old('address', $user->address) }}</textarea>
          </div>

          <div class="payment-step-wrap">
            <span class="eyebrow">LANGKAH 2 DARI 2</span>
            <h2>Pilih Metode Pembayaran</h2>

            <div class="payment-options">
              @foreach($payments as $pm)
                @php [$title, $text, $badge] = $desc[$pm->name] ?? [$pm->name, '', null]; @endphp
                <label class="payment-card {{ $selected === $pm->id ? 'active' : '' }}">
                  <input type="radio" name="payment_method_id" value="{{ $pm->id }}" {{ $selected === $pm->id ? 'checked' : '' }}>
                  <div class="payment-info">
                    <div class="payment-title">
                      <strong>{{ $title }}</strong>
                      @if($badge)<span class="badge-recom">{{ $badge }}</span>@endif
                    </div>
                    <p>{{ $text }}</p>
                  </div>
                </label>
              @endforeach
            </div>
          </div>

          <button type="submit" class="btn btn-primary btn-block btn-lg">PROSES PESANAN SEKARANG &rarr;</button>
        </form>
      </div>
    </section>

    <aside class="checkout-summary-col">
      <div class="summary-card">
        <h3>Ringkasan Pesanan</h3>
        <div class="review-items-list">
          @foreach($cart['items'] as $item)
            <div class="review-item">
              <img src="{{ $item['image'] }}" alt="{{ $item['title'] }}">
              <div class="review-item-info">
                <strong>{{ $item['title'] }}</strong>
                <span>{{ $item['size'] }} &bull; {{ $item['qty'] }}x</span>
              </div>
              <span class="review-item-price">{{ $rp($item['subtotal']) }}</span>
            </div>
          @endforeach
        </div>

        <div class="summary-divider"></div>

        <div class="summary-line"><span>Subtotal Produk</span><strong>{{ $rp($cart['subtotal']) }}</strong></div>
        <div class="summary-line">
          <span>Ongkos Kirim</span>
          <strong class="{{ $cart['shipping'] === 0 ? 'free-badge' : '' }}">{{ $cart['shipping'] === 0 ? 'GRATIS' : $rp($cart['shipping']) }}</strong>
        </div>
        <div class="summary-line grand-total"><span>Total Tagihan</span><strong>{{ $rp($cart['total']) }}</strong></div>

        <div class="checkout-guarantee">
          <i class="fa-solid fa-shield-halved"></i>
          <div>
            <strong>Garansi Keamanan NgeScent</strong>
            <p>Paket pecah di jalan langsung diganti baru. 100% original bergaransi batch code resmi.</p>
          </div>
        </div>
      </div>
    </aside>

  </div>
</main>
@endsection
