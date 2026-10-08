<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Dashboard Pelanggan — NgeScent.</title>

  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:wght@600;700&family=Plus+Jakarta+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
  
  <link rel="stylesheet" href="style.css">
</head>
<body>

  <!-- Navbar -->
  <header class="navbar">
    <div class="nav-container">
      <a href="index.html" class="logo">Nge<span>Scent</span>.</a>

      <div class="nav-icons">
        <span class="user-greeting-pill"><i class="fa-solid fa-circle-check"></i> <strong id="dashNavName">Member</strong></span>
        <a href="index.html" class="btn btn-secondary btn-sm">Katalog Parfum</a>
        <button type="button" onclick="handleLogout()" class="btn-logout-link" title="Keluar"><i class="fa-solid fa-arrow-right-from-bracket"></i></button>
      </div>
    </div>
  </header>

  <main class="dashboard-page-container">
    <div class="dashboard-wrapper">

      <div class="dashboard-user-card">
        <div class="user-avatar-circle" id="dashAvatar">B</div>
        <div class="user-info-text">
          <span class="eyebrow" style="margin-bottom: 0;">MEMBER AREA NGESCENT</span>
          <h1 id="dashName">Halo, Pengguna</h1>
          <p><i class="fa-regular fa-envelope"></i> <span id="dashEmail">email@gmail.com</span> &bull; <i class="fa-brands fa-whatsapp"></i> <span id="dashPhone">-</span></p>
        </div>
      </div>

      <div class="dash-stats">
        <div class="stat-card"><span class="stat-label">Total Pesanan</span><strong id="dashStatOrders">0</strong></div>
        <div class="stat-card"><span class="stat-label">Total Belanja</span><strong id="dashStatSpent" style="color: var(--terracotta);">Rp 0</strong></div>
        <div class="stat-card"><span class="stat-label">Pesanan Aktif</span><strong id="dashStatActive">0</strong></div>
      </div>

      <div class="dashboard-nav-tabs">
        <button type="button" class="dash-tab active" data-tab="active-order"><i class="fa-solid fa-truck-fast"></i> Lacak Pesanan</button>
        <button type="button" class="dash-tab" data-tab="order-history"><i class="fa-solid fa-clock-rotate-left"></i> Riwayat Belanja</button>
        <button type="button" class="dash-tab" data-tab="profile-info"><i class="fa-regular fa-id-card"></i> Profil & Alamat</button>
      </div>

      <div class="dashboard-content-area">

        <!-- Tab 1: Lacak Pesanan -->
        <div class="tab-pane active" id="tab-active-order">
          <div id="dashOrderPicker" class="order-picker" style="display:none;">
            <label for="dashOrderSelect">Pilih pesanan:</label>
            <select id="dashOrderSelect" class="status-select-edit" onchange="renderTrackedOrder(this.value)"></select>
          </div>
          <div id="dashActiveOrder"></div>
        </div>

        <!-- Tab 2: Riwayat -->
        <div class="tab-pane" id="tab-order-history">
          <div class="toolbar">
            <select id="dashHistoryFilter" class="status-select-edit" onchange="renderHistory()">
              <option value="all">Semua Status</option>
              <option value="active">Sedang Diproses</option>
              <option value="4">Selesai</option>
              <option value="0">Dibatalkan</option>
            </select>
          </div>
          <div id="dashHistoryList" class="history-list"></div>
        </div>

        <!-- Tab 3: Profil -->
        <div class="tab-pane" id="tab-profile-info">
          <form class="profile-card" onsubmit="event.preventDefault(); saveProfile();">
            <h4>Data Diri & Alamat Pengiriman</h4>
            <div class="form-grid">
              <div class="form-group"><label>Nama Lengkap</label><input type="text" id="profName" required></div>
              <div class="form-group"><label>Nomor WhatsApp</label><input type="tel" id="profPhone" placeholder="08xxxxxxxxxx" required></div>
            </div>
            <div class="form-group"><label>Email (tidak dapat diubah)</label><input type="email" id="profEmail" disabled></div>
            <div class="form-group"><label>Alamat Pengiriman Utama</label><textarea id="profAddress" rows="3" placeholder="Nama jalan, nomor rumah, RT/RW, kelurahan, kecamatan, kota, kode pos"></textarea></div>
            <button type="submit" class="btn btn-primary btn-sm">Simpan Perubahan</button>
            <small class="text-muted" style="display:block;margin-top:.8rem;"><i class="fa-solid fa-circle-check"></i> Alamat otomatis terisi saat checkout berikutnya.</small>
          </form>

          <form class="profile-card" id="passwordCard" style="margin-top:1.5rem;" onsubmit="event.preventDefault(); changePassword();">
            <h4>Ganti Password</h4>
            <div class="form-grid">
              <div class="form-group"><label>Password Lama</label><input type="password" id="pwOld" required></div>
              <div class="form-group"><label>Password Baru (min. 6 karakter)</label><input type="password" id="pwNew" minlength="6" required></div>
            </div>
            <button type="submit" class="btn btn-secondary btn-sm">Perbarui Password</button>
          </form>
        </div>

      </div>
    </div>
  </main>

  <script src="script.js"></script>
</body>
</html>
