<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Masuk / Daftar — NgeScent.</title>

  <!-- Google Fonts & Font Awesome Icons -->
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:wght@600;700&family=Plus+Jakarta+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
  
  <style>
    :root {
      --bg-cream: #FAF6F0;
      --terracotta: #9B4926;
      --terracotta-dark: #813B1E;
      --text-primary: #1C1917;
      --text-muted: #78716C;
      --border-light: #E7DFD5;
      --border-subtle: #D8CCC0;
      --font-serif: 'Cormorant Garamond', Georgia, serif;
      --font-sans: 'Plus Jakarta Sans', sans-serif;
    }

    * { margin: 0; padding: 0; box-sizing: border-box; }

    body {
      background-color: var(--bg-cream);
      color: var(--text-primary);
      font-family: var(--font-sans);
      min-height: 100vh;
      display: flex;
      align-items: center;
      justify-content: center;
      padding: 2.5rem 1.5rem;
    }

    .auth-box {
      background: #FFFFFF;
      border: 1px solid var(--border-light);
      padding: 3rem 2.5rem;
      max-width: 480px;
      width: 100%;
      border-radius: 4px;
      box-shadow: 0 15px 40px rgba(0, 0, 0, 0.05);
    }

    .text-center { text-align: center; }

    .logo {
      font-family: var(--font-serif);
      font-size: 2.3rem;
      font-weight: 700;
      color: var(--text-primary);
      text-decoration: none;
      display: inline-block;
    }
    .logo span { color: var(--terracotta); font-weight: 400; }

    .eyebrow {
      display: block;
      font-size: 0.72rem;
      text-transform: uppercase;
      letter-spacing: 3px;
      color: var(--terracotta);
      font-weight: 700;
      margin-top: 0.8rem;
    }

    h2 {
      font-family: var(--font-serif);
      font-size: 2.2rem;
      margin: 0.3rem 0;
    }

    .subtitle {
      font-size: 0.85rem;
      color: var(--text-muted);
      margin-bottom: 1.8rem;
      line-height: 1.5;
    }

    /* Tab Switcher Masuk vs Daftar */
    .auth-tabs {
      display: flex;
      background: #FAF6F0;
      border: 1px solid var(--border-subtle);
      border-radius: 4px;
      padding: 0.3rem;
      margin-bottom: 1.8rem;
      gap: 0.4rem;
    }

    .tab-btn {
      flex: 1;
      background: transparent;
      border: none;
      padding: 0.7rem;
      font-size: 0.88rem;
      font-weight: 600;
      color: var(--text-muted);
      cursor: pointer;
      border-radius: 2px;
      transition: all 0.2s ease;
      font-family: inherit;
    }

    .tab-btn.active {
      background: #FFFFFF;
      color: var(--terracotta);
      box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
    }

    .checkout-alert-banner {
      background: rgba(155, 73, 38, 0.08);
      border: 1px solid var(--terracotta);
      color: var(--terracotta);
      padding: 0.75rem 1rem;
      border-radius: 4px;
      font-size: 0.82rem;
      font-weight: 600;
      display: flex;
      align-items: center;
      gap: 0.6rem;
      margin-bottom: 1.5rem;
      text-align: left;
    }

    .form-group {
      display: flex;
      flex-direction: column;
      gap: 0.35rem;
      margin-bottom: 1.1rem;
      text-align: left;
    }

    .form-group label {
      font-size: 0.8rem;
      font-weight: 600;
      color: var(--text-muted);
    }

    .form-group input {
      background: #FFFFFF;
      border: 1px solid var(--border-subtle);
      padding: 0.75rem 0.9rem;
      font-size: 0.9rem;
      font-family: inherit;
      outline: none;
      border-radius: 2px;
      width: 100%;
    }

    .form-group input:focus {
      border-color: var(--terracotta);
    }

    .input-wa-wrapper {
      display: flex;
      align-items: center;
      background: #fff;
      border: 1px solid var(--border-subtle);
      border-radius: 2px;
    }

    .wa-prefix {
      padding: 0.75rem 0.9rem;
      background: #f1ece5;
      font-weight: 600;
      font-size: 0.88rem;
      border-right: 1px solid var(--border-subtle);
      color: var(--text-primary);
    }

    .input-wa-wrapper input {
      border: none;
      padding: 0.75rem 0.9rem;
      outline: none;
      font-size: 0.9rem;
      width: 100%;
    }

    .btn-submit {
      width: 100%;
      background-color: var(--terracotta);
      color: #fff;
      border: 1px solid var(--terracotta);
      padding: 0.95rem;
      font-size: 0.82rem;
      letter-spacing: 1.5px;
      text-transform: uppercase;
      font-weight: 700;
      cursor: pointer;
      border-radius: 2px;
      margin-top: 0.5rem;
      transition: background-color 0.2s ease;
      font-family: inherit;
    }

    .btn-submit:hover {
      background-color: var(--terracotta-dark);
    }

    .switch-link {
      margin-top: 1.2rem;
      font-size: 0.85rem;
      color: var(--text-muted);
    }

    .switch-link a {
      color: var(--terracotta);
      font-weight: 600;
      cursor: pointer;
    }

    .admin-divider {
      text-align: center;
      position: relative;
      margin: 1.8rem 0 0.8rem;
    }

    .admin-divider::before {
      content: '';
      position: absolute;
      top: 50%;
      left: 0;
      width: 100%;
      height: 1px;
      background: var(--border-light);
      z-index: 1;
    }

    .admin-divider span {
      position: relative;
      background: #fff;
      padding: 0 0.8rem;
      font-size: 0.68rem;
      letter-spacing: 1.5px;
      color: var(--text-muted);
      font-weight: 700;
      z-index: 2;
    }

    .btn-admin {
      width: 100%;
      background: #FAF6F0;
      border: 1px dashed var(--terracotta);
      padding: 0.75rem;
      font-size: 0.78rem;
      font-weight: 600;
      color: var(--terracotta);
      border-radius: 4px;
      cursor: pointer;
      display: flex;
      align-items: center;
      justify-content: center;
      gap: 0.5rem;
      font-family: inherit;
      transition: all 0.2s ease;
    }

    .btn-admin:hover {
      background: var(--terracotta);
      color: #fff;
    }

    .back-home {
      display: inline-block;
      margin-top: 1.2rem;
      font-size: 0.82rem;
      color: var(--text-muted);
      text-decoration: none;
    }
    .back-home:hover { color: var(--terracotta); }
  </style>
