@extends('layouts.app')
@section('title', 'Admin Portal — NgeScent.')
@section('body_class', 'admin-body')
@section('content')
@php
  $rp = fn ($n) => 'Rp ' . number_format($n, 0, ',', '.');
  $low = config('shop.low_stock_limit');
  $chip = fn ($s) => '<span class="status-chip s'.$s.'">'.\App\Models\Order::LABELS[$s].'</span>';
@endphp

<main class="admin-dashboard-container">
  <div class="admin-topbar">
    <div>
      <span class="eyebrow" style="margin-bottom:0;">OWNER PORTAL</span>
      <h2>Panel Kendali Toko</h2>
      <p class="text-muted" style="font-size:.85rem;">Login Admin Toko: <strong>{{ auth()->user()->email }}</strong></p>
    </div>
    <div class="admin-actions">
      <a href="{{ url('/') }}" target="_blank" class="btn btn-secondary btn-sm"><i class="fa-solid fa-arrow-up-right-from-square"></i> Lihat Toko Live</a>
      <form method="POST" action="{{ route('logout') }}" style="display:inline;">
        @csrf
        <button type="submit" class="btn btn-primary btn-sm"><i class="fa-solid fa-power-off"></i> Keluar</button>
      </form>
    </div>
  </div>

  <div class="admin-stats-grid">
    <div class="stat-card"><span class="stat-label">Total Pesanan</span><strong>{{ $stats['orders'] }}</strong></div>
    <div class="stat-card"><span class="stat-label">Omzet (tanpa batal)</span><strong style="color:var(--terracotta);">{{ $rp($stats['revenue']) }}</strong></div>
    <div class="stat-card"><span class="stat-label">Perlu Diproses</span><strong style="color:#2e7d32;">{{ $stats['pending'] }} Pesanan</strong></div>
    <div class="stat-card"><span class="stat-label">Pelanggan Terdaftar</span><strong>{{ $stats['customers'] }}</strong></div>
    <div class="stat-card"><span class="stat-label">Katalog</span><strong style="font-size:1.15rem;">{{ $stats['products'] }} Produk{{ $stats['low'] ? ' · '.$stats['low'].' stok menipis' : '' }}</strong></div>
  </div>

  <div class="dashboard-nav-tabs">
    <button type="button" class="dash-tab active" data-tab="admin-orders"><i class="fa-solid fa-receipt"></i> Pesanan</button>
    <button type="button" class="dash-tab" data-tab="admin-products"><i class="fa-solid fa-spray-can-sparkles"></i> Produk</button>
    <button type="button" class="dash-tab" data-tab="admin-customers"><i class="fa-solid fa-users"></i> Pelanggan</button>
    <button type="button" class="dash-tab" data-tab="admin-reports"><i class="fa-solid fa-chart-column"></i> Laporan Penjualan</button>
  </div>

  {{-- ============ Tab Pesanan ============ --}}
  <div class="tab-pane active" id="tab-admin-orders">
    <div class="admin-table-card">
      <div class="table-header">
        <h3>Daftar Transaksi Pembeli</h3>
        <p>Ubah status, ketik nomor resi, atau klik WA untuk kabari pembeli.</p>
      </div>

      <form method="GET" action="{{ route('admin.index') }}" class="toolbar toolbar-pad">
        <input type="search" name="q" value="{{ $filters['q'] }}" class="toolbar-input" placeholder="Cari ID, nama, email, atau resi...">
        <select name="status" class="status-select-edit" onchange="this.form.submit()">
          <option value="all">Semua Status</option>
          @foreach([1 => 'Dikonfirmasi', 2 => 'Dikemas', 3 => 'Di Jalan', 4 => 'Selesai', 0 => 'Dibatalkan'] as $v => $t)
            <option value="{{ $v }}" {{ (string) $filters['status'] === (string) $v ? 'selected' : '' }}>{{ $t }}</option>
          @endforeach
        </select>
        <button type="submit" class="btn btn-secondary btn-sm">Cari</button>
        <a href="{{ route('admin.orders.export') }}" class="btn btn-secondary btn-sm"><i class="fa-solid fa-file-csv"></i> Ekspor CSV</a>
      </form>

      <div class="table-responsive">
        <table class="admin-table">
          <thead>
            <tr>
              <th>ID Order & Waktu</th><th>Penerima & WhatsApp</th><th>Detail Item & Tagihan</th>
              <th>Metode Bayar</th><th>Nomor Resi (J&T)</th><th>Status Paket</th><th>Aksi</th>
            </tr>
          </thead>
          <tbody>
            @forelse($orders as $o)
              <tr>
                <td><strong>#{{ $o->order_code }}</strong><br><small class="text-muted">{{ $o->date_label }}</small></td>
                <td><strong>{{ $o->recipient_name }}</strong><br>
                  <small class="text-muted">{{ $o->recipient_email }}</small><br>
                  <small class="text-muted">+62 {{ $o->recipient_phone }}</small><br>
                  <small style="font-size:.75rem;color:#888;">{{ \Illuminate\Support\Str::limit($o->shipping_address, 35) }}</small></td>
                <td>
                  <div style="font-size:.8rem;margin-bottom:.3rem;">
                    @foreach($o->items as $i){{ $i->product_title }} ({{ $i->variant_label }}) x{{ $i->qty }}@if(!$loop->last)<br>@endif @endforeach
                  </div>
                  <strong style="color:var(--terracotta);">{{ $rp($o->total) }}</strong>
                </td>
                <td><span class="badge-pay">{{ $o->paymentMethod->name }}</span></td>
                <td>
                  <form method="POST" action="{{ route('admin.orders.resi', $o) }}">
                    @csrf @method('PATCH')
                    <input type="text" name="tracking_number" class="resi-input-edit" value="{{ $o->tracking_number }}"
                           placeholder="Isi nomor resi" maxlength="40" onchange="this.form.submit()" title="Ketik untuk mengubah nomor resi">
                  </form>
                </td>
                <td>
                  <form method="POST" action="{{ route('admin.orders.status', $o) }}">
                    @csrf @method('PATCH')
                    <select name="status" class="status-select-edit" onchange="this.form.submit()">
                      @foreach([1 => '1. Dikonfirmasi', 2 => '2. Dikemas', 3 => '3. Di Jalan (Kurir)', 4 => '4. Selesai / Diterima', 0 => 'Dibatalkan'] as $v => $t)
                        <option value="{{ $v }}" {{ $o->status === $v ? 'selected' : '' }}>{{ $t }}</option>
                      @endforeach
                    </select>
                  </form>
                </td>
                <td>
                  <div class="row-actions">
                    <a class="btn-send-wa" target="_blank" rel="noopener" href="{{ $o->waUpdateUrl() }}"><i class="fa-brands fa-whatsapp"></i> WA</a>
                    <button type="button" class="btn-icon" title="Detail" data-order-detail="{{ $o->id }}"><i class="fa-regular fa-eye"></i></button>
                    <form method="POST" action="{{ route('admin.orders.destroy', $o) }}" style="display:inline;"
                          onsubmit="return confirm('Hapus permanen pesanan #{{ $o->order_code }}? Tindakan ini tidak bisa dibatalkan.')">
                      @csrf @method('DELETE')
                      <button type="submit" class="btn-icon danger" title="Hapus"><i class="fa-regular fa-trash-can"></i></button>
                    </form>
                  </div>

                  <template id="tpl-order-{{ $o->id }}">
                    <span class="label-tiny">DETAIL PESANAN &bull; {{ $o->date_label }}</span>
                    <h3 style="font-family:var(--font-serif);font-size:1.6rem;margin-bottom:.5rem;">#{{ $o->order_code }} {!! $chip($o->status) !!}</h3>
                    <div class="modal-section"><strong>Penerima</strong>
                      <p>{{ $o->recipient_name }}<br>{{ $o->recipient_email }}<br>+62 {{ $o->recipient_phone }}</p></div>
                    <div class="modal-section"><strong>Alamat Pengiriman</strong><p>{{ $o->shipping_address }}</p></div>
                    <div class="modal-section"><strong>Pembayaran & Resi</strong><p>{{ $o->paymentMethod->name }} &bull; {{ $o->tracking_number ?: 'Resi belum diisi' }}</p></div>
                    <div class="modal-section"><strong>Item</strong>
                      @foreach($o->items as $i)
                        <div class="mini-item"><img src="{{ $i->image }}" alt="">
                          <div style="flex:1"><strong>{{ $i->product_title }}</strong><p>{{ $i->variant_label }} &bull; {{ $i->qty }}x &bull; {{ $rp($i->unit_price) }}</p></div>
                          <strong style="font-size:.85rem">{{ $rp($i->line_total) }}</strong></div>
                      @endforeach
                      <div class="order-ship-row"><span>Ongkos Kirim</span><span>{{ $o->shipping_fee ? $rp($o->shipping_fee) : 'GRATIS' }}</span></div>
                      <div class="order-total-row"><span>Total Tagihan</span><strong>{{ $rp($o->total) }}</strong></div>
                    </div>
                  </template>
                </td>
              </tr>
            @empty
              <tr><td colspan="7" style="text-align:center;color:var(--text-muted);padding:3rem;">
                {{ $stats['orders'] === 0 ? 'Belum ada pesanan masuk. Lakukan tes order melalui halaman checkout.' : 'Tidak ada pesanan yang cocok dengan pencarian/filter.' }}
              </td></tr>
            @endforelse
          </tbody>
        </table>
      </div>
    </div>
  </div>

  {{-- ============ Tab Produk ============ --}}
  <div class="tab-pane" id="tab-admin-products">
    <div class="admin-table-card">
      <div class="table-header">
        <h3>Katalog Produk</h3>
        <p>Produk yang berstatus "Tayang" otomatis muncul di halaman toko. Stok berkurang saat ada pesanan dan kembali bila pesanan dibatalkan.</p>
      </div>

      <form method="GET" action="{{ route('admin.index') }}" class="toolbar toolbar-pad">
        <input type="search" name="pq" value="{{ $filters['pq'] }}" class="toolbar-input" placeholder="Cari nama, merek, atau notes...">
        <select name="cat" class="status-select-edit" onchange="this.form.submit()">
          <option value="all">Semua Kategori</option>
          @foreach($categories as $c)
            <option value="{{ $c->slug }}" {{ $filters['cat'] === $c->slug ? 'selected' : '' }}>{{ $c->name }}</option>
          @endforeach
        </select>
        <button type="submit" class="btn btn-secondary btn-sm">Cari</button>
        <button type="button" class="btn btn-primary btn-sm" data-product-modal data-store-url="{{ route('admin.products.store') }}"><i class="fa-solid fa-plus"></i> Tambah Produk</button>
      </form>

      <div class="table-responsive">
        <table class="admin-table">
          <thead><tr><th>Foto</th><th>Produk</th><th>Kategori</th><th>Varian, Harga &amp; Stok</th><th>Status</th><th>Aksi</th></tr></thead>
          <tbody>
            @forelse($products as $p)
              @php
                $payload = [
                  'update_url' => route('admin.products.update', $p),
                  'brand' => $p->brand, 'name' => $p->name, 'category' => $p->category?->slug ?? 'ngantor',
                  'badge' => $p->badge, 'vibe' => $p->vibe, 'notes' => $p->notes,
                  'longevity_label' => $p->longevity_label, 'longevity_pct' => $p->longevity_pct,
                  'sillage_label' => $p->sillage_label, 'sillage_pct' => $p->sillage_pct,
                  'image_src' => $p->image_src,
                  'image_url' => \Illuminate\Support\Str::startsWith((string) $p->image, ['http://', 'https://']) ? $p->image : '',
                  'is_active' => $p->is_active,
                  'variants' => $p->variants->map(fn ($v) => ['id' => $v->id, 'label' => $v->label, 'price' => $v->price, 'stock' => $v->stock])->all(),
                ];
              @endphp
              <tr class="{{ $p->is_active ? '' : 'row-hidden' }}">
                <td><img class="admin-thumb" src="{{ $p->image_src }}" alt=""></td>
                <td><strong>{{ $p->name }}</strong><br><small class="text-muted">{{ $p->brand }}</small>
                  @if($p->is_set)<br><span class="badge-pay" style="margin-top:.3rem;display:inline-block;">Discovery Set</span>@endif
                  @if($p->badge)<br><span class="badge-pay" style="margin-top:.3rem;display:inline-block;">{{ $p->badge }}</span>@endif</td>
                <td><small>{{ $p->category?->name ?? '—' }}</small></td>
                <td>
                  @forelse($p->variants as $v)
                    <div class="variant-line"><span>{{ $v->label }}</span><span>{{ $rp($v->price) }}</span>
                      <span class="stock-pill {{ $v->stock <= 0 ? 'stock-out' : ($v->stock <= $low ? 'stock-low' : '') }}">{{ $v->stock <= 0 ? 'Habis' : 'Stok '.$v->stock }}</span></div>
                  @empty
                    <small class="text-muted">Belum ada varian</small>
                  @endforelse
                </td>
                <td>
                  <form method="POST" action="{{ route('admin.products.toggle', $p) }}">
                    @csrf @method('PATCH')
                    <label class="switch" title="{{ $p->is_active ? 'Tayang di website' : 'Disembunyikan' }}">
                      <input type="checkbox" {{ $p->is_active ? 'checked' : '' }} onchange="this.form.submit()">
                      <span class="switch-slider"></span>
                    </label>
                  </form>
                  <small class="text-muted" style="display:block;margin-top:.3rem;">{{ $p->is_active ? 'Tayang' : 'Disembunyikan' }}</small>
                </td>
                <td>
                  <div class="row-actions">
                    <button type="button" class="btn-icon" title="Ubah" data-product-modal data-product="{{ json_encode($payload) }}"><i class="fa-regular fa-pen-to-square"></i></button>
                    <form method="POST" action="{{ route('admin.products.destroy', $p) }}" style="display:inline;"
                          onsubmit="return confirm('Hapus produk &quot;{{ addslashes($p->name) }}&quot;? Pesanan lama tetap tersimpan, tetapi produk hilang dari katalog.\n\nTips: pakai tombol Tayang jika hanya ingin menyembunyikan sementara.')">
                      @csrf @method('DELETE')
                      <button type="submit" class="btn-icon danger" title="Hapus"><i class="fa-regular fa-trash-can"></i></button>
                    </form>
                  </div>
                </td>
              </tr>
            @empty
              <tr><td colspan="6" style="text-align:center;color:var(--text-muted);padding:3rem;">
                {{ $stats['products'] === 0 ? 'Belum ada produk. Klik "Tambah Produk" untuk menambahkan parfum pertama.' : 'Tidak ada produk yang cocok dengan pencarian/filter.' }}
              </td></tr>
            @endforelse
          </tbody>
        </table>
      </div>
    </div>
  </div>

  {{-- ============ Tab Pelanggan ============ --}}
  <div class="tab-pane" id="tab-admin-customers">
    <div class="admin-table-card">
      <div class="table-header"><h3>Data Pelanggan</h3><p>Pelanggan yang mendaftar lewat website beserta riwayat belanjanya.</p></div>
      <div class="table-responsive">
        <table class="admin-table">
          <thead><tr><th>Nama</th><th>Username</th><th>Email</th><th>WhatsApp</th><th>Jml Pesanan</th><th>Total Belanja</th><th>Aksi</th></tr></thead>
          <tbody>
            @forelse($customers as $u)
              <tr>
                <td><strong>{{ $u->name }}</strong></td>
                <td>{{ $u->username }}</td>
                <td>{{ $u->email }}</td>
                <td>+62 {{ $u->phone }}</td>
                <td>{{ $u->orders->count() }}</td>
                <td><strong style="color:var(--terracotta);">{{ $rp($u->orders->where('status', '!=', 0)->sum(fn ($o) => $o->total)) }}</strong></td>
                <td><a class="btn-send-wa" target="_blank" rel="noopener" href="https://wa.me/62{{ $u->phone }}"><i class="fa-brands fa-whatsapp"></i> Chat</a></td>
              </tr>
            @empty
              <tr><td colspan="7" style="text-align:center;color:var(--text-muted);padding:3rem;">Belum ada pelanggan yang mendaftar.</td></tr>
            @endforelse
          </tbody>
        </table>
      </div>
    </div>
  </div>

  {{-- ============ Tab Laporan ============ --}}
  <div class="tab-pane" id="tab-admin-reports">
    <div class="report-grid">
      <div class="admin-table-card report-card"><h3>Produk Terlaris</h3>@include('admin.partials.bars', ['rows' => $report['topProducts'], 'mode' => 'sold'])</div>
      <div class="admin-table-card report-card"><h3>Metode Pembayaran</h3>@include('admin.partials.bars', ['rows' => $report['payments'], 'mode' => 'money'])</div>
      <div class="admin-table-card report-card"><h3>Status Pesanan</h3>@include('admin.partials.bars', ['rows' => $report['statuses'], 'mode' => 'count'])</div>
      <div class="admin-table-card report-card"><h3>Stok Menipis / Habis</h3>
        @include('admin.partials.bars', ['mode' => 'stock', 'rows' => $report['lowStock']->map(fn ($v) => (object) [
          'label' => "{$v->product->name} — {$v->product->brand} ({$v->label})", 'total' => $v->stock,
        ])])
      </div>
    </div>
  </div>
