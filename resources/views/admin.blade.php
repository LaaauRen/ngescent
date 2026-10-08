<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Admin Portal — NgeScent.</title>

  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:wght@600;700&family=Plus+Jakarta+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
  
  <link rel="stylesheet" href="style.css">
</head>
<body class="admin-body">

  <main class="admin-dashboard-container">
    <div class="admin-topbar">
      <div>
        <span class="eyebrow" style="margin-bottom: 0;">OWNER PORTAL</span>
        <h2>Panel Kendali Toko</h2>
        <p class="text-muted" style="font-size: 0.85rem;">Login Admin Toko: <strong id="adminEmailDisplay">owner.ngescent@gmail.com</strong></p>
      </div>
      <div class="admin-actions">
        <a href="index.html" target="_blank" class="btn btn-secondary btn-sm"><i class="fa-solid fa-arrow-up-right-from-square"></i> Lihat Toko Live</a>
        <button type="button" onclick="handleLogout()" class="btn btn-primary btn-sm"><i class="fa-solid fa-power-off"></i> Keluar</button>
      </div>
    </div>

    <div class="admin-stats-grid">
      <div class="stat-card"><span class="stat-label">Total Pesanan</span><strong id="statTotalOrders">0</strong></div>
      <div class="stat-card"><span class="stat-label">Omzet (tanpa batal)</span><strong id="statTotalRevenue" style="color: var(--terracotta);">Rp 0</strong></div>
      <div class="stat-card"><span class="stat-label">Perlu Diproses</span><strong id="statPendingOrders" style="color: #2e7d32;">0 Pesanan</strong></div>
      <div class="stat-card"><span class="stat-label">Pelanggan Terdaftar</span><strong id="statCustomers">0</strong></div>
      <div class="stat-card"><span class="stat-label">Katalog</span><strong id="statProducts" style="font-size:1.15rem;">0 Produk</strong></div>
    </div>

    <div class="dashboard-nav-tabs">
      <button type="button" class="dash-tab active" data-tab="admin-orders"><i class="fa-solid fa-receipt"></i> Pesanan</button>
      <button type="button" class="dash-tab" data-tab="admin-products"><i class="fa-solid fa-spray-can-sparkles"></i> Produk</button>
      <button type="button" class="dash-tab" data-tab="admin-customers"><i class="fa-solid fa-users"></i> Pelanggan</button>
      <button type="button" class="dash-tab" data-tab="admin-reports"><i class="fa-solid fa-chart-column"></i> Laporan Penjualan</button>
    </div>

    <!-- Tab Pesanan -->
    <div class="tab-pane active" id="tab-admin-orders">
      <div class="admin-table-card">
        <div class="table-header">
          <h3>Daftar Transaksi Pembeli</h3>
          <p>Ubah status, ketik nomor resi, atau klik WA untuk kabari pembeli.</p>
        </div>
        <div class="toolbar toolbar-pad">
          <input type="search" id="adminSearch" class="toolbar-input" placeholder="Cari ID, nama, email, atau resi..." oninput="renderAdminOrdersTable()">
          <select id="adminStatusFilter" class="status-select-edit" onchange="renderAdminOrdersTable()">
            <option value="all">Semua Status</option>
            <option value="1">Dikonfirmasi</option>
            <option value="2">Dikemas</option>
            <option value="3">Di Jalan</option>
            <option value="4">Selesai</option>
            <option value="0">Dibatalkan</option>
          </select>
          <button type="button" class="btn btn-secondary btn-sm" onclick="exportOrdersCSV()"><i class="fa-solid fa-file-csv"></i> Ekspor CSV</button>
        </div>
        <div class="table-responsive">
          <table class="admin-table">
            <thead>
              <tr>
                <th>ID Order & Waktu</th><th>Penerima & WhatsApp</th><th>Detail Item & Tagihan</th>
                <th>Metode Bayar</th><th>Nomor Resi (J&T)</th><th>Status Paket</th><th>Aksi</th>
              </tr>
            </thead>
            <tbody id="adminOrderTableBody"></tbody>
          </table>
        </div>
      </div>
    </div>

    <!-- Tab Produk -->
    <div class="tab-pane" id="tab-admin-products">
      <div class="admin-table-card">
        <div class="table-header">
          <h3>Katalog Produk</h3>
          <p>Produk yang berstatus "Tayang" otomatis muncul di halaman toko. Stok berkurang saat ada pesanan dan kembali bila pesanan dibatalkan.</p>
        </div>
        <div class="toolbar toolbar-pad">
          <input type="search" id="productSearch" class="toolbar-input" placeholder="Cari nama, merek, atau notes..." oninput="renderProductsTable()">
          <select id="productCategoryFilter" class="status-select-edit" onchange="renderProductsTable()">
            <option value="all">Semua Kategori</option>
            <option value="ngantor">Daily Ngantor &amp; Kampus</option>
            <option value="dating">Kencan Malam</option>
            <option value="outdoor">Segar Outdoor / Tropis</option>
          </select>
          <button type="button" class="btn btn-primary btn-sm" onclick="openProductModal()"><i class="fa-solid fa-plus"></i> Tambah Produk</button>
        </div>
        <div class="table-responsive">
          <table class="admin-table">
            <thead><tr><th>Foto</th><th>Produk</th><th>Kategori</th><th>Varian, Harga &amp; Stok</th><th>Status</th><th>Aksi</th></tr></thead>
            <tbody id="adminProductBody"></tbody>
          </table>
        </div>
      </div>
    </div>

    <!-- Tab Pelanggan -->
    <div class="tab-pane" id="tab-admin-customers">
      <div class="admin-table-card">
        <div class="table-header"><h3>Data Pelanggan</h3><p>Pelanggan yang mendaftar lewat website beserta riwayat belanjanya.</p></div>
        <div class="table-responsive">
          <table class="admin-table">
            <thead><tr><th>Nama</th><th>Username</th><th>Email</th><th>WhatsApp</th><th>Jml Pesanan</th><th>Total Belanja</th><th>Aksi</th></tr></thead>
            <tbody id="adminCustomerBody"></tbody>
          </table>
        </div>
      </div>
    </div>

    <!-- Tab Laporan -->
    <div class="tab-pane" id="tab-admin-reports">
      <div class="report-grid">
        <div class="admin-table-card report-card"><h3>Produk Terlaris</h3><div id="reportTopProducts"></div></div>
        <div class="admin-table-card report-card"><h3>Metode Pembayaran</h3><div id="reportPayments"></div></div>
        <div class="admin-table-card report-card"><h3>Status Pesanan</h3><div id="reportStatuses"></div></div>
        <div class="admin-table-card report-card"><h3>Stok Menipis / Habis</h3><div id="reportLowStock"></div></div>
      </div>
    </div>
  </main>

  <!-- Modal Detail Pesanan -->
  <div class="modal-backdrop" id="orderModal" onclick="if(event.target===this) closeOrderModal()">
    <div class="modal-box">
      <button type="button" class="modal-close" onclick="closeOrderModal()" aria-label="Tutup"><i class="fa-solid fa-xmark"></i></button>
      <div id="orderModalBody"></div>
    </div>
  </div>

  <!-- Modal Tambah / Ubah Produk -->
  <div class="modal-backdrop" id="productModal">
    <div class="modal-box modal-wide">
      <button type="button" class="modal-close" onclick="closeProductModal()" aria-label="Tutup"><i class="fa-solid fa-xmark"></i></button>
      <span class="label-tiny">KATALOG TOKO</span>
      <h3 id="productModalTitle" style="font-family:var(--font-serif);font-size:1.7rem;margin-bottom:1.2rem;">Tambah Produk Baru</h3>

      <form onsubmit="event.preventDefault(); saveProduct();">
        <div class="form-grid">
          <div class="form-group"><label>Merek / Brand *</label><input type="text" id="pfBrand" placeholder="Contoh: HMNS" required></div>
          <div class="form-group"><label>Nama Parfum *</label><input type="text" id="pfName" placeholder="Contoh: Senja di Ubud" required></div>
        </div>
        <div class="form-grid">
          <div class="form-group"><label>Kategori Momen *</label>
            <select id="pfCategory" class="form-select">
              <option value="ngantor">Daily Ngantor &amp; Kampus</option>
              <option value="dating">Kencan Malam (Date Night)</option>
              <option value="outdoor">Segar Outdoor / Tropis</option>
            </select></div>
          <div class="form-group"><label>Label Pita (opsional)</label><input type="text" id="pfBadge" placeholder="Contoh: Viral #1 / Paling Manis" maxlength="24"></div>
        </div>
        <div class="form-group"><label>Deskripsi Suasana</label><input type="text" id="pfVibe" placeholder="Contoh: Sore hangat di kafe kayu bernuansa tenang"></div>
        <div class="form-group"><label>Notes Aroma</label><input type="text" id="pfNotes" placeholder="Contoh: Fig, Sandalwood, Warm Bourbon Amber"></div>

        <div class="form-grid">
          <div class="form-group"><label>Ketahanan (teks)</label><input type="text" id="pfLongevityLabel" placeholder="8-10 Jam"></div>
          <div class="form-group"><label>Ketahanan (bar 0–100)</label><input type="number" id="pfLongevityPct" min="0" max="100" value="70"></div>
        </div>
        <div class="form-grid">
          <div class="form-group"><label>Jarak Sebar (teks)</label><input type="text" id="pfSillageLabel" placeholder="Sedang-Tinggi"></div>
          <div class="form-group"><label>Jarak Sebar (bar 0–100)</label><input type="number" id="pfSillagePct" min="0" max="100" value="70"></div>
        </div>

        <div class="form-group">
          <label>Foto Produk</label>
          <div class="image-field">
            <img id="pfImagePreview" class="admin-thumb big" src="" alt="Pratinjau">
            <div class="image-field-inputs">
              <input type="file" id="pfImageFile" accept="image/*" onchange="handleImageFile(this)">
              <input type="url" id="pfImageUrl" placeholder="atau tempel URL gambar (https://...)" onchange="handleImageUrl(this.value)">
              <small class="text-muted">Foto upload otomatis dikecilkan. Kosongkan untuk memakai gambar bawaan.</small>
            </div>
          </div>
        </div>

        <div class="form-group">
          <label>Varian Ukuran, Harga &amp; Stok *</label>
          <div class="variant-head"><span>Ukuran</span><span>Harga (Rp)</span><span>Stok</span><span></span></div>
          <div id="variantRows"></div>
          <button type="button" class="btn btn-secondary btn-sm" style="align-self:flex-start;margin-top:.6rem;" onclick="addVariantRow()"><i class="fa-solid fa-plus"></i> Tambah Varian</button>
        </div>

        <label class="check-line"><input type="checkbox" id="pfActive" checked> Tayangkan di website sekarang</label>

        <div class="modal-actions">
          <button type="button" class="btn btn-secondary btn-sm" onclick="closeProductModal()">Batal</button>
          <button type="submit" class="btn btn-primary btn-sm">Simpan Produk</button>
        </div>
      </form>
    </div>
  </div>

  <script src="script.js"></script>
</body>
</html>