</head>
<body>

  <div class="auth-box">
    <div class="text-center">
      <a href="index.html" class="logo">Nge<span>Scent</span>.</a>
      <span class="eyebrow">AKUN & PELACAKAN</span>
      <h2 id="authHeading">Masuk ke Akun</h2>
      <p id="authSubheading" class="subtitle">Gunakan Email/Username dan Password untuk melacak pesananmu.</p>
    </div>

    <!-- Banner Notifikasi jika diarahkan saat mau bayar -->
    <div id="checkoutNoticeBanner" class="checkout-alert-banner" style="display: none;">
      <i class="fa-solid fa-circle-exclamation"></i>
      <span>Silakan daftar terlebih dahulu untuk melanjutkan pembayaran pesananmu.</span>
    </div>

    <!-- Tab Tombol: Masuk vs Daftar -->
    <div class="auth-tabs">
      <button type="button" id="tabBtnLogin" class="tab-btn active" onclick="setAuthMode('login')">Masuk</button>
      <button type="button" id="tabBtnRegister" class="tab-btn" onclick="setAuthMode('register')">Daftar Akun Baru</button>
    </div>

    <!-- 1. Form Masuk (Email / Username & Password) -->
    <form id="formLogin" onsubmit="event.preventDefault(); submitLoginWithPassword();">
      <div class="form-group">
        <label>Email atau Username</label>
        <input type="text" id="loginUserOrEmail" placeholder="nama@gmail.com atau budisantoso" required autofocus>
      </div>

      <div class="form-group">
        <label>Password</label>
        <input type="password" id="loginPassword" placeholder="Masukkan password kamu" required>
      </div>

      <button type="submit" class="btn-submit">
        MASUK SEKARANG &rarr;
      </button>

      <div class="text-center switch-link">
        Belum punya akun? <a onclick="setAuthMode('register')">Daftar di sini</a>
      </div>
    </form>

    <!-- 2. Form Daftar Lengkap (Nama, Username, Email, No. WA, Password) -->
    <form id="formRegister" style="display: none;" onsubmit="event.preventDefault(); submitRegisterWithPassword();">
      <div class="form-group">
        <label>Nama Lengkap</label>
        <input type="text" id="regName" placeholder="Contoh: Budi Santoso" required>
      </div>

      <div class="form-group">
        <label>Username</label>
        <input type="text" id="regUsername" placeholder="Contoh: budisantoso" required>
      </div>

      <div class="form-group">
        <label>Alamat Email</label>
        <input type="email" id="regEmail" placeholder="nama@gmail.com" required>
      </div>

      <div class="form-group">
        <label>Nomor WhatsApp Aktif</label>
        <div class="input-wa-wrapper">
          <span class="wa-prefix">+62</span>
          <input type="tel" id="regPhone" placeholder="81234567890" required>
        </div>
        <small style="font-size: 0.72rem; color: var(--text-muted); margin-top: 0.2rem;">
          Wajib aktif untuk konfirmasi kurir COD & update resi WhatsApp.
        </small>
      </div>

      <div class="form-group">
        <label>Password</label>
        <input type="password" id="regPassword" placeholder="Minimal 6 karakter" minlength="6" required>
      </div>

      <button type="submit" class="btn-submit">
        DAFTAR & LANJUTKAN &rarr;
      </button>

      <div class="text-center switch-link">
        Sudah memiliki akun? <a onclick="setAuthMode('login')">Masuk di sini</a>
      </div>
    </form>

    <!-- Akses Khusus Owner Toko -->
    <div class="admin-divider">
      <span>AKSES OWNER TOKO</span>
    </div>
    <button type="button" class="btn-admin" onclick="simulateGoogleLogin('admin')">
      <i class="fa-solid fa-user-shield"></i>
      <span>Masuk sebagai Admin / Pemilik Toko</span>
    </button>

    <div class="text-center">
      <a href="index.html" class="back-home">&larr; Kembali ke Beranda Toko</a>
    </div>
  </div>

  <script src="script.js"></script>
</body>
</html>