</main>

{{-- Modal Detail Pesanan (isi diambil dari <template> per pesanan) --}}
<div class="modal-backdrop" id="orderModal">
  <div class="modal-box">
    <button type="button" class="modal-close" data-close-modal aria-label="Tutup"><i class="fa-solid fa-xmark"></i></button>
    <div id="orderModalBody"></div>
  </div>
</div>

{{-- Modal Tambah / Ubah Produk --}}
<div class="modal-backdrop" id="productModal">
  <div class="modal-box modal-wide">
    <button type="button" class="modal-close" data-close-modal aria-label="Tutup"><i class="fa-solid fa-xmark"></i></button>
    <span class="label-tiny">KATALOG TOKO</span>
    <h3 id="productModalTitle" style="font-family:var(--font-serif);font-size:1.7rem;margin-bottom:1.2rem;">Tambah Produk Baru</h3>

    <form id="productForm" method="POST" enctype="multipart/form-data" action="{{ route('admin.products.store') }}">
      @csrf
      <input type="hidden" name="_method" value="POST" id="productMethod">

      <div class="form-grid">
        <div class="form-group"><label>Merek / Brand *</label><input type="text" name="brand" id="pfBrand" placeholder="Contoh: HMNS" required></div>
        <div class="form-group"><label>Nama Parfum *</label><input type="text" name="name" id="pfName" placeholder="Contoh: Senja di Ubud" required></div>
      </div>
      <div class="form-grid">
        <div class="form-group"><label>Kategori Momen *</label>
          <select name="category" id="pfCategory" class="form-select">
            @foreach($categories as $c)<option value="{{ $c->slug }}">{{ $c->name }}</option>@endforeach
          </select></div>
        <div class="form-group"><label>Label Pita (opsional)</label><input type="text" name="badge" id="pfBadge" placeholder="Contoh: Viral #1 / Paling Manis" maxlength="24"></div>
      </div>
      <div class="form-group"><label>Deskripsi Suasana</label><input type="text" name="vibe" id="pfVibe" placeholder="Contoh: Sore hangat di kafe kayu bernuansa tenang"></div>
      <div class="form-group"><label>Notes Aroma</label><input type="text" name="notes" id="pfNotes" placeholder="Contoh: Fig, Sandalwood, Warm Bourbon Amber"></div>

      <div class="form-grid">
        <div class="form-group"><label>Ketahanan (teks)</label><input type="text" name="longevity_label" id="pfLongevityLabel" placeholder="8-10 Jam"></div>
        <div class="form-group"><label>Ketahanan (bar 0–100)</label><input type="number" name="longevity_pct" id="pfLongevityPct" min="0" max="100" value="70"></div>
      </div>
      <div class="form-grid">
        <div class="form-group"><label>Jarak Sebar (teks)</label><input type="text" name="sillage_label" id="pfSillageLabel" placeholder="Sedang-Tinggi"></div>
        <div class="form-group"><label>Jarak Sebar (bar 0–100)</label><input type="number" name="sillage_pct" id="pfSillagePct" min="0" max="100" value="70"></div>
      </div>

      <div class="form-group">
        <label>Foto Produk</label>
        <div class="image-field">
          <img id="pfImagePreview" class="admin-thumb big" src="{{ config('shop.default_image') }}" alt="Pratinjau">
          <div class="image-field-inputs">
            <input type="file" name="image_file" id="pfImageFile" accept="image/*">
            <input type="url" name="image_url" id="pfImageUrl" placeholder="atau tempel URL gambar (https://...)">
            <small class="text-muted">Foto diunggah ke server (maks. 2 MB). Kosongkan untuk mempertahankan / memakai gambar bawaan.</small>
          </div>
        </div>
      </div>

      <div class="form-group">
        <label>Varian Ukuran, Harga &amp; Stok *</label>
        <div class="variant-head"><span>Ukuran</span><span>Harga (Rp)</span><span>Stok</span><span></span></div>
        <div id="variantRows"></div>
        <button type="button" class="btn btn-secondary btn-sm" style="align-self:flex-start;margin-top:.6rem;" id="addVariantBtn"><i class="fa-solid fa-plus"></i> Tambah Varian</button>
      </div>

      <label class="check-line"><input type="checkbox" name="is_active" value="1" id="pfActive" checked> Tayangkan di website sekarang</label>

      <div class="modal-actions">
        <button type="button" class="btn btn-secondary btn-sm" data-close-modal>Batal</button>
        <button type="submit" class="btn btn-primary btn-sm">Simpan Produk</button>
      </div>
    </form>
  </div>
</div>
@endsection
