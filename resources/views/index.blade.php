<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>NgeScent. — Kurasi Parfum Viral & Booming</title>

  <!-- Google Fonts & Font Awesome Icons -->
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:ital,wght@0,500;0,600;0,700;1,400;1,600&family=Plus+Jakarta+Sans:wght@300;400;500;600;700&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
  
  <link rel="stylesheet" href="style.css">
</head>
<body>

  <!-- Top Announcement Bar -->
  <div class="top-bar">
    <p>⚡ PROMO KURASI: FREE SHIPPING MIN. BELANJA RP 500.000 — 100% GARANSI BATCH CODE RESMI</p>
  </div>

  <!-- Header Navigation -->
  <header class="navbar">
    <div class="nav-container">
      <a href="index.html" class="logo">Nge<span>Scent</span>.</a>

      <nav class="nav-links">
        <a href="#koleksi">Katalog Produk</a>
        <a href="#discovery">Discovery Set</a>
        <a href="#trust">Garansi Original</a>
        <a href="#ulasan">Ulasan</a>
      </nav>

      <div class="nav-icons">
        <!-- Tombol Masuk Navbar Murni -->
        <a href="login.html" class="nav-user-btn" id="navUserLink" aria-label="Akun Saya">
          <i class="fa-regular fa-user"></i>
          <span id="navUserStatus">Masuk</span>
        </a>

        <!-- Tombol Buka Keranjang Drawer -->
        <button type="button" id="cartTrigger" class="cart-icon-btn" aria-label="Buka Keranjang" onclick="openCart()">
          <i class="fa-solid fa-bag-shopping"></i>
          <span class="badge" id="cartBadge">0</span>
        </button>
      </div>
    </div>
  </header>

  <!-- Hero Section -->
  <section class="hero">
    <div class="hero-left">
      <span class="eyebrow">KURASI AROMA PALING DICARI</span>
      <h1 class="hero-title">Wangi yang <em>pulang</em> ke kamu.</h1>
      <p class="hero-desc">
        Takut salah pilih wangi? Tenang. NgeScent mengurasi parfum viral, indie lokal terhits, dan brand desainer ternama lengkap dengan panduan karakter wangi serta opsi tester botol kecil (decant).
      </p>
      <div class="hero-actions">
        <a href="#koleksi" class="btn btn-primary">LIHAT KOLEKSI &rarr;</a>
        <a href="#discovery" class="btn btn-secondary">COBA TESTER SET</a>
      </div>
    </div>

    <div class="hero-right">
      <img src="https://images.unsplash.com/photo-1594035910387-fea47794261f?auto=format&fit=crop&w=1200&q=80" alt="Botol Parfum Eksklusif NgeScent">
      <div class="hero-tag">EDISI PILIHAN TERLARIS &bull; 01</div>
    </div>
  </section>

  <!-- Brands Strip -->
  <section class="brands-strip">
    <div class="brands-inner">
      <span>HMNS</span><span class="dot">&bull;</span>
      <span>SAFF & CO.</span><span class="dot">&bull;</span>
      <span>MYKONOS</span><span class="dot">&bull;</span>
      <span>YVES SAINT LAURENT</span><span class="dot">&bull;</span>
      <span>ALCHEMIST FRAGRANCE</span><span class="dot">&bull;</span>
      <span>PROJECT 1945</span>
    </div>
  </section>

  <!-- Trust Section -->
  <section id="trust" class="trust-section">
    <div class="trust-container">
      <div class="trust-card">
        <i class="fa-solid fa-certificate"></i>
        <h4>100% Original Guarantee</h4>
        <p>Setiap produk memiliki batch code resmi terverifikasi. Garansi uang kembali 200% jika terbukti tidak asli.</p>
      </div>
      <div class="trust-card">
        <i class="fa-solid fa-vial-circle-check"></i>
        <h4>Sterile Decant Sampling</h4>
        <p>Tester 5ml dipindahkan langsung dari botol original menggunakan alat steril khusus tanpa merusak konsentrasi wangi.</p>
      </div>
      <div class="trust-card">
        <i class="fa-solid fa-box-open"></i>
        <h4>Garansi Paket Pecah</h4>
        <p>Packing ekstra tebal dengan double bubble wrap dan kardus solid. Rusak di perjalanan langsung diganti baru.</p>
      </div>
    </div>
  </section>

  <!-- Catalog Section -->
  <section id="koleksi" class="catalog-section">
    <div class="catalog-header">
      <div class="title-group">
        <span class="eyebrow">KATALOG PILIHAN VIRAL</span>
        <h2>Koleksi Terpopuler</h2>
      </div>
      <p class="catalog-intro">
        Pilih wangi berdasarkan momen beraktivitas. Tersedia pilihan ukuran botol penuh maupun botol mini decant untuk mencoba.
      </p>
    </div>

    <!-- Filter Tabs Momen -->
    <div class="filter-bar">
      <div class="filter-tabs">
        <button type="button" class="tab-btn active" data-filter="all">Semua Momen</button>
        <button type="button" class="tab-btn" data-filter="ngantor">Daily Ngantor & Kampus</button>
        <button type="button" class="tab-btn" data-filter="dating">Kencan Malam (Date Night)</button>
        <button type="button" class="tab-btn" data-filter="outdoor">Segar Outdoor / Tropis</button>
      </div>
    </div>

    <!-- Product Grid -->
    <div class="product-grid" id="productGrid">
      <!-- Kartu produk dirender otomatis oleh script.js dari data katalog (dikelola lewat admin.html) -->
    </div>
  </section>

  <!-- Discovery Set Promo -->
  <section id="discovery" class="discovery-section">
    <div class="discovery-container">
      <div class="discovery-text">
        <span class="eyebrow">ANTI SALAH BELI (BLIND BUY SOLVER)</span>
        <h2>Discovery Box: 4 Aroma Viral Terbaik</h2>
        <p>Ragu keluar ratusan ribu untuk botol besar yang belum tentu kamu sukai? Cobalah <strong>NgeScent Trial Kit</strong>. Berisi 4 decant travel spray (masing-masing 5ml) dari varian terlaris HMNS, Saff & Co, Mykonos, dan Project 1945.</p>
        <ul class="discovery-features">
          <li><i class="fa-solid fa-check"></i> Cukup untuk pemakaian lebih dari 150 kali semprot</li>
          <li><i class="fa-solid fa-check"></i> Dilengkapi kartu panduan piramida wangi dan waktu pakai terbaik</li>
          <li><i class="fa-solid fa-check"></i> <strong>Bonus voucher cashback Rp 50.000</strong> untuk pembelian botol full-size nantinya</li>
        </ul>
        <div class="discovery-action">
          <div class="price-wrap">
            <span class="bundle-price">Rp 149.000</span>
            <span class="bundle-old-price">Rp 185.000</span>
          </div>
          <button type="button" class="btn btn-primary" onclick="addToCart('Discovery Set Viral (4x 5ml)', 149000, 'Discovery Box', 'https://images.unsplash.com/photo-1608571423902-eed4a5ad8108?auto=format&fit=crop&w=700&q=80')">
            AMBIL SET SEKARANG &rarr;
          </button>
        </div>
      </div>
      <div class="discovery-img">
        <img src="https://images.unsplash.com/photo-1608571423902-eed4a5ad8108?auto=format&fit=crop&w=1200&q=80" alt="Discovery Set NgeScent">
      </div>
    </div>
  </section>

  <!-- Reviews Section -->
  <section id="ulasan" class="reviews-section">
    <div class="reviews-header">
      <span class="eyebrow">KOMUNITAS NGESCENT</span>
      <h2>Apa Kata Mereka yang Sudah Mencobanya</h2>
    </div>

    <div class="reviews-grid">
      <div class="review-card">
        <div class="stars"><i class="fa-solid fa-star"></i><i class="fa-solid fa-star"></i><i class="fa-solid fa-star"></i><i class="fa-solid fa-star"></i><i class="fa-solid fa-star"></i></div>
        <p class="review-text">"Bagus banget konsep decant-nya! Gue yang tadinya takut blind-buy HMNS akhirnya beli versi 5ml dulu. Pas dicoba seharian di kantor ternyata banyak yang muji wanginya, akhirnya beli yang full size di sini juga!"</p>
        <div class="reviewer">
          <strong>Rian Pratama</strong>
          <span>Membeli HMNS Senja di Ubud &bull; Terverifikasi</span>
        </div>
      </div>

      <div class="review-card">
        <div class="stars"><i class="fa-solid fa-star"></i><i class="fa-solid fa-star"></i><i class="fa-solid fa-star"></i><i class="fa-solid fa-star"></i><i class="fa-solid fa-star"></i></div>
        <p class="review-text">"Packing-nya niat parah. Bubble wrap tebal jadi botol kaca aman sampai Surabaya tanpa bocor setetes pun. Batch code SAFF & Co-nya saya cek di website resmi juga tembus. Pengiriman cepat!"</p>
        <div class="reviewer">
          <strong>Dinda Clarissa</strong>
          <span>Membeli SAFF & CO. S.O.T.B &bull; Terverifikasi</span>
        </div>
      </div>

      <div class="review-card">
        <div class="stars"><i class="fa-solid fa-star"></i><i class="fa-solid fa-star"></i><i class="fa-solid fa-star"></i><i class="fa-solid fa-star"></i><i class="fa-solid fa-star"></i></div>
        <p class="review-text">"Deskripsi suasana wangi di webnya akurat. Buat yang gak ngerti istilah notes parfum rumit, panduan suasananya ngebantu banget pas mau cari parfum buat ngedate!"</p>
        <div class="reviewer">
          <strong>Fahri Alamsyah</strong>
          <span>Membeli Mykonos Aphrodite &bull; Terverifikasi</span>
        </div>
      </div>
    </div>
  </section>

  <!-- Editorial Terracotta Footer -->
  <footer class="footer">
    <div class="footer-container">
      <div class="footer-brand">
        <h2 class="footer-logo">Nge<span>Scent</span>.</h2>
        <p class="footer-tagline">Tempat kurasi wewangian terpercaya. Menghubungkan cerita dalam dirimu dengan aroma yang paling tepat.</p>
        <div class="footer-guarantee">
          <i class="fa-solid fa-shield-halved"></i> 100% Guaranteed Original & Safely Delivered
        </div>
      </div>

      <div class="footer-links-group">
        <div class="footer-col">
          <h4>Koleksi</h4>
          <ul>
            <li><a href="#koleksi">Parfum Viral TikTok</a></li>
            <li><a href="#discovery">Discovery Trial Set</a></li>
            <li><a href="#koleksi">Decant Size (5ml)</a></li>
            <li><a href="#koleksi">Full Size Bottles</a></li>
          </ul>
        </div>

        <div class="footer-col">
          <h4>Bantuan</h4>
          <ul>
            <li><a href="#trust">Garansi Batch Code</a></li>
            <li><a href="#trust">Klaim Paket Pecah</a></li>
            <li><a href="https://wa.me/" target="_blank">Chat CS WhatsApp</a></li>
            <li><a href="login.html">Status Pengiriman</a></li>
          </ul>
        </div>
      </div>

      <div class="footer-newsletter">
        <h4>DAPATKAN KABAR RESTOCK</h4>
        <p>Jadilah orang pertama yang tahu saat varian parfum langka kembali hadir.</p>
        <form class="newsletter-form" onsubmit="event.preventDefault(); alert('Terima kasih sudah berlangganan info NgeScent!');">
          <input type="email" placeholder="Email kamu..." required>
          <button type="submit" aria-label="Submit Email">&rarr;</button>
        </form>
      </div>
    </div>

    <div class="footer-bottom">
      <div class="bottom-container">
        <span>&copy; 2026 NGESCENT STORE &bull; ALL RIGHTS RESERVED</span>
        <div class="bottom-links">
          <a href="#">INSTAGRAM</a>
          <a href="#">TIKTOK</a>
          <a href="https://wa.me/" target="_blank">WHATSAPP</a>
        </div>
      </div>
    </div>
  </footer>

  <!-- Floating WhatsApp Advice Button -->
  <a href="https://wa.me/" target="_blank" class="floating-wa" title="Konsultasi Aroma via WhatsApp">
    <i class="fa-brands fa-whatsapp"></i>
    <span>Bingung pilih wangi? Tanya Admin</span>
  </a>

  <!-- Slide-Over Shopping Cart Drawer (LENGKAP DENGAN BACKDROP) -->
  <div class="cart-backdrop" id="cartBackdrop" onclick="closeCart()"></div>
  <aside class="cart-drawer" id="cartDrawer">
    <div class="drawer-header">
      <h3>Keranjang Belanja (<span id="cartCountHeader">0</span>)</h3>
      <button type="button" class="btn-close-drawer" id="closeCartBtn" onclick="closeCart()">&times;</button>
    </div>

    <div class="shipping-tracker">
      <p id="shippingStatusText">Tambah Rp 500.000 lagi untuk <strong>Free Ongkir</strong>!</p>
      <div class="progress-bar"><div class="progress-fill" id="shippingProgress"></div></div>
    </div>

    <div class="drawer-items" id="cartItemsList"></div>

    <div class="drawer-footer">
      <div class="subtotal-row">
        <span>Subtotal</span>
        <strong id="cartSubtotalText">Rp 0</strong>
      </div>
      <p class="shipping-note">Ongkos kirim & diskon dihitung saat langkah pembayaran.</p>
      <button type="button" class="btn btn-primary btn-checkout" onclick="handleCheckoutGuard()">LANJUT KE PEMBAYARAN &rarr;</button>
    </div>
  </aside>

  <script src="script.js"></script>
</body>
</html>
