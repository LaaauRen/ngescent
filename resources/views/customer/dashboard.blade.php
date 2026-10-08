@extends('layouts.app')
@section('title', 'Dashboard Pelanggan — NgeScent.')
@section('content')
@php
  $rp = fn ($n) => 'Rp ' . number_format($n, 0, ',', '.');
  $chip = fn ($s) => '<span class="status-chip s'.$s.'">'.\App\Models\Order::LABELS[$s].'</span>';
@endphp

<header class="navbar">
  <div class="nav-container">
    <a href="{{ url('/') }}" class="logo">Nge<span>Scent</span>.</a>
    <div class="nav-icons">
      <span class="user-greeting-pill"><i class="fa-solid fa-circle-check"></i> <strong>{{ $user->first_name }}</strong></span>
      <a href="{{ url('/') }}" class="btn btn-secondary btn-sm">Katalog Parfum</a>
      <form method="POST" action="{{ route('logout') }}" style="display:inline;">
        @csrf
        <button type="submit" class="btn-logout-link" title="Keluar"><i class="fa-solid fa-arrow-right-from-bracket"></i></button>
      </form>
    </div>
  </div>
</header>

<main class="dashboard-page-container">
  <div class="dashboard-wrapper">

    <div class="dashboard-user-card">
      <div class="user-avatar-circle">{{ mb_strtoupper(mb_substr($user->name, 0, 1)) }}</div>
      <div class="user-info-text">
        <span class="eyebrow" style="margin-bottom:0;">MEMBER AREA NGESCENT</span>
        <h1>Halo, {{ $user->name }}</h1>
        <p><i class="fa-regular fa-envelope"></i> {{ $user->email }} &bull; <i class="fa-brands fa-whatsapp"></i> {{ $user->phone ? '+62 '.$user->phone : 'Belum diisi' }}</p>
      </div>
    </div>

    <div class="dash-stats">
      <div class="stat-card"><span class="stat-label">Total Pesanan</span><strong>{{ $stats['orders'] }}</strong></div>
      <div class="stat-card"><span class="stat-label">Total Belanja</span><strong style="color:var(--terracotta);">{{ $rp($stats['spent']) }}</strong></div>
      <div class="stat-card"><span class="stat-label">Pesanan Aktif</span><strong>{{ $stats['active'] }}</strong></div>
    </div>

    <div class="dashboard-nav-tabs">
      <button type="button" class="dash-tab active" data-tab="active-order"><i class="fa-solid fa-truck-fast"></i> Lacak Pesanan</button>
      <button type="button" class="dash-tab" data-tab="order-history"><i class="fa-solid fa-clock-rotate-left"></i> Riwayat Belanja</button>
      <button type="button" class="dash-tab" data-tab="profile-info"><i class="fa-regular fa-id-card"></i> Profil & Alamat</button>
    </div>

    <div class="dashboard-content-area">

      {{-- Tab 1: Lacak Pesanan --}}
      <div class="tab-pane active" id="tab-active-order">
        @if($orders->count() > 1)
          <form method="GET" action="{{ route('dashboard') }}" class="order-picker">
            <label for="dashOrderSelect">Pilih pesanan:</label>
            <select id="dashOrderSelect" name="order" class="status-select-edit" onchange="this.form.submit()">
              @foreach($orders as $o)
                <option value="{{ $o->order_code }}" {{ $tracked && $tracked->id === $o->id ? 'selected' : '' }}>#{{ $o->order_code }} — {{ $o->status_label }}</option>
              @endforeach
            </select>
          </form>
        @endif

        @if(!$tracked)
          <div class="empty-state">
            <i class="fa-solid fa-bag-shopping"></i>
            <p>Kamu belum pernah melakukan checkout.<br>Yuk pilih parfum viral favoritmu!</p>
            <a href="{{ url('/') }}#koleksi" class="btn btn-primary btn-sm">Lihat Katalog Parfum</a>
          </div>
        @else
          @php $o = $tracked; $step = $o->status; @endphp
          <div class="order-highlight-card">
            <div class="order-head-info">
              <div><span class="label-tiny">NOMOR PESANAN &bull; {{ $o->date_label }}</span><h3>#{{ $o->order_code }}</h3></div>
              {!! $chip($step) !!}
            </div>

            <div class="shipping-quick-details">
              <div class="quick-col"><span>Ekspedisi Pengiriman:</span><strong>{{ $o->courier }}</strong></div>
              <div class="quick-col"><span>Nomor Resi Resmi:</span>
                @if($o->tracking_number)
                  <div class="resi-copy-box"><span>{{ $o->tracking_number }}</span>
                    <button type="button" data-copy="{{ $o->tracking_number }}"><i class="fa-regular fa-copy"></i> Salin</button></div>
                @else
                  <div class="resi-copy-box resi-pending"><span>Belum tersedia</span></div>
                @endif
              </div>
              <div class="quick-col"><span>Metode Pembayaran:</span><span class="badge-pay">{{ $o->paymentMethod->name }}</span></div>
            </div>

            @if($step === 0)
              <div class="cancel-banner"><i class="fa-solid fa-circle-xmark"></i> Pesanan ini telah dibatalkan. Hubungi kami jika ada kendala.</div>
            @else
              <div class="tracking-timeline">
                @foreach(\App\Models\Order::TRACK_STEPS as $i => $s)
                  @php $n = $i + 1; $cls = ($step >= 4 || $n < $step) ? 'done' : ($n === $step ? 'active' : ''); @endphp
                  <div class="timeline-step {{ $cls }}">
                    <div class="step-icon"><i class="fa-solid {{ $s['icon'] }}"></i></div>
                    <div class="step-content"><strong>{{ $s['title'] }}</strong><small>{{ $s['desc'] }}</small></div>
                  </div>
                @endforeach
              </div>
            @endif

            <div class="order-items-mini">
              <h4>Item dalam Pesanan Ini:</h4>
              @foreach($o->items as $item)
                <div class="mini-item">
                  <img src="{{ $item->image }}" alt="{{ $item->product_title }}">
                  <div><strong>{{ $item->product_title }}</strong><p>{{ $item->variant_label }} &bull; {{ $item->qty }}x &bull; {{ $rp($item->unit_price) }}</p></div>
                </div>
              @endforeach
              <div class="order-ship-row"><span>Ongkos Kirim</span><span>{{ $o->shipping_fee ? $rp($o->shipping_fee) : 'GRATIS' }}</span></div>
              <div class="order-total-row"><span>Total Tagihan</span><strong>{{ $rp($o->total) }}</strong></div>
              <div class="address-mini"><i class="fa-solid fa-location-dot"></i> {{ $o->shipping_address }}</div>
            </div>

            <div class="order-actions">
              <a class="btn btn-secondary btn-sm" target="_blank" rel="noopener"
                 href="https://wa.me/{{ config('shop.admin_wa') }}?text={{ rawurlencode("Halo NgeScent, saya ingin tanya soal pesanan #{$o->order_code}.") }}"><i class="fa-brands fa-whatsapp"></i> Tanya Penjual</a>
              @if($o->can_be_cancelled)
                <form method="POST" action="{{ route('orders.cancel', $o) }}" onsubmit="return confirm('Yakin ingin membatalkan pesanan #{{ $o->order_code }}?')" style="display:inline;">
                  @csrf
                  <button type="submit" class="btn-danger-outline">Batalkan Pesanan</button>
                </form>
              @endif
            </div>
          </div>
        @endif
      </div>

      {{-- Tab 2: Riwayat --}}
      <div class="tab-pane" id="tab-order-history">
        <form method="GET" action="{{ route('dashboard') }}" class="toolbar">
          <select name="status" class="status-select-edit" onchange="this.form.submit()">
            <option value="all"    {{ $filter === 'all' ? 'selected' : '' }}>Semua Status</option>
            <option value="active" {{ $filter === 'active' ? 'selected' : '' }}>Sedang Diproses</option>
            <option value="4"      {{ $filter === '4' ? 'selected' : '' }}>Selesai</option>
            <option value="0"      {{ $filter === '0' ? 'selected' : '' }}>Dibatalkan</option>
          </select>
        </form>

        <div class="history-list">
          @forelse($history as $o)
            <div class="history-item">
              <div class="history-head">
                <strong>#{{ $o->order_code }} &bull; {{ $o->date_label }}</strong>
                <span>{!! $chip($o->status) !!}</span>
              </div>
              <p style="font-size:.88rem;margin-bottom:.6rem;">{{ $o->items_summary }}</p>
              <div class="history-foot">
                <span style="font-size:.8rem;color:var(--text-muted);">{{ $o->paymentMethod->name }} &bull; Resi J&T: <strong>{{ $o->tracking_number ?: 'Belum tersedia' }}</strong></span>
                <strong style="color:var(--terracotta);">{{ $rp($o->total) }}</strong>
              </div>
              <div class="history-actions">
                <a class="btn btn-secondary btn-sm" href="{{ route('dashboard', ['order' => $o->order_code]) }}">Lacak</a>
                <form method="POST" action="{{ route('orders.reorder', $o) }}" style="display:inline;">
                  @csrf
                  <button type="submit" class="btn btn-primary btn-sm">Beli Lagi</button>
                </form>
              </div>
            </div>
          @empty
            <div class="empty-state"><i class="fa-solid fa-clock-rotate-left"></i><p>Belum ada riwayat belanja untuk filter ini.</p></div>
          @endforelse
        </div>
      </div>

      {{-- Tab 3: Profil --}}
      <div class="tab-pane" id="tab-profile-info">
        <form class="profile-card" method="POST" action="{{ route('profile.update') }}">
          @csrf @method('PUT')
          <h4>Data Diri & Alamat Pengiriman</h4>
          <div class="form-grid">
            <div class="form-group"><label>Nama Lengkap</label><input type="text" name="name" value="{{ old('name', $user->name) }}" required></div>
            <div class="form-group"><label>Nomor WhatsApp</label><input type="tel" name="phone" value="{{ old('phone', $user->phone ? '0'.$user->phone : '') }}" placeholder="08xxxxxxxxxx" required></div>
          </div>
          <div class="form-group"><label>Email (tidak dapat diubah)</label><input type="email" value="{{ $user->email }}" disabled></div>
          <div class="form-group"><label>Alamat Pengiriman Utama</label>
            <textarea name="address" rows="3" placeholder="Nama jalan, nomor rumah, RT/RW, kelurahan, kecamatan, kota, kode pos">{{ old('address', $user->address) }}</textarea></div>
          <button type="submit" class="btn btn-primary btn-sm">Simpan Perubahan</button>
          <small class="text-muted" style="display:block;margin-top:.8rem;"><i class="fa-solid fa-circle-check"></i> Alamat otomatis terisi saat checkout berikutnya.</small>
        </form>

        <form class="profile-card" style="margin-top:1.5rem;" method="POST" action="{{ route('profile.password') }}">
          @csrf @method('PUT')
          <h4>Ganti Password</h4>
          <div class="form-grid">
            <div class="form-group"><label>Password Lama</label><input type="password" name="current_password" required></div>
            <div class="form-group"><label>Password Baru (min. 6 karakter)</label><input type="password" name="password" minlength="6" required></div>
          </div>
          <button type="submit" class="btn btn-secondary btn-sm">Perbarui Password</button>
        </form>
      </div>

    </div>
  </div>
</main>
@endsection
