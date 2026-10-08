@extends('layouts.auth')
@section('title', 'Masuk / Daftar — NgeScent.')
@section('content')
<div class="auth-box" id="authBox" data-mode="{{ old('username') !== null || $errors->has('username') || $errors->has('name') || $errors->has('phone') || $errors->has('email') ? 'register' : $mode }}">
  <div class="text-center">
    <a href="{{ url('/') }}" class="logo">Nge<span>Scent</span>.</a>
    <span class="eyebrow">AKUN & PELACAKAN</span>
    <h2 id="authHeading">Masuk ke Akun</h2>
    <p id="authSubheading" class="subtitle">Gunakan Email/Username dan Password untuk melacak pesananmu.</p>
  </div>

  @if($fromCheckout)
    <div class="checkout-alert-banner">
      <i class="fa-solid fa-circle-exclamation"></i>
      <span>Silakan daftar terlebih dahulu untuk melanjutkan pembayaran pesananmu.</span>
    </div>
  @endif

  @if($errors->any())
    <div class="checkout-alert-banner" style="background:rgba(185,28,28,.08);border-color:#b91c1c;color:#b91c1c;">
      <i class="fa-solid fa-circle-exclamation"></i>
      <span>{{ $errors->first() }}</span>
    </div>
  @endif

  <div class="auth-tabs">
    <button type="button" id="tabBtnLogin" class="tab-btn active" data-auth-mode="login">Masuk</button>
    <button type="button" id="tabBtnRegister" class="tab-btn" data-auth-mode="register">Daftar Akun Baru</button>
  </div>

  {{-- Form Masuk --}}
  <form id="formLogin" method="POST" action="{{ route('login') }}">
    @csrf
    <div class="form-group">
      <label>Email atau Username</label>
      <input type="text" name="login" value="{{ old('login') }}" placeholder="nama@gmail.com atau budisantoso" required autofocus>
    </div>
    <div class="form-group">
      <label>Password</label>
      <input type="password" name="password" placeholder="Masukkan password kamu" required>
    </div>
    <button type="submit" class="btn-submit">MASUK SEKARANG &rarr;</button>
    <div class="text-center switch-link">
      Belum punya akun? <a data-auth-mode="register">Daftar di sini</a>
    </div>
  </form>

  {{-- Form Daftar --}}
  <form id="formRegister" method="POST" action="{{ route('register') }}" style="display:none;">
    @csrf
    <div class="form-group">
      <label>Nama Lengkap</label>
      <input type="text" name="name" value="{{ old('name') }}" placeholder="Contoh: Budi Santoso" required>
    </div>
    <div class="form-group">
      <label>Username</label>
      <input type="text" name="username" value="{{ old('username') }}" placeholder="Contoh: budisantoso" required>
    </div>
    <div class="form-group">
      <label>Alamat Email</label>
      <input type="email" name="email" value="{{ old('email') }}" placeholder="nama@gmail.com" required>
    </div>
    <div class="form-group">
      <label>Nomor WhatsApp Aktif</label>
      <div class="input-wa-wrapper">
        <span class="wa-prefix">+62</span>
        <input type="tel" name="phone" value="{{ old('phone') }}" placeholder="81234567890" required>
      </div>
      <small style="font-size:.72rem;color:var(--text-muted);margin-top:.2rem;">
        Wajib aktif untuk konfirmasi kurir COD & update resi WhatsApp.
      </small>
    </div>
    <div class="form-group">
      <label>Password</label>
      <input type="password" name="password" placeholder="Minimal 6 karakter" minlength="6" required>
    </div>
    <button type="submit" class="btn-submit">DAFTAR & LANJUTKAN &rarr;</button>
    <div class="text-center switch-link">
      Sudah memiliki akun? <a data-auth-mode="login">Masuk di sini</a>
    </div>
  </form>

  {{-- Tombol "Masuk sebagai Admin" DIHAPUS: admin login lewat form yang sama (role = admin di database) --}}
  <div class="text-center">
    <a href="{{ url('/') }}" class="back-home">&larr; Kembali ke Beranda Toko</a>
  </div>
</div>
@endsection
