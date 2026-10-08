<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Checkout Pesanan — NgeScent.</title>

  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:wght@600;700&family=Plus+Jakarta+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
  
  <link rel="stylesheet" href="style.css">
</head>
<body>

  <!-- Header Ringkas -->
  <header class="navbar simple-nav">
    <div class="nav-container">
      <a href="index.html" class="logo">Nge<span>Scent</span>.</a>
      <a href="index.html" class="link-back"><i class="fa-solid fa-arrow-left"></i> Lanjut Belanja</a>
    </div>
  </header>

  <main class="checkout-page-container">
    <div class="checkout-layout">
      
      <!-- Kolom Kiri: Form Alamat & Pembayaran -->
      <section class="checkout-form-col">
        <div class="checkout-box">
          <span class="eyebrow">LANGKAH 1 DARI 2</span>
          <h2>Informasi Pengiriman & Akun</h2>
          
          <!-- Indikator Akun Terhubung -->
          <div id="checkoutAuthNotice" class="google-auth-notice">
            <i class="fa-regular fa-circle-user"></i>
            <div id="checkoutAuthContent">
              <span>Akun terhubung: <strong id="checkoutUserEmailDisplay">-</strong></span>
            </div>
          </div>

          <form id="checkoutForm" onsubmit="event.preventDefault(); submitCheckoutPage();">
            <div class="form-grid">
              <div class="form-group">
                <label>Nama Lengkap Penerima</label>
                <input type="text" id="pageCustName" placeholder="Contoh: Budi Santoso" required>
              </div>
              <div class="form-group">
                <label>Nomor WhatsApp Aktif</label>
                <input type="tel" id="pageCustPhone" placeholder="08xxxxxxxxxx" required>
              </div>
            </div>

            <div class="form-group">
              <label>Alamat Lengkap Pengiriman</label>
              <textarea id="pageCustAddress" rows="3" placeholder="Nama jalan, nomor rumah, RT/RW, kelurahan, kecamatan, kota/kabupaten, kode pos" required></textarea>
            </div>

            <div class="payment-step-wrap">
              <span class="eyebrow">LANGKAH 2 DARI 2</span>
              <h2>Pilih Metode Pembayaran</h2>

              <div class="payment-options">
                <label class="payment-card active">
                  <input type="radio" name="pagePaymentMethod" value="QRIS" checked>
                  <div class="payment-info">
                    <div class="payment-title">
                      <strong>QRIS (BCA Mobile, GoPay, ShopeePay, Dana)</strong>
                      <span class="badge-recom">Bebas Biaya Admin</span>
                    </div>
                    <p>Scan barcode langsung dari HP Anda. Terverifikasi instan tanpa cek mutasi.</p>
                  </div>
                </label>

                <label class="payment-card">
                  <input type="radio" name="pagePaymentMethod" value="Transfer Virtual Account">
                  <div class="payment-info">
                    <div class="payment-title">
                      <strong>Transfer Virtual Account (BCA, Mandiri, BRI, BNI)</strong>
                    </div>
                    <p>Kode pembayaran otomatis terhubung 24 jam tanpa perlu kirim bukti transfer.</p>
                  </div>
                </label>

                <label class="payment-card">
                  <input type="radio" name="pagePaymentMethod" value="COD (Bayar di Tempat)">
                  <div class="payment-info">
                    <div class="payment-title">
                      <strong>COD (Bayar Tunai ke Kurir)</strong>
                    </div>
                    <p>Bayar tunai ke kurir saat paket parfum sampai di rumah. Wajib nomor WA aktif.</p>
                  </div>
                </label>
              </div>
            </div>

            <button type="submit" class="btn btn-primary btn-block btn-lg">
              PROSES PESANAN SEKARANG &rarr;
            </button>
          </form>
        </div>
      </section>

      <!-- Kolom Kanan: Ringkasan Keranjang Pesanan -->
      <aside class="checkout-summary-col">
        <div class="summary-card">
          <h3>Ringkasan Pesanan</h3>
          <div id="checkoutItemsReview" class="review-items-list"></div>

          <div class="summary-divider"></div>

          <div class="summary-line">
            <span>Subtotal Produk</span>
            <strong id="pageSubtotal">Rp 0</strong>
          </div>
          <div class="summary-line">
            <span>Ongkos Kirim</span>
            <strong id="pageShippingFee" class="free-badge">GRATIS</strong>
          </div>

          <div class="summary-line grand-total">
            <span>Total Tagihan</span>
            <strong id="pageGrandTotal">Rp 0</strong>
          </div>

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

  <script src="script.js"></script>
</body>
</html>
