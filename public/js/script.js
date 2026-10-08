/* ==========================================================================
   STORAGE ENGINE & APPLICATION STATE
   ========================================================================== */
const STORAGE_CART_KEY = 'ngescent_cart';
const STORAGE_USER_KEY = 'ngescent_current_user';
const STORAGE_USERS_LIST = 'ngescent_all_registered_users';
const STORAGE_ORDERS_KEY = 'ngescent_orders';
const STORAGE_PRODUCTS_KEY = 'ngescent_products';
const FREE_SHIPPING_THRESHOLD = 500000;
const SHIPPING_FEE = 20000;        // ongkir flat bila belanja di bawah batas gratis ongkir (ubah sesuai kebutuhan)
const LOW_STOCK_LIMIT = 5;         // stok <= angka ini dianggap "menipis"
const DEFAULT_PRODUCT_IMAGE = 'https://images.unsplash.com/photo-1594035910387-fea47794261f?auto=format&fit=crop&w=700&q=80';
const CATEGORIES = {               // harus sama dengan data-filter pada tab katalog di index.html
  ngantor: 'Daily Ngantor & Kampus',
  dating: 'Kencan Malam (Date Night)',
  outdoor: 'Segar Outdoor / Tropis'
};

/* Data awal katalog (dipakai sekali saja saat localStorage masih kosong).
   Persis sama dengan 4 produk yang sebelumnya ditulis manual di index.html. */
const SEED_PRODUCTS = [
  {
    id: 'p_seed_1', brand: 'HMNS', name: 'Senja di Ubud', category: 'dating', badge: 'Viral #1',
    vibe: 'Sore hangat di kafe kayu bernuansa tenang',
    longevityLabel: '8-10 Jam', longevityPct: 85, sillageLabel: 'Sedang-Tinggi', sillagePct: 75,
    notes: 'Fig, Sandalwood, Warm Bourbon Amber',
    image: 'https://images.unsplash.com/photo-1541643600914-78b084683601?auto=format&fit=crop&w=700&q=80',
    variants: [
      { id: 'v_seed_1a', label: '50ml', price: 389000, stock: 25 },
      { id: 'v_seed_1b', label: 'Decant 5ml', price: 49000, stock: 60 }
    ], active: true, createdAt: 1
  },
  {
    id: 'p_seed_2', brand: 'SAFF & CO.', name: 'S.O.T.B Extrait', category: 'ngantor', badge: '',
    vibe: 'Kemeja putih rapi, segar, memikat di ruangan ber-AC',
    longevityLabel: '10-12 Jam', longevityPct: 95, sillageLabel: 'Tinggi', sillagePct: 80,
    notes: 'Mandarin, Sweet Vanilla Orchid, White Musk',
    image: 'https://images.unsplash.com/photo-1523293182086-7651a899d37f?auto=format&fit=crop&w=700&q=80',
    variants: [
      { id: 'v_seed_2a', label: '30ml', price: 249000, stock: 30 },
      { id: 'v_seed_2b', label: 'Decant 5ml', price: 42000, stock: 60 }
    ], active: true, createdAt: 2
  },
  {
    id: 'p_seed_3', brand: 'MYKONOS', name: 'Aphrodite Extrait', category: 'dating', badge: 'Paling Manis',
    vibe: 'Manis misterius aroma kue kayu manis panggang yang lezat',
    longevityLabel: '9-11 Jam', longevityPct: 90, sillageLabel: 'Semerbak', sillagePct: 85,
    notes: 'Jasmine, Burnt Cinnamon, Warm Amber, Caramel',
    image: 'https://images.unsplash.com/photo-1592945403244-b3fbafd7f539?auto=format&fit=crop&w=700&q=80',
    variants: [
      { id: 'v_seed_3a', label: '50ml', price: 219000, stock: 25 },
      { id: 'v_seed_3b', label: 'Decant 5ml', price: 39000, stock: 60 }
    ], active: true, createdAt: 3
  },
  {
    id: 'p_seed_4', brand: 'PROJECT 1945', name: 'Hujan di Bandung', category: 'outdoor', badge: '',
    vibe: 'Udara segar sejuk setelah gerimis, pepohonan basah',
    longevityLabel: '6-8 Jam', longevityPct: 75, sillageLabel: 'Fresh & Bersih', sillagePct: 70,
    notes: 'Rain Petrichor, Clean Cedarwood, Patchouli',
    image: 'https://images.unsplash.com/photo-1588405748880-12d1d2a59f75?auto=format&fit=crop&w=700&q=80',
    variants: [
      { id: 'v_seed_4a', label: '50ml', price: 369000, stock: 25 },
      { id: 'v_seed_4b', label: 'Decant 5ml', price: 48000, stock: 60 }
    ], active: true, createdAt: 4
  }
];

let cart = JSON.parse(localStorage.getItem(STORAGE_CART_KEY)) || [];
let currentUser = JSON.parse(localStorage.getItem(STORAGE_USER_KEY)) || null;

function formatRupiah(amount) {
  return 'Rp ' + Number(amount || 0).toLocaleString('id-ID');
}
function shortRupiah(amount) {          // 389000 -> "389rb"
  const k = Number(amount || 0) / 1000;
  return (Number.isInteger(k) ? k : k.toFixed(1).replace('.', ',')) + 'rb';
}
function uid(prefix) {
  return prefix + Date.now().toString(36) + Math.random().toString(36).slice(2, 6);
}

/* ==========================================================================
   SINKRONISASI NAVBAR: TOMBOL HANYA BERTULISKAN "MASUK"
   ========================================================================== */
function updateNavbar() {
  const navUserStatus = document.getElementById('navUserStatus');
  const navUserLink = document.getElementById('navUserLink');

  if (navUserStatus && navUserLink) {
    if (currentUser) {
      if (currentUser.role === 'admin') {
        navUserStatus.innerText = 'Admin Toko';
        navUserLink.href = 'admin.html';
      } else {
        navUserStatus.innerText = currentUser.name.split(' ')[0] + ' (Lacak)';
        navUserLink.href = 'dashboard.html';
      }
    } else {
      // Murni hanya teks "Masuk"
      navUserStatus.innerText = 'Masuk';
      navUserLink.href = 'login.html';
    }
  }
}

/* ==========================================================================
   CHECKOUT GUARD: JIKA BELUM LOGIN -> LANGSUNG KE TAB DAFTAR
   ========================================================================== */
function handleCheckoutGuard() {
  if (cart.length === 0) {
    alert("Keranjang belanjamu masih kosong! Silakan pilih parfum terlebih dahulu.");
    return;
  }

  // Jika sudah login -> langsung menuju halaman checkout
  if (currentUser) {
    window.location.href = 'checkout.html';
  } else {
    // Jika belum login -> langsung lempar ke login.html mode DAFTAR
    window.location.href = 'login.html?mode=register&from=checkout';
  }
}

/* ==========================================================================
   LOGIKA LOGIN & DAFTAR DENGAN EMAIL/USERNAME + PASSWORD
   ========================================================================== */
function setAuthMode(mode) {
  const formLogin = document.getElementById('formLogin');
  const formRegister = document.getElementById('formRegister');
  const tabBtnLogin = document.getElementById('tabBtnLogin');
  const tabBtnRegister = document.getElementById('tabBtnRegister');
  const heading = document.getElementById('authHeading');
  const subheading = document.getElementById('authSubheading');

  if (!formLogin || !formRegister) return;

  if (mode === 'register') {
    formLogin.style.display = 'none';
    formRegister.style.display = 'block';
    tabBtnLogin.classList.remove('active');
    tabBtnRegister.classList.add('active');
    heading.innerText = "Daftar Akun Baru";
    subheading.innerText = "Lengkapi Nama, Username, Email, No. WA, dan Password.";
  } else {
    formLogin.style.display = 'block';
    formRegister.style.display = 'none';
    tabBtnLogin.classList.add('active');
    tabBtnRegister.classList.remove('active');
    heading.innerText = "Masuk ke Akun";
    subheading.innerText = "Gunakan Email/Username dan Password untuk melacak pesananmu.";
  }
}

function checkLoginUrlParameters() {
  const urlParams = new URLSearchParams(window.location.search);
  const mode = urlParams.get('mode');
  const isFromCheckout = urlParams.get('from') === 'checkout';
  const noticeBanner = document.getElementById('checkoutNoticeBanner');

  if (mode === 'register') {
    setAuthMode('register');
  }

  if (isFromCheckout && noticeBanner) {
    noticeBanner.style.display = 'flex';
  }
}

// 1. Submit Masuk (Email/Username + Password)
function submitLoginWithPassword() {
  const userOrEmail = document.getElementById('loginUserOrEmail').value.trim();
  const password = document.getElementById('loginPassword').value.trim();

  const allUsers = JSON.parse(localStorage.getItem(STORAGE_USERS_LIST)) || [];

  // Cari user berdasarkan email atau username
  const matchedUser = allUsers.find(u => 
    (u.email.toLowerCase() === userOrEmail.toLowerCase() || u.username.toLowerCase() === userOrEmail.toLowerCase()) &&
    u.password === password
  );

  if (!matchedUser) {
    alert("Email/Username atau Password salah! Jika belum punya akun, silakan klik tab 'Daftar Akun Baru'.");
    return;
  }

  currentUser = matchedUser;
  localStorage.setItem(STORAGE_USER_KEY, JSON.stringify(currentUser));
  updateNavbar();

  // Jika dialihkan saat mau bayar di checkout
  const urlParams = new URLSearchParams(window.location.search);
  if (urlParams.get('from') === 'checkout') {
    window.location.href = 'checkout.html';
  } else {
    window.location.href = 'dashboard.html';
  }
}

// 2. Submit Pendaftaran Akun Baru (Nama, Username, Email, No. WA, Password)
function submitRegisterWithPassword() {
  const name = document.getElementById('regName').value.trim();
  const username = document.getElementById('regUsername').value.trim();
  const email = document.getElementById('regEmail').value.trim();
  const phone = document.getElementById('regPhone').value.trim();
  const password = document.getElementById('regPassword').value.trim();

  if (!name || !username || !email || !phone || !password) {
    alert("Mohon lengkapi seluruh formulir pendaftaran!");
    return;
  }

  let allUsers = JSON.parse(localStorage.getItem(STORAGE_USERS_LIST)) || [];

  // Cek apakah username atau email sudah digunakan
  const isExist = allUsers.some(u => u.email.toLowerCase() === email.toLowerCase() || u.username.toLowerCase() === username.toLowerCase());
  if (isExist) {
    alert("Username atau Email sudah terdaftar! Silakan langsung login di tab 'Masuk'.");
    setAuthMode('login');
    return;
  }

  const newUser = {
    name: name,
    username: username,
    email: email,
    phone: phone.replace(/^0/, ''),
    password: password,
    role: 'customer',
    address: ''
  };

  allUsers.push(newUser);
  localStorage.setItem(STORAGE_USERS_LIST, JSON.stringify(allUsers));

  currentUser = newUser;
  localStorage.setItem(STORAGE_USER_KEY, JSON.stringify(currentUser));
  updateNavbar();

  alert(`Pendaftaran berhasil! Selamat datang, ${name}.`);

  // Jika pendaftaran dipicu oleh tombol checkout
  const urlParams = new URLSearchParams(window.location.search);
  if (urlParams.get('from') === 'checkout') {
    window.location.href = 'checkout.html';
  } else {
    window.location.href = 'dashboard.html';
  }
}

/* ==========================================================================
   ROLE ADMIN TOKO
   ========================================================================== */
function simulateGoogleLogin(role) {
  if (role === 'admin') {
    currentUser = {
      name: "Owner / Admin Toko",
      username: "admin_ngescent",
      email: "owner.ngescent@gmail.com",
      role: "admin",
      phone: "081299998888",
      address: "Headquarters Store NgeScent, Jakarta"
    };
    localStorage.setItem(STORAGE_USER_KEY, JSON.stringify(currentUser));
    alert("Berhasil masuk sebagai Akun Admin Toko!");
    window.location.href = 'admin.html';
  }
}

function handleLogout() {
  localStorage.removeItem(STORAGE_USER_KEY);
  currentUser = null;
  alert("Anda telah keluar dari akun.");
  window.location.href = 'index.html';
}

/* ==========================================================================
   KERANJANG BELANJA (CART DRAWER DI INDEX.HTML) - 100% AMAN DARI NULL
   ========================================================================== */
function openCart() {
  const drawer = document.getElementById('cartDrawer');
  const backdrop = document.getElementById('cartBackdrop');

  if (!drawer || !backdrop) return;

  renderCart();
  drawer.classList.add('active');
  backdrop.classList.add('active');
  document.body.style.overflow = 'hidden';
}

function closeCart() {
  const drawer = document.getElementById('cartDrawer');
  const backdrop = document.getElementById('cartBackdrop');

  if (drawer) drawer.classList.remove('active');
  if (backdrop) backdrop.classList.remove('active');
  document.body.style.overflow = '';
}

function addToCart(title, price, size, image, ref) {
  ref = ref || {};
  const itemKey = ref.productId ? `${ref.productId}:${ref.variantId}` : `${title}-${size}`;
  const existingItem = cart.find(item => item.key === itemKey);

  if (existingItem) {
    existingItem.qty += 1;
  } else {
    cart.push({
      key: itemKey,
      title: title,
      price: Number(price),
      size: size,
      image: image,
      qty: 1,
      productId: ref.productId || null,
      variantId: ref.variantId || null
    });
  }

  saveCart();
  openCart(); // Otomatis membuka drawer saat barang ditambahkan
}

/* Tambah produk dari katalog (cek stok & status tayang dulu) */
function addProductToCart(productId, variantId) {
  const p = findProduct(productId);
  const v = p && p.variants.find(x => x.id === variantId);
  if (!p || !p.active || !v) {
    showToast('Produk ini sudah tidak tersedia.', 'error');
    renderCatalog();
    return;
  }
  const inCart = (cart.find(i => i.key === `${p.id}:${v.id}`) || {}).qty || 0;
  if (inCart + 1 > v.stock) {
    showToast(v.stock > 0 ? `Stok ${v.label} tersisa ${v.stock}.` : 'Maaf, stok sudah habis.', 'error');
    return;
  }
  addToCart(`${p.name} (${p.brand})`, v.price, v.label, p.image, { productId: p.id, variantId: v.id });
}

function updateItemQty(itemKey, delta) {
  const item = cart.find(i => i.key === itemKey);
  if (!item) return;

  if (delta > 0 && item.productId) {
    const v = findVariant(item.productId, item.variantId);
    if (v && item.qty + delta > v.stock) {
      showToast(`Stok tersisa ${v.stock}.`, 'error');
      return;
    }
  }

  item.qty += delta;
  if (item.qty <= 0) {
    cart = cart.filter(i => i.key !== itemKey);
  }
  saveCart();
}

function saveCart() {
  localStorage.setItem(STORAGE_CART_KEY, JSON.stringify(cart));
  renderCart();
}

function renderCart() {
  const cartItemsList = document.getElementById('cartItemsList');
  const cartBadge = document.getElementById('cartBadge');
  const cartCountHeader = document.getElementById('cartCountHeader');
  const cartSubtotalText = document.getElementById('cartSubtotalText');
  const shippingProgress = document.getElementById('shippingProgress');
  const shippingStatusText = document.getElementById('shippingStatusText');

  if (!cartItemsList) return;

  cartItemsList.innerHTML = '';
  let subtotal = 0;
  let totalCount = 0;

  if (cart.length === 0) {
    cartItemsList.innerHTML = `
      <div style="text-align: center; color: #78716C; padding: 4rem 1rem;">
        <i class="fa-solid fa-bag-shopping" style="font-size: 3rem; margin-bottom: 1rem; opacity: 0.3;"></i>
        <p style="font-size: 0.95rem;">Keranjang belanja kamu masih kosong.</p>
        <button type="button" class="btn btn-secondary btn-sm" onclick="closeCart()" style="margin-top: 1rem;">
          Mulai Belanja
        </button>
      </div>
    `;
  } else {
    cart.forEach(item => {
      const itemTotal = item.price * item.qty;
      subtotal += itemTotal;
      totalCount += item.qty;

      const el = document.createElement('div');
      el.className = 'cart-item';
      const k = escapeHtml(item.key);
      el.innerHTML = `
        <img src="${escapeHtml(item.image)}" alt="${escapeHtml(item.title)}" class="cart-item-img">
        <div class="cart-item-details">
          <h4>${escapeHtml(item.title)}</h4>
          <p class="cart-item-size">${escapeHtml(item.size)}</p>
          <p class="cart-item-price">${formatRupiah(item.price)}</p>
          <div class="qty-control">
            <button type="button" class="qty-btn" data-key="${k}" onclick="updateItemQty(this.dataset.key, -1)">-</button>
            <span class="qty-num">${item.qty}</span>
            <button type="button" class="qty-btn" data-key="${k}" onclick="updateItemQty(this.dataset.key, 1)">+</button>
          </div>
        </div>
      `;
      cartItemsList.appendChild(el);
    });
  }

  if (cartBadge) cartBadge.innerText = totalCount;
  if (cartCountHeader) cartCountHeader.innerText = totalCount;
  if (cartSubtotalText) cartSubtotalText.innerText = formatRupiah(subtotal);

  if (shippingProgress && shippingStatusText) {
    const progressPercent = Math.min((subtotal / FREE_SHIPPING_THRESHOLD) * 100, 100);
    shippingProgress.style.width = `${progressPercent}%`;

    if (subtotal >= FREE_SHIPPING_THRESHOLD) {
      shippingStatusText.innerHTML = `🎉 Selamat! Kamu mendapatkan <strong>GRATIS ONGKIR</strong>.`;
      shippingStatusText.style.color = '#2e7d32';
    } else {
      const remaining = FREE_SHIPPING_THRESHOLD - subtotal;
      shippingStatusText.innerHTML = `Tambah <strong>${formatRupiah(remaining)}</strong> lagi untuk <strong>Free Ongkir</strong>!`;
      shippingStatusText.style.color = '';
    }
  }
}

/* ==========================================================================
   KATALOG PRODUK DINAMIS DI INDEX.HTML
   Dirender dari DB.getProducts() — produk yang ditambah/diedit admin
   otomatis tampil di sini. Produk "disembunyikan" tidak ditampilkan.
   ========================================================================== */
let catalogFilter = 'all';

function findProduct(id) {
  return DB.getProducts().find(p => p.id === id) || null;
}
function findVariant(productId, variantId) {
  const p = findProduct(productId);
  return p ? (p.variants.find(v => v.id === variantId) || null) : null;
}
function stockHint(stock) {
  return stock > 0 && stock <= LOW_STOCK_LIMIT ? `Sisa ${stock} stok` : '';
}

function productCardHTML(p) {
  const variants = p.variants || [];
  const firstIdx = Math.max(0, variants.findIndex(v => v.stock > 0));
  const first = variants[firstIdx] || { price: 0, stock: 0 };
  const soldOut = variants.length === 0 || variants.every(v => v.stock <= 0);
  const pct = n => Math.max(0, Math.min(100, Number(n) || 0));

  const badge = soldOut
    ? `<span class="badge-status soldout">Stok Habis</span>`
    : (p.badge ? `<span class="badge-status">${escapeHtml(p.badge)}</span>` : '');

  const opts = variants.map((v, i) => {
    const out = v.stock <= 0;
    return `<label class="size-opt${i === firstIdx ? ' active' : ''}${out ? ' disabled' : ''}" data-variant-id="${escapeHtml(v.id)}" data-price="${v.price}" data-stock="${v.stock}">
      <input type="radio" name="size_${escapeHtml(p.id)}" ${i === firstIdx ? 'checked' : ''} ${out ? 'disabled' : ''}> ${escapeHtml(v.label)} (${out ? 'Habis' : 'Rp ' + shortRupiah(v.price)})
    </label>`;
  }).join('');

  const vibe = p.vibe ? `
          <div class="scent-vibe">
            <i class="fa-regular fa-compass"></i> Suasana: <strong>${escapeHtml(p.vibe)}</strong>
          </div>` : '';
  const perf = `
          <div class="performance-bar">
            <div class="perf-item">
              <span>Ketahanan:</span>
              <div class="bar-track"><div class="bar-fill" style="width: ${pct(p.longevityPct)}%;"></div></div>
              <small>${escapeHtml(p.longevityLabel || '-')}</small>
            </div>
            <div class="perf-item">
              <span>Jarak Sebar:</span>
              <div class="bar-track"><div class="bar-fill" style="width: ${pct(p.sillagePct)}%;"></div></div>
              <small>${escapeHtml(p.sillageLabel || '-')}</small>
            </div>
          </div>`;
  const notes = p.notes ? `<p class="notes-line"><strong>Notes:</strong> ${escapeHtml(p.notes)}</p>` : '';

  return `
      <article class="product-card" data-category="${escapeHtml(p.category)}" data-product-id="${escapeHtml(p.id)}">
        <div class="product-img-wrapper">
          ${badge}
          <img src="${escapeHtml(p.image || DEFAULT_PRODUCT_IMAGE)}" alt="${escapeHtml(p.name)} ${escapeHtml(p.brand)}" loading="lazy">
        </div>
        <div class="product-meta">
          <span class="brand-name">${escapeHtml(p.brand)}</span>
          <h3 class="product-title">${escapeHtml(p.name)}</h3>
          ${vibe}
          ${perf}
          ${notes}
          <div class="size-selector">${opts}</div>
          <small class="stock-hint">${stockHint(first.stock)}</small>
          <div class="card-bottom">
            <span class="current-price">${formatRupiah(first.price)}</span>
            <button type="button" class="btn-buy" ${soldOut ? 'disabled' : ''} onclick="handleCardBuy(this)">
              ${soldOut ? 'Habis' : '+ Masukkan'}
            </button>
          </div>
        </div>
      </article>`;
}

function renderCatalog() {
  const grid = document.getElementById('productGrid');
  if (!grid) return;

  const products = DB.getProducts().filter(p => p.active && (p.variants || []).length > 0);
  if (products.length === 0) {
    grid.innerHTML = `<div class="catalog-empty"><i class="fa-solid fa-spray-can-sparkles"></i><p>Koleksi sedang diperbarui. Silakan cek kembali sebentar lagi.</p></div>`;
    return;
  }
  grid.innerHTML = products.slice().reverse().map(productCardHTML).join('') +
    `<div class="catalog-empty" id="catalogFilterEmpty" style="display:none;"><p>Belum ada produk pada kategori ini.</p></div>`;
  applyCatalogFilter();
}

function applyCatalogFilter() {
  const grid = document.getElementById('productGrid');
  if (!grid) return;
  let visible = 0;
  grid.querySelectorAll('.product-card').forEach(card => {
    const show = catalogFilter === 'all' || card.dataset.category === catalogFilter;
    card.style.display = show ? 'flex' : 'none';
    if (show) visible++;
  });
  const empty = document.getElementById('catalogFilterEmpty');
  if (empty) empty.style.display = visible === 0 ? 'block' : 'none';
}

function handleCardBuy(btn) {
  const card = btn.closest('.product-card');
  const opt = card && card.querySelector('.size-opt.active');
  if (!opt || opt.classList.contains('disabled')) return;
  addProductToCart(card.dataset.productId, opt.dataset.variantId);
}

/* Samakan isi keranjang dengan katalog terbaru (harga, stok, produk dihapus/disembunyikan).
   Mengembalikan daftar pesan perubahan untuk ditampilkan ke pembeli. */
function syncCartWithCatalog() {
  const products = DB.getProducts();
  const msgs = [];
  cart = cart.filter(item => {
    if (!item.productId) return true;                  // item lama / Discovery Set
    const p = products.find(x => x.id === item.productId);
    const v = p && p.variants.find(x => x.id === item.variantId);
    if (!p || !p.active || !v) { msgs.push(`${item.title} sudah tidak tersedia dan dihapus dari keranjang.`); return false; }
    if (v.stock <= 0) { msgs.push(`${item.title} (${v.label}) stok habis dan dihapus dari keranjang.`); return false; }
    if (item.qty > v.stock) { item.qty = v.stock; msgs.push(`Jumlah ${item.title} (${v.label}) disesuaikan dengan stok tersisa (${v.stock}).`); }
    if (item.price !== v.price) { item.price = v.price; msgs.push(`Harga ${item.title} (${v.label}) diperbarui menjadi ${formatRupiah(v.price)}.`); }
    item.title = `${p.name} (${p.brand})`;
    item.size = v.label;
    item.image = p.image;
    return true;
  });
  localStorage.setItem(STORAGE_CART_KEY, JSON.stringify(cart));
  return msgs;
}

function initCatalog() {
  const grid = document.getElementById('productGrid');
  if (!grid) return;

  // Pilih ukuran (delegasi event, tetap jalan setelah katalog dirender ulang)
  grid.addEventListener('click', e => {
    const opt = e.target.closest('.size-opt');
    if (!opt || opt.classList.contains('disabled')) return;
    const card = opt.closest('.product-card');
    card.querySelectorAll('.size-opt').forEach(o => o.classList.remove('active'));
    opt.classList.add('active');
    card.querySelector('.current-price').innerText = formatRupiah(Number(opt.dataset.price));
    const hint = card.querySelector('.stock-hint');
    if (hint) hint.innerText = stockHint(Number(opt.dataset.stock));
  });

  // Filter momen
  document.querySelectorAll('.filter-tabs .tab-btn').forEach(btn => {
    btn.addEventListener('click', () => {
      document.querySelectorAll('.filter-tabs .tab-btn').forEach(b => b.classList.remove('active'));
      btn.classList.add('active');
      catalogFilter = btn.dataset.filter;
      applyCatalogFilter();
    });
  });

  const msgs = syncCartWithCatalog();
  if (msgs.length) showToast(msgs[0], 'error');
  renderCatalog();
  renderCart();

  // Admin mengubah produk di tab lain -> katalog & keranjang ikut diperbarui
  window.addEventListener('storage', e => {
    if (e.key === STORAGE_PRODUCTS_KEY) {
      cart = JSON.parse(localStorage.getItem(STORAGE_CART_KEY)) || [];
      syncCartWithCatalog();
      renderCatalog();
      renderCart();
    }
  });
}

/* ==========================================================================
   HALAMAN CHECKOUT (checkout.html)
   ========================================================================== */
function cartSubtotal() {
  return cart.reduce((a, i) => a + i.price * i.qty, 0);
}
function calcShipping(subtotal) {
  return subtotal <= 0 || subtotal >= FREE_SHIPPING_THRESHOLD ? 0 : SHIPPING_FEE;
}

function renderCheckoutItems() {
  const reviewContainer = document.getElementById('checkoutItemsReview');
  reviewContainer.innerHTML = '';

  cart.forEach(item => {
    const el = document.createElement('div');
    el.className = 'review-item';
    el.innerHTML = `
      <img src="${escapeHtml(item.image)}" alt="${escapeHtml(item.title)}">
      <div class="review-item-info">
        <strong>${escapeHtml(item.title)}</strong>
        <span>${escapeHtml(item.size)} &bull; ${item.qty}x</span>
      </div>
      <span class="review-item-price">${formatRupiah(item.price * item.qty)}</span>
    `;
    reviewContainer.appendChild(el);
  });

  const subtotal = cartSubtotal();
  const shipping = calcShipping(subtotal);
  document.getElementById('pageSubtotal').innerText = formatRupiah(subtotal);
  const shipEl = document.getElementById('pageShippingFee');
  shipEl.innerText = shipping === 0 ? 'GRATIS' : formatRupiah(shipping);
  shipEl.classList.toggle('free-badge', shipping === 0);
  document.getElementById('pageGrandTotal').innerText = formatRupiah(subtotal + shipping);
}

function initCheckoutPage() {
  const reviewContainer = document.getElementById('checkoutItemsReview');
  if (!reviewContainer) return;

  // Wajib login sebelum checkout
  if (!currentUser) {
    window.location.href = 'login.html?mode=register&from=checkout';
    return;
  }

  const msgs = syncCartWithCatalog();

  if (cart.length === 0) {
    alert(msgs.length ? msgs.join('\n') : 'Keranjang belanja kamu masih kosong. Silakan pilih parfum terlebih dahulu.');
    window.location.href = 'index.html#koleksi';
    return;
  }
  if (msgs.length) showToast(msgs[0], 'error');

  renderCheckoutItems();

  // Auto-fill form dengan data akun terdaftar
  document.getElementById('pageCustName').value = currentUser.name || '';
  document.getElementById('pageCustPhone').value = currentUser.phone ? '0' + currentUser.phone : '';
  document.getElementById('pageCustAddress').value = currentUser.address || '';

  const authDisplay = document.getElementById('checkoutUserEmailDisplay');
  if (authDisplay) authDisplay.innerText = `${currentUser.email} (+62 ${currentUser.phone})`;

  document.querySelectorAll('.payment-card').forEach(card => {
    card.addEventListener('click', () => {
      document.querySelectorAll('.payment-card').forEach(c => c.classList.remove('active'));
      card.classList.add('active');
    });
  });
}

function generateOrderId() {
  const existing = new Set(DB.getOrders().map(o => o.id));
  let id;
  do { id = 'NS-' + Math.floor(10000 + Math.random() * 90000); } while (existing.has(id));
  return id;
}

function submitCheckoutPage() {
  if (!currentUser) { window.location.href = 'login.html?mode=register&from=checkout'; return; }

  const name = document.getElementById('pageCustName').value.trim();
  const phone = document.getElementById('pageCustPhone').value.trim();
  const address = document.getElementById('pageCustAddress').value.trim();
  const paymentMethod = document.querySelector('input[name="pagePaymentMethod"]:checked').value;

  if (!/^0?\d{8,13}$/.test(phone.replace(/^\+?62/, '0'))) {
    showToast('Nomor WhatsApp tidak valid.', 'error');
    return;
  }

  // Validasi ulang harga & stok tepat sebelum order dibuat
  const msgs = syncCartWithCatalog();
  if (cart.length === 0) {
    alert(msgs.join('\n') || 'Keranjang kosong.');
    window.location.href = 'index.html#koleksi';
    return;
  }
  if (msgs.length) {
    renderCheckoutItems();
    showToast('Keranjang diperbarui: ' + msgs[0] + ' Periksa kembali lalu proses ulang.', 'error');
    return;
  }

  const submitBtn = document.querySelector('#checkoutForm button[type="submit"]');
  if (submitBtn) submitBtn.disabled = true;

  const subtotal = cartSubtotal();
  const shippingFee = calcShipping(subtotal);
  const orderId = generateOrderId();
  const customerEmail = currentUser.email;

  const newOrder = {
    id: orderId,
    resi: '',                       // diisi admin setelah paket diserahkan ke kurir
    date: new Date().toLocaleDateString('id-ID', { day: 'numeric', month: 'short', year: 'numeric' }),
    customer: { name, phone, address, email: customerEmail },
    paymentMethod: paymentMethod,
    items: cart.map(i => ({ ...i })),
    shippingFee: shippingFee,
    createdAt: Date.now(),
    statusStep: 1
  };

  currentUser.name = name;
  currentUser.phone = phone.replace(/^\+?62/, '').replace(/^0/, '');
  currentUser.address = address;
  DB.saveSession(currentUser);
  const users = DB.getUsers();
  const me = users.find(u => u.email.toLowerCase() === customerEmail.toLowerCase());
  if (me) { me.name = name; me.phone = currentUser.phone; me.address = address; DB.saveUsers(users); }

  applyStock(newOrder.items, -1);   // kurangi stok produk
  const orders = DB.getOrders();
  orders.push(newOrder);
  DB.saveOrders(orders);

  cart = [];
  localStorage.setItem(STORAGE_CART_KEY, JSON.stringify([]));

  let msg = `*PESANAN BARU - NGESCENT*\n`;
  msg += `ID Pesanan: *#${orderId}*\n`;
  msg += `----------------------------------\n`;
  msg += `👤 *Nama:* ${name}\n`;
  msg += `📧 *Email:* ${customerEmail}\n`;
  msg += `📱 *WhatsApp:* ${phone}\n`;
  msg += `📍 *Alamat:* ${address}\n`;
  msg += `💳 *Metode Pembayaran:* ${paymentMethod}\n\n`;
  msg += `*PRODUK DIPESAN:*\n`;

  newOrder.items.forEach((item, idx) => {
    msg += `${idx + 1}. ${item.title} (${item.size}) x${item.qty} = ${formatRupiah(item.price * item.qty)}\n`;
  });

  msg += `----------------------------------\n`;
  msg += `Subtotal: ${formatRupiah(subtotal)}\n`;
  msg += `Ongkir: ${shippingFee === 0 ? 'GRATIS' : formatRupiah(shippingFee)}\n`;
  msg += `*TOTAL TAGIHAN: ${formatRupiah(subtotal + shippingFee)}*\n\n`;
  msg += `Mohon segera diproses. Saya akan cek update resi di dashboard web NgeScent.`;

  window.open(`https://wa.me/${ADMIN_WA}?text=${encodeURIComponent(msg)}`, '_blank');
  window.location.href = 'dashboard.html';
}

/* ==========================================================================
   DATA LAYER (SEMENTARA localStorage)
   Saat backend siap, cukup ganti isi fungsi-fungsi DB di bawah ini
   dengan fetch() ke API — seluruh dashboard user & admin memakainya.
   ========================================================================== */
const DB = {
  // Produk: seed 4 produk awal hanya jika belum pernah ada data (array kosong hasil hapus semua TIDAK di-seed ulang)
  getProducts: () => {
    const raw = localStorage.getItem(STORAGE_PRODUCTS_KEY);
    if (raw === null) {
      localStorage.setItem(STORAGE_PRODUCTS_KEY, JSON.stringify(SEED_PRODUCTS));
      return JSON.parse(JSON.stringify(SEED_PRODUCTS));
    }
    try { return JSON.parse(raw) || []; } catch (e) { return []; }
  },
  // return true/false (false jika penyimpanan browser penuh, mis. foto terlalu besar)
  saveProducts: (list) => {
    try { localStorage.setItem(STORAGE_PRODUCTS_KEY, JSON.stringify(list)); return true; }
    catch (e) { return false; }
  },
  getOrders: () => JSON.parse(localStorage.getItem(STORAGE_ORDERS_KEY)) || [],
  saveOrders: (list) => localStorage.setItem(STORAGE_ORDERS_KEY, JSON.stringify(list)),
  getUsers: () => JSON.parse(localStorage.getItem(STORAGE_USERS_LIST)) || [],
  saveUsers: (list) => localStorage.setItem(STORAGE_USERS_LIST, JSON.stringify(list)),
  saveSession: (user) => localStorage.setItem(STORAGE_USER_KEY, JSON.stringify(user))
};

/* ---------- Helper bersama ---------- */
const ADMIN_WA = '6281299998888';
const TRACK_STEPS = [
  { icon: 'fa-check', title: 'Pesanan Dikonfirmasi', desc: 'Pesanan masuk dan terverifikasi' },
  { icon: 'fa-box-open', title: 'Sedang Dikemas (Double Bubble Wrap)', desc: 'Botol disegel rapat dengan garansi anti pecah' },
  { icon: 'fa-truck-fast', title: 'Paket Diserahkan ke Kurir', desc: 'Paket sedang bergerak menuju kotamu' },
  { icon: 'fa-house-chimney', title: 'Paket Sampai & Diterima', desc: 'Siapkan uang pas (jika memilih opsi COD)' }
];
const STATUS_LABEL = { 0: 'DIBATALKAN', 1: 'DIKONFIRMASI', 2: 'SEDANG DIKEMAS', 3: 'DALAM PERJALANAN', 4: 'SELESAI' };

function escapeHtml(str) {
  return String(str == null ? '' : str).replace(/[&<>"']/g, c => ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[c]));
}
function orderSubtotal(ord) {
  return (ord.items || []).reduce((acc, i) => acc + i.price * i.qty, 0);
}
function orderTotal(ord) {                       // subtotal + ongkir (pesanan lama tanpa ongkir tetap valid)
  return orderSubtotal(ord) + Number(ord.shippingFee || 0);
}
function shippingRowHTML(ord) {
  if (ord.shippingFee === undefined) return '';
  return `<div class="order-ship-row"><span>Ongkos Kirim</span><span>${ord.shippingFee ? formatRupiah(ord.shippingFee) : 'GRATIS'}</span></div>`;
}

/* Kurangi (-1) atau kembalikan (+1) stok sesuai item pesanan.
   Saat backend siap, ini dikerjakan di server dalam satu transaksi dengan pembuatan/pembatalan order. */
function applyStock(items, direction) {
  const products = DB.getProducts();
  let changed = false;
  (items || []).forEach(it => {
    if (!it.productId) return;
    const p = products.find(x => x.id === it.productId);
    const v = p && p.variants.find(x => x.id === it.variantId);
    if (!v) return;
    v.stock = Math.max(0, Number(v.stock) + direction * Number(it.qty));
    changed = true;
  });
  if (changed) DB.saveProducts(products);
}
function stockShortage(items) {                   // daftar item yang stoknya tidak cukup
  const products = DB.getProducts();
  return (items || []).filter(it => {
    if (!it.productId) return false;
    const p = products.find(x => x.id === it.productId);
    const v = p && p.variants.find(x => x.id === it.variantId);
    return !v || v.stock < it.qty;
  }).map(it => it.title);
}
function orderStep(ord) {
  return ord.statusStep === undefined ? 1 : Number(ord.statusStep);
}
function isActiveOrder(ord) {
  const s = orderStep(ord);
  return s >= 1 && s <= 3;
}
function showToast(message, type) {
  let box = document.getElementById('toastBox');
  if (!box) {
    box = document.createElement('div');
    box.id = 'toastBox';
    box.className = 'toast-box';
    document.body.appendChild(box);
  }
  const t = document.createElement('div');
  t.className = 'toast ' + (type || 'success');
  t.innerText = message;
  box.appendChild(t);
  setTimeout(() => t.classList.add('show'), 10);
  setTimeout(() => { t.classList.remove('show'); setTimeout(() => t.remove(), 300); }, 2800);
}
function initDashTabs() {
  const tabs = document.querySelectorAll('.dash-tab');
  tabs.forEach(tabBtn => {
    tabBtn.addEventListener('click', () => {
      tabs.forEach(b => b.classList.remove('active'));
      document.querySelectorAll('.tab-pane').forEach(p => p.classList.remove('active'));
      tabBtn.classList.add('active');
      document.getElementById(`tab-${tabBtn.dataset.tab}`).classList.add('active');
    });
  });
}
function statusBadge(step) {
  return `<span class="status-chip s${step}">${STATUS_LABEL[step] || '-'}</span>`;
}

/* ==========================================================================
   HALAMAN DASHBOARD PELANGGAN (dashboard.html)
   ========================================================================== */
let dashOrders = [];

function getMyOrders() {
  const me = String(currentUser.email || '').toLowerCase();
  return DB.getOrders().filter(o => String(o.customer.email || '').toLowerCase() === me);
}

function initDashboardPage() {
  const dashName = document.getElementById('dashName');
  if (!dashName) return;

  if (!currentUser) { window.location.href = 'login.html'; return; }
  if (currentUser.role === 'admin') { window.location.href = 'admin.html'; return; }

  initDashTabs();
  refreshDashboard();
}

function refreshDashboard() {
  dashOrders = getMyOrders();

  document.getElementById('dashName').innerText = `Halo, ${currentUser.name}`;
  document.getElementById('dashNavName').innerText = currentUser.name.split(' ')[0];
  document.getElementById('dashEmail').innerText = currentUser.email;
  document.getElementById('dashPhone').innerText = currentUser.phone ? `+62 ${currentUser.phone}` : 'Belum diisi';
  document.getElementById('dashAvatar').innerText = currentUser.name.charAt(0).toUpperCase();

  const valid = dashOrders.filter(o => orderStep(o) !== 0);
  document.getElementById('dashStatOrders').innerText = dashOrders.length;
  document.getElementById('dashStatSpent').innerText = formatRupiah(valid.reduce((a, o) => a + orderTotal(o), 0));
  document.getElementById('dashStatActive').innerText = dashOrders.filter(isActiveOrder).length;

  document.getElementById('profName').value = currentUser.name || '';
  document.getElementById('profPhone').value = currentUser.phone ? '0' + currentUser.phone : '';
  document.getElementById('profEmail').value = currentUser.email || '';
  document.getElementById('profAddress').value = currentUser.address || '';
  const pwCard = document.getElementById('passwordCard');
  if (pwCard) pwCard.style.display = currentUser.password ? 'block' : 'none';

  // Picker pesanan: tampilkan yang aktif lebih dulu
  const picker = document.getElementById('dashOrderPicker');
  const select = document.getElementById('dashOrderSelect');
  const sorted = dashOrders.slice().reverse();
  select.innerHTML = sorted.map(o => `<option value="${escapeHtml(o.id)}">#${escapeHtml(o.id)} — ${escapeHtml(STATUS_LABEL[orderStep(o)])}</option>`).join('');
  picker.style.display = sorted.length > 1 ? 'flex' : 'none';

  const firstActive = sorted.find(isActiveOrder) || sorted[0];
  if (firstActive) { select.value = firstActive.id; }
  renderTrackedOrder(firstActive ? firstActive.id : null);
  renderHistory();
}

function renderTrackedOrder(orderId) {
  const box = document.getElementById('dashActiveOrder');
  const ord = dashOrders.find(o => o.id === orderId);

  if (!ord) {
    box.innerHTML = `
      <div class="empty-state">
        <i class="fa-solid fa-bag-shopping"></i>
        <p>Kamu belum pernah melakukan checkout.<br>Yuk pilih parfum viral favoritmu!</p>
        <a href="index.html#koleksi" class="btn btn-primary btn-sm">Lihat Katalog Parfum</a>
      </div>`;
    return;
  }

  const step = orderStep(ord);
  const cancelled = step === 0;

  const timeline = cancelled
    ? `<div class="cancel-banner"><i class="fa-solid fa-circle-xmark"></i> Pesanan ini telah dibatalkan. Hubungi kami jika ada kendala.</div>`
    : `<div class="tracking-timeline">${TRACK_STEPS.map((s, i) => {
        const n = i + 1;
        const cls = step >= 4 || n < step ? 'done' : (n === step ? 'active' : '');
        return `<div class="timeline-step ${cls}">
          <div class="step-icon"><i class="fa-solid ${s.icon}"></i></div>
          <div class="step-content"><strong>${s.title}</strong><small>${s.desc}</small></div>
        </div>`;
      }).join('')}</div>`;

  const items = ord.items.map(item => `
    <div class="mini-item">
      <img src="${escapeHtml(item.image)}" alt="${escapeHtml(item.title)}">
      <div><strong>${escapeHtml(item.title)}</strong>
      <p>${escapeHtml(item.size)} &bull; ${Number(item.qty)}x &bull; ${formatRupiah(item.price)}</p></div>
    </div>`).join('');

  const canCancel = step >= 1 && step <= 2;
  const waText = encodeURIComponent(`Halo NgeScent, saya ingin tanya soal pesanan #${ord.id}.`);

  box.innerHTML = `
    <div class="order-highlight-card">
      <div class="order-head-info">
        <div><span class="label-tiny">NOMOR PESANAN &bull; ${escapeHtml(ord.date || '')}</span><h3>#${escapeHtml(ord.id)}</h3></div>
        ${statusBadge(step)}
      </div>
      <div class="shipping-quick-details">
        <div class="quick-col"><span>Ekspedisi Pengiriman:</span><strong>J&T Express (Reguler)</strong></div>
        <div class="quick-col"><span>Nomor Resi Resmi:</span>
          ${ord.resi
            ? `<div class="resi-copy-box"><span>${escapeHtml(ord.resi)}</span>
          <button type="button" data-resi="${escapeHtml(ord.resi)}" onclick="copyResiDashboard(this.dataset.resi)"><i class="fa-regular fa-copy"></i> Salin</button></div>`
            : `<div class="resi-copy-box resi-pending"><span>Belum tersedia</span></div>`}</div>
        <div class="quick-col"><span>Metode Pembayaran:</span><span class="badge-pay">${escapeHtml(ord.paymentMethod)}</span></div>
      </div>
      ${timeline}
      <div class="order-items-mini">
        <h4>Item dalam Pesanan Ini:</h4>${items}
        ${shippingRowHTML(ord)}
        <div class="order-total-row"><span>Total Tagihan</span><strong>${formatRupiah(orderTotal(ord))}</strong></div>
        <div class="address-mini"><i class="fa-solid fa-location-dot"></i> ${escapeHtml(ord.customer.address)}</div>
      </div>
      <div class="order-actions">
        <a class="btn btn-secondary btn-sm" target="_blank" href="https://wa.me/${ADMIN_WA}?text=${waText}"><i class="fa-brands fa-whatsapp"></i> Tanya Penjual</a>
        ${canCancel ? `<button type="button" class="btn-danger-outline" onclick="cancelMyOrder('${escapeHtml(ord.id)}')">Batalkan Pesanan</button>` : ''}
      </div>
    </div>`;
}

function renderHistory() {
  const list = document.getElementById('dashHistoryList');
  const filter = document.getElementById('dashHistoryFilter').value;
  let rows = dashOrders.slice().reverse();
  if (filter === 'active') rows = rows.filter(isActiveOrder);
  else if (filter !== 'all') rows = rows.filter(o => orderStep(o) === Number(filter));

  if (rows.length === 0) {
    list.innerHTML = `<div class="empty-state"><i class="fa-solid fa-clock-rotate-left"></i><p>Belum ada riwayat belanja untuk filter ini.</p></div>`;
    return;
  }

  list.innerHTML = rows.map(ord => {
    const itemsText = ord.items.map(i => `${escapeHtml(i.title)} (${escapeHtml(i.size)}) x${i.qty}`).join(', ');
    return `
      <div class="history-item">
        <div class="history-head">
          <strong>#${escapeHtml(ord.id)} &bull; ${escapeHtml(ord.date || '')}</strong>
          <span>${statusBadge(orderStep(ord))}</span>
        </div>
        <p style="font-size: 0.88rem; margin-bottom: 0.6rem;">${itemsText}</p>
        <div class="history-foot">
          <span style="font-size: 0.8rem; color: var(--text-muted);">${escapeHtml(ord.paymentMethod)} &bull; Resi J&T: <strong>${escapeHtml(ord.resi || 'Belum tersedia')}</strong></span>
          <strong style="color: var(--terracotta);">${formatRupiah(orderTotal(ord))}</strong>
        </div>
        <div class="history-actions">
          <button type="button" class="btn btn-secondary btn-sm" onclick="trackFromHistory('${escapeHtml(ord.id)}')">Lacak</button>
          <button type="button" class="btn btn-primary btn-sm" onclick="reorder('${escapeHtml(ord.id)}')">Beli Lagi</button>
        </div>
      </div>`;
  }).join('');
}

function trackFromHistory(orderId) {
  document.getElementById('dashOrderSelect').value = orderId;
  renderTrackedOrder(orderId);
  document.querySelector('.dash-tab[data-tab="active-order"]').click();
  window.scrollTo({ top: 0, behavior: 'smooth' });
}

function reorder(orderId) {
  const ord = dashOrders.find(o => o.id === orderId);
  if (!ord) return;
  ord.items.forEach(item => {
    const existing = cart.find(c => c.key === item.key);
    if (existing) existing.qty += item.qty;
    else cart.push({ ...item });
  });
  const msgs = syncCartWithCatalog();           // harga & stok mengikuti katalog terbaru
  if (cart.length === 0) { showToast(msgs[0] || 'Produk sudah tidak tersedia.', 'error'); return; }
  showToast(msgs.length ? msgs[0] : 'Item ditambahkan ke keranjang. Mengalihkan ke checkout...', msgs.length ? 'error' : 'success');
  setTimeout(() => { window.location.href = 'checkout.html'; }, msgs.length ? 1800 : 900);
}

function cancelMyOrder(orderId) {
  if (!confirm(`Yakin ingin membatalkan pesanan #${orderId}?`)) return;
  const orders = DB.getOrders();
  const target = orders.find(o => o.id === orderId);
  const mine = String((target && target.customer.email) || '').toLowerCase() === String(currentUser.email).toLowerCase();
  if (!target || !mine) { showToast('Pesanan tidak ditemukan.', 'error'); return; }
  if (orderStep(target) < 1 || orderStep(target) > 2) { showToast('Pesanan sudah dikirim / selesai, tidak bisa dibatalkan.', 'error'); return; }
  target.statusStep = 0;
  DB.saveOrders(orders);
  applyStock(target.items, +1);        // stok kembali ke gudang
  refreshDashboard();
  showToast(`Pesanan #${orderId} dibatalkan.`);
}

function saveProfile() {
  const name = document.getElementById('profName').value.trim();
  const phone = document.getElementById('profPhone').value.trim().replace(/^0/, '');
  const address = document.getElementById('profAddress').value.trim();
  if (!name || !phone) { showToast('Nama dan nomor WhatsApp wajib diisi.', 'error'); return; }
  if (!/^\d{8,13}$/.test(phone)) { showToast('Nomor WhatsApp tidak valid.', 'error'); return; }

  currentUser.name = name;
  currentUser.phone = phone;
  currentUser.address = address;
  DB.saveSession(currentUser);

  const users = DB.getUsers();
  const me = users.find(u => u.email.toLowerCase() === currentUser.email.toLowerCase());
  if (me) { me.name = name; me.phone = phone; me.address = address; DB.saveUsers(users); }

  refreshDashboard();
  showToast('Profil berhasil diperbarui.');
}

function changePassword() {
  const oldPw = document.getElementById('pwOld').value;
  if (!currentUser.password) { showToast('Akun ini tidak memakai password.', 'error'); return; }
  const newPw = document.getElementById('pwNew').value;
  if (oldPw !== currentUser.password) { showToast('Password lama salah.', 'error'); return; }
  if (newPw.length < 6) { showToast('Password baru minimal 6 karakter.', 'error'); return; }

  currentUser.password = newPw;
  DB.saveSession(currentUser);
  const users = DB.getUsers();
  const me = users.find(u => u.email.toLowerCase() === currentUser.email.toLowerCase());
  if (me) { me.password = newPw; DB.saveUsers(users); }

  document.getElementById('pwOld').value = '';
  document.getElementById('pwNew').value = '';
  showToast('Password berhasil diperbarui.');
}

function copyResiDashboard(resi) {
  if (navigator.clipboard) navigator.clipboard.writeText(resi);
  showToast('Nomor resi disalin: ' + resi);
}

/* ==========================================================================
   HALAMAN ADMIN PORTAL (admin.html)
   ========================================================================== */
function initAdminPanelPage() {
  const tableBody = document.getElementById('adminOrderTableBody');
  if (!tableBody) return;

  if (!currentUser || currentUser.role !== 'admin') {
    alert("Akses Terbatas! Halaman ini hanya untuk Akun Admin Toko.");
    window.location.href = 'login.html';
    return;
  }

  document.getElementById('adminEmailDisplay').innerText = currentUser.email;
  initDashTabs();
  refreshAdmin();
  window.addEventListener('storage', e => {          // pesanan baru dari tab lain muncul otomatis
    if (e.key === STORAGE_ORDERS_KEY || e.key === STORAGE_PRODUCTS_KEY) refreshAdmin();
  });
}

function refreshAdmin() {
  renderAdminOrdersTable();
  renderProductsTable();
  renderCustomersTable();
  renderReports();
}

function renderAdminStats(orders) {
  const valid = orders.filter(o => orderStep(o) !== 0);
  document.getElementById('statTotalOrders').innerText = orders.length;
  document.getElementById('statTotalRevenue').innerText = formatRupiah(valid.reduce((a, o) => a + orderTotal(o), 0));
  document.getElementById('statPendingOrders').innerText = `${orders.filter(isActiveOrder).length} Pesanan`;
  document.getElementById('statCustomers').innerText = DB.getUsers().filter(u => u.role !== 'admin').length;
  const pr = DB.getProducts();
  const low = pr.filter(p => (p.variants || []).some(v => v.stock <= LOW_STOCK_LIMIT)).length;
  const el = document.getElementById('statProducts');
  if (el) el.innerText = `${pr.length} Produk${low ? ' · ' + low + ' stok menipis' : ''}`;
}

function renderAdminOrdersTable() {
  const tableBody = document.getElementById('adminOrderTableBody');
  if (!tableBody) return;

  const orders = DB.getOrders();
  renderAdminStats(orders);

  const q = (document.getElementById('adminSearch').value || '').trim().toLowerCase();
  const sf = document.getElementById('adminStatusFilter').value;

  const rows = orders.slice().reverse().filter(o => {
    if (sf !== 'all' && orderStep(o) !== Number(sf)) return false;
    if (!q) return true;
    return [o.id, o.customer.name, o.customer.email, o.resi].some(v => String(v || '').toLowerCase().includes(q));
  });

  if (rows.length === 0) {
    tableBody.innerHTML = `<tr><td colspan="7" style="text-align:center;color:var(--text-muted);padding:3rem;">
      ${orders.length === 0 ? 'Belum ada pesanan masuk. Lakukan tes order melalui halaman checkout.' : 'Tidak ada pesanan yang cocok dengan pencarian/filter.'}</td></tr>`;
    return;
  }

  tableBody.innerHTML = rows.map(ord => {
    const step = orderStep(ord);
    const itemsSummary = ord.items.map(i => `${escapeHtml(i.title)} (${escapeHtml(i.size)}) x${i.qty}`).join('<br>');
    const opt = (v, t) => `<option value="${v}" ${step === v ? 'selected' : ''}>${t}</option>`;
    const addr = String(ord.customer.address || '');
    return `
      <tr>
        <td><strong>#${escapeHtml(ord.id)}</strong><br><small class="text-muted">${escapeHtml(ord.date || '')}</small></td>
        <td><strong>${escapeHtml(ord.customer.name)}</strong><br>
          <small class="text-muted">${escapeHtml(ord.customer.email)}</small><br>
          <small class="text-muted">+62 ${escapeHtml(String(ord.customer.phone).replace(/^0/, ''))}</small><br>
          <small style="font-size:0.75rem;color:#888;">${escapeHtml(addr.substring(0, 35))}${addr.length > 35 ? '...' : ''}</small></td>
        <td><div style="font-size:0.8rem;margin-bottom:0.3rem;">${itemsSummary}</div>
          <strong style="color:var(--terracotta);">${formatRupiah(orderTotal(ord))}</strong></td>
        <td><span class="badge-pay">${escapeHtml(ord.paymentMethod)}</span></td>
        <td><input type="text" class="resi-input-edit" value="${escapeHtml(ord.resi)}" placeholder="Isi nomor resi"
          onchange="updateOrderResi('${escapeHtml(ord.id)}', this.value)" title="Ketik untuk mengubah nomor resi"></td>
        <td><select class="status-select-edit" onchange="updateOrderStatus('${escapeHtml(ord.id)}', this.value)">
          ${opt(1, '1. Dikonfirmasi')}${opt(2, '2. Dikemas')}${opt(3, '3. Di Jalan (Kurir)')}${opt(4, '4. Selesai / Diterima')}${opt(0, 'Dibatalkan')}
        </select></td>
        <td><div class="row-actions">
          <button type="button" class="btn-send-wa" onclick="sendWhatsAppUpdate('${escapeHtml(ord.id)}')"><i class="fa-brands fa-whatsapp"></i> WA</button>
          <button type="button" class="btn-icon" title="Detail" onclick="openOrderModal('${escapeHtml(ord.id)}')"><i class="fa-regular fa-eye"></i></button>
          <button type="button" class="btn-icon danger" title="Hapus" onclick="deleteOrder('${escapeHtml(ord.id)}')"><i class="fa-regular fa-trash-can"></i></button>
        </div></td>
      </tr>`;
  }).join('');
}

function updateOrderResi(orderId, newResi) {
  const orders = DB.getOrders();
  const target = orders.find(o => o.id === orderId);
  if (!target) return;
  target.resi = newResi.trim();
  DB.saveOrders(orders);
  showToast(`Resi #${orderId} diperbarui: ${target.resi}`);
}

function updateOrderStatus(orderId, newStep) {
  const orders = DB.getOrders();
  const target = orders.find(o => o.id === orderId);
  if (!target) return;
  newStep = Number(newStep);
  const oldStep = orderStep(target);
  if (newStep === oldStep) return;

  // Dibatalkan -> dihidupkan lagi: stok harus cukup lalu dipotong ulang
  if (oldStep === 0 && newStep !== 0) {
    const short = stockShortage(target.items);
    if (short.length) {
      showToast(`Stok tidak cukup untuk mengaktifkan lagi: ${short.join(', ')}`, 'error');
      refreshAdmin();
      return;
    }
    applyStock(target.items, -1);
  }
  // Aktif -> dibatalkan: stok dikembalikan
  if (oldStep !== 0 && newStep === 0) applyStock(target.items, +1);

  target.statusStep = newStep;
  DB.saveOrders(orders);
  refreshAdmin();
  showToast(`Status #${orderId} diubah menjadi "${STATUS_LABEL[newStep]}".`);
}

function deleteOrder(orderId) {
  const target = DB.getOrders().find(o => o.id === orderId);
  if (!target) return;
  if (!confirm(`Hapus permanen pesanan #${orderId}? Tindakan ini tidak bisa dibatalkan.`)) return;
  if (orderStep(target) !== 0 && orderStep(target) !== 4) applyStock(target.items, +1);   // pesanan belum selesai: stok dikembalikan
  DB.saveOrders(DB.getOrders().filter(o => o.id !== orderId));
  refreshAdmin();
  showToast(`Pesanan #${orderId} dihapus.`);
}

function sendWhatsAppUpdate(orderId) {
  const ord = DB.getOrders().find(o => o.id === orderId);
  if (!ord) return;

  const stepLabels = {
    0: "Dibatalkan",
    1: "Dikonfirmasi & Masuk Antrean",
    2: "Sedang Dikemas Rapi (Double Bubble Wrap)",
    3: "Sedang Dalam Perjalanan bersama Kurir J&T",
    4: "Telah Sampai di Alamat Tujuan"
  };

  let msg = `Halo Kak *${ord.customer.name}*! 👋\n\n`;
  msg += `Update pesanan parfum kamu di *NgeScent.* (Order #${ord.id}):\n`;
  msg += `📍 *Status Paket:* ${stepLabels[orderStep(ord)]}\n`;
  msg += ord.resi ? `📦 *Nomor Resi J&T:* *${ord.resi}*\n\n` : `📦 *Nomor Resi:* akan diinfokan setelah paket diserahkan ke kurir\n\n`;
  msg += `Kamu bisa cek detail dan pelacakan langsung kapan saja lewat dashboard akun NgeScent.\n\n`;
  msg += `Terima kasih banyak sudah berbelanja wewangian di NgeScent! ✨`;

  const phone = '62' + String(ord.customer.phone).replace(/^0/, '').replace(/^62/, '');
  window.open(`https://wa.me/${phone}?text=${encodeURIComponent(msg)}`, '_blank');
}

function openOrderModal(orderId) {
  const ord = DB.getOrders().find(o => o.id === orderId);
  if (!ord) return;
  const items = ord.items.map(i => `
    <div class="mini-item"><img src="${escapeHtml(i.image)}" alt="">
    <div style="flex:1"><strong>${escapeHtml(i.title)}</strong><p>${escapeHtml(i.size)} &bull; ${i.qty}x &bull; ${formatRupiah(i.price)}</p></div>
    <strong style="font-size:.85rem">${formatRupiah(i.price * i.qty)}</strong></div>`).join('');
  document.getElementById('orderModalBody').innerHTML = `
    <span class="label-tiny">DETAIL PESANAN &bull; ${escapeHtml(ord.date || '')}</span>
    <h3 style="font-family:var(--font-serif);font-size:1.6rem;margin-bottom:.5rem;">#${escapeHtml(ord.id)} ${statusBadge(orderStep(ord))}</h3>
    <div class="modal-section"><strong>Penerima</strong>
      <p>${escapeHtml(ord.customer.name)}<br>${escapeHtml(ord.customer.email)}<br>+62 ${escapeHtml(String(ord.customer.phone).replace(/^0/, ''))}</p></div>
    <div class="modal-section"><strong>Alamat Pengiriman</strong><p>${escapeHtml(ord.customer.address)}</p></div>
    <div class="modal-section"><strong>Pembayaran & Resi</strong><p>${escapeHtml(ord.paymentMethod)} &bull; ${escapeHtml(ord.resi || 'Resi belum diisi')}</p></div>
    <div class="modal-section"><strong>Item</strong>${items}
      ${shippingRowHTML(ord)}
      <div class="order-total-row"><span>Total Tagihan</span><strong>${formatRupiah(orderTotal(ord))}</strong></div></div>`;
  document.getElementById('orderModal').classList.add('active');
}
function closeOrderModal() {
  document.getElementById('orderModal').classList.remove('active');
}

function exportOrdersCSV() {
  const orders = DB.getOrders();
  if (orders.length === 0) { showToast('Belum ada pesanan untuk diekspor.', 'error'); return; }
  const cell = v => `"${String(v == null ? '' : v).replace(/"/g, '""')}"`;
  const head = ['ID', 'Tanggal', 'Nama', 'Email', 'WhatsApp', 'Alamat', 'Item', 'Metode Bayar', 'Resi', 'Status', 'Ongkir', 'Total'];
  const lines = orders.map(o => [
    o.id, o.date, o.customer.name, o.customer.email, o.customer.phone, o.customer.address,
    o.items.map(i => `${i.title} (${i.size}) x${i.qty}`).join('; '),
    o.paymentMethod, o.resi, STATUS_LABEL[orderStep(o)], Number(o.shippingFee || 0), orderTotal(o)
  ].map(cell).join(','));
  const blob = new Blob(['\ufeff' + [head.map(cell).join(',')].concat(lines).join('\n')], { type: 'text/csv;charset=utf-8;' });
  const a = document.createElement('a');
  a.href = URL.createObjectURL(blob);
  a.download = `pesanan-ngescent-${new Date().toISOString().slice(0, 10)}.csv`;
  a.click();
  URL.revokeObjectURL(a.href);
}

function renderCustomersTable() {
  const body = document.getElementById('adminCustomerBody');
  if (!body) return;
  const users = DB.getUsers().filter(u => u.role !== 'admin');
  const orders = DB.getOrders();

  if (users.length === 0) {
    body.innerHTML = `<tr><td colspan="7" style="text-align:center;color:var(--text-muted);padding:3rem;">Belum ada pelanggan yang mendaftar.</td></tr>`;
    return;
  }
  body.innerHTML = users.slice().reverse().map(u => {
    const mine = orders.filter(o => String(o.customer.email).toLowerCase() === String(u.email).toLowerCase());
    const spent = mine.filter(o => orderStep(o) !== 0).reduce((a, o) => a + orderTotal(o), 0);
    return `<tr>
      <td><strong>${escapeHtml(u.name)}</strong></td>
      <td>${escapeHtml(u.username)}</td>
      <td>${escapeHtml(u.email)}</td>
      <td>+62 ${escapeHtml(u.phone)}</td>
      <td>${mine.length}</td>
      <td><strong style="color:var(--terracotta);">${formatRupiah(spent)}</strong></td>
      <td><a class="btn-send-wa" target="_blank" href="https://wa.me/62${escapeHtml(String(u.phone).replace(/^0/, ''))}"><i class="fa-brands fa-whatsapp"></i> Chat</a></td>
    </tr>`;
  }).join('');
}


/* ==========================================================================
   ADMIN: MANAJEMEN PRODUK
   Tambah / ubah / sembunyikan / hapus produk + varian ukuran (harga & stok).
   Semua lewat DB.getProducts / DB.saveProducts — ganti dengan fetch() ke API
   (lihat database.sql & tabel products, product_variants).
   ========================================================================== */
let editingProductId = null;
let formImageData = '';

function categoryLabel(key) { return CATEGORIES[key] || key; }

function renderProductsTable() {
  const body = document.getElementById('adminProductBody');
  if (!body) return;

  const q = ((document.getElementById('productSearch') || {}).value || '').trim().toLowerCase();
  const cf = (document.getElementById('productCategoryFilter') || {}).value || 'all';
  const all = DB.getProducts();

  const rows = all.slice().reverse().filter(p => {
    if (cf !== 'all' && p.category !== cf) return false;
    if (!q) return true;
    return [p.name, p.brand, p.notes].some(v => String(v || '').toLowerCase().includes(q));
  });

  if (rows.length === 0) {
    body.innerHTML = `<tr><td colspan="6" style="text-align:center;color:var(--text-muted);padding:3rem;">
      ${all.length === 0 ? 'Belum ada produk. Klik "Tambah Produk" untuk menambahkan parfum pertama.' : 'Tidak ada produk yang cocok dengan pencarian/filter.'}</td></tr>`;
    return;
  }

  body.innerHTML = rows.map(p => {
    const vs = (p.variants || []).map(v => {
      const cls = v.stock <= 0 ? 'stock-out' : (v.stock <= LOW_STOCK_LIMIT ? 'stock-low' : '');
      return `<div class="variant-line"><span>${escapeHtml(v.label)}</span><span>${formatRupiah(v.price)}</span>
        <span class="stock-pill ${cls}">${v.stock <= 0 ? 'Habis' : 'Stok ' + v.stock}</span></div>`;
    }).join('') || '<small class="text-muted">Belum ada varian</small>';
    const id = escapeHtml(p.id);
    return `
      <tr class="${p.active ? '' : 'row-hidden'}">
        <td><img class="admin-thumb" src="${escapeHtml(p.image || DEFAULT_PRODUCT_IMAGE)}" alt=""></td>
        <td><strong>${escapeHtml(p.name)}</strong><br><small class="text-muted">${escapeHtml(p.brand)}</small>
          ${p.badge ? `<br><span class="badge-pay" style="margin-top:.3rem;display:inline-block;">${escapeHtml(p.badge)}</span>` : ''}</td>
        <td><small>${escapeHtml(categoryLabel(p.category))}</small></td>
        <td>${vs}</td>
        <td>
          <label class="switch" title="${p.active ? 'Tayang di website' : 'Disembunyikan'}">
            <input type="checkbox" ${p.active ? 'checked' : ''} onchange="toggleProductActive('${id}', this.checked)">
            <span class="switch-slider"></span>
          </label>
          <small class="text-muted" style="display:block;margin-top:.3rem;">${p.active ? 'Tayang' : 'Disembunyikan'}</small>
        </td>
        <td><div class="row-actions">
          <button type="button" class="btn-icon" title="Ubah" onclick="openProductModal('${id}')"><i class="fa-regular fa-pen-to-square"></i></button>
          <button type="button" class="btn-icon danger" title="Hapus" onclick="deleteProduct('${id}')"><i class="fa-regular fa-trash-can"></i></button>
        </div></td>
      </tr>`;
  }).join('');
}

function variantRowHTML(v) {
  v = v || { id: '', label: '', price: '', stock: '' };
  return `<div class="variant-row" data-variant-id="${escapeHtml(v.id)}">
    <input type="text" class="vr-label" placeholder="Ukuran, mis. 50ml" value="${escapeHtml(v.label)}" required>
    <input type="number" class="vr-price" placeholder="Harga (Rp)" min="0" step="500" value="${escapeHtml(v.price)}" required>
    <input type="number" class="vr-stock" placeholder="Stok" min="0" step="1" value="${escapeHtml(v.stock)}" required>
    <button type="button" class="btn-icon danger" title="Hapus varian" onclick="removeVariantRow(this)"><i class="fa-solid fa-xmark"></i></button>
  </div>`;
}
function addVariantRow(v) {
  document.getElementById('variantRows').insertAdjacentHTML('beforeend', variantRowHTML(v));
}
function removeVariantRow(btn) {
  const rows = document.querySelectorAll('#variantRows .variant-row');
  if (rows.length <= 1) { showToast('Minimal harus ada 1 varian ukuran.', 'error'); return; }
  btn.closest('.variant-row').remove();
}

function setFormImage(src) {
  formImageData = src || '';
  const prev = document.getElementById('pfImagePreview');
  prev.src = formImageData || DEFAULT_PRODUCT_IMAGE;
  prev.style.opacity = formImageData ? '1' : '.45';
}

function openProductModal(productId) {
  editingProductId = productId || null;
  const p = productId ? findProduct(productId) : null;
  if (productId && !p) return;

  document.getElementById('productModalTitle').innerText = p ? 'Ubah Produk' : 'Tambah Produk Baru';
  document.getElementById('pfBrand').value = p ? p.brand : '';
  document.getElementById('pfName').value = p ? p.name : '';
  document.getElementById('pfCategory').value = p ? p.category : 'ngantor';
  document.getElementById('pfBadge').value = p ? (p.badge || '') : '';
  document.getElementById('pfVibe').value = p ? (p.vibe || '') : '';
  document.getElementById('pfLongevityLabel').value = p ? (p.longevityLabel || '') : '';
  document.getElementById('pfLongevityPct').value = p ? p.longevityPct : 70;
  document.getElementById('pfSillageLabel').value = p ? (p.sillageLabel || '') : '';
  document.getElementById('pfSillagePct').value = p ? p.sillagePct : 70;
  document.getElementById('pfNotes').value = p ? (p.notes || '') : '';
  document.getElementById('pfActive').checked = p ? !!p.active : true;
  document.getElementById('pfImageUrl').value = p && /^https?:/i.test(p.image || '') ? p.image : '';
  document.getElementById('pfImageFile').value = '';
  setFormImage(p ? p.image : '');

  document.getElementById('variantRows').innerHTML = '';
  (p && p.variants.length ? p.variants : [{ id: '', label: '50ml', price: '', stock: '' }, { id: '', label: 'Decant 5ml', price: '', stock: '' }])
    .forEach(addVariantRow);

  document.getElementById('productModal').classList.add('active');
}
function closeProductModal() {
  document.getElementById('productModal').classList.remove('active');
  editingProductId = null;
}

// Foto diunggah -> dikecilkan (maks 700px, JPEG) agar hemat penyimpanan browser
function handleImageFile(input) {
  const file = input.files && input.files[0];
  if (!file) return;
  if (!/^image\//.test(file.type)) { showToast('File harus berupa gambar.', 'error'); input.value = ''; return; }
  const reader = new FileReader();
  reader.onload = () => {
    const img = new Image();
    img.onload = () => {
      const max = 700;
      const scale = Math.min(1, max / Math.max(img.width, img.height));
      const c = document.createElement('canvas');
      c.width = Math.round(img.width * scale);
      c.height = Math.round(img.height * scale);
      c.getContext('2d').drawImage(img, 0, 0, c.width, c.height);
      document.getElementById('pfImageUrl').value = '';
      setFormImage(c.toDataURL('image/jpeg', 0.82));
    };
    img.onerror = () => showToast('Gambar tidak bisa dibaca.', 'error');
    img.src = reader.result;
  };
  reader.readAsDataURL(file);
}
function handleImageUrl(value) {
  value = value.trim();
  if (!value) { setFormImage(''); return; }
  if (!/^https?:\/\//i.test(value)) { showToast('URL gambar harus diawali http:// atau https://', 'error'); return; }
  document.getElementById('pfImageFile').value = '';
  setFormImage(value);
}

function saveProduct() {
  const val = id => document.getElementById(id).value.trim();
  const brand = val('pfBrand');
  const name = val('pfName');
  if (!brand || !name) { showToast('Merek dan nama produk wajib diisi.', 'error'); return; }

  const variants = [];
  const labels = new Set();
  for (const row of document.querySelectorAll('#variantRows .variant-row')) {
    const label = row.querySelector('.vr-label').value.trim();
    const price = Number(row.querySelector('.vr-price').value);
    const stock = Number(row.querySelector('.vr-stock').value);
    if (!label) { showToast('Nama ukuran varian wajib diisi.', 'error'); return; }
    if (labels.has(label.toLowerCase())) { showToast(`Ukuran "${label}" terdaftar dua kali.`, 'error'); return; }
    if (!(price > 0)) { showToast(`Harga untuk "${label}" harus lebih dari 0.`, 'error'); return; }
    if (!Number.isInteger(stock) || stock < 0) { showToast(`Stok untuk "${label}" harus bilangan bulat ≥ 0.`, 'error'); return; }
    labels.add(label.toLowerCase());
    variants.push({ id: row.dataset.variantId || uid('v_'), label, price, stock });
  }
  if (variants.length === 0) { showToast('Tambahkan minimal 1 varian ukuran.', 'error'); return; }

  const pct = id => Math.max(0, Math.min(100, Number(document.getElementById(id).value) || 0));
  const data = {
    brand, name,
    category: document.getElementById('pfCategory').value,
    badge: val('pfBadge'),
    vibe: val('pfVibe'),
    longevityLabel: val('pfLongevityLabel'), longevityPct: pct('pfLongevityPct'),
    sillageLabel: val('pfSillageLabel'), sillagePct: pct('pfSillagePct'),
    notes: val('pfNotes'),
    image: formImageData || DEFAULT_PRODUCT_IMAGE,
    variants,
    active: document.getElementById('pfActive').checked
  };

  const products = DB.getProducts();
  if (editingProductId) {
    const p = products.find(x => x.id === editingProductId);
    if (!p) { showToast('Produk tidak ditemukan.', 'error'); return; }
    Object.assign(p, data);
  } else {
    products.push({ id: uid('p_'), createdAt: Date.now(), ...data });
  }
  if (!DB.saveProducts(products)) {
    showToast('Penyimpanan penuh. Gunakan foto yang lebih kecil atau pakai URL gambar.', 'error');
    return;
  }
  const wasEdit = !!editingProductId;
  closeProductModal();
  refreshAdmin();
  showToast(wasEdit ? `Produk "${name}" diperbarui.` : `Produk "${name}" ditambahkan ke katalog.`);
}

function toggleProductActive(productId, active) {
  const products = DB.getProducts();
  const p = products.find(x => x.id === productId);
  if (!p) return;
  p.active = !!active;
  DB.saveProducts(products);
  renderProductsTable();
  showToast(`"${p.name}" ${active ? 'ditayangkan di website' : 'disembunyikan dari website'}.`);
}

function deleteProduct(productId) {
  const p = findProduct(productId);
  if (!p) return;
  if (!confirm(`Hapus produk "${p.name}"? Pesanan lama tetap tersimpan, tetapi produk hilang dari katalog.\n\nTips: pakai tombol "Tayang" jika hanya ingin menyembunyikan sementara.`)) return;
  DB.saveProducts(DB.getProducts().filter(x => x.id !== productId));
  refreshAdmin();
  showToast(`Produk "${p.name}" dihapus.`);
}

function barList(entries, fmt) {
  if (entries.length === 0) return `<p class="text-muted" style="font-size:.85rem;">Belum ada data.</p>`;
  const max = Math.max(...entries.map(e => e[1]));
  return entries.map(([label, val]) => `
    <div class="bar-row">
      <div class="bar-label"><span>${escapeHtml(label)}</span><strong>${fmt(val)}</strong></div>
      <div class="bar-track"><div class="bar-fill" style="width:${Math.max(4, (val / max) * 100)}%"></div></div>
    </div>`).join('');
}

function renderReports() {
  const top = document.getElementById('reportTopProducts');
  if (!top) return;
  const orders = DB.getOrders();
  const valid = orders.filter(o => orderStep(o) !== 0);

  const prod = {};
  valid.forEach(o => o.items.forEach(i => { prod[i.title] = (prod[i.title] || 0) + i.qty; }));
  top.innerHTML = barList(Object.entries(prod).sort((a, b) => b[1] - a[1]).slice(0, 6), v => `${v} terjual`);

  const low = [];
  DB.getProducts().forEach(p => (p.variants || []).forEach(v => { if (v.stock <= LOW_STOCK_LIMIT) low.push([`${p.name} — ${p.brand} (${v.label})`, v.stock]); }));
  const lowEl = document.getElementById('reportLowStock');
  if (lowEl) lowEl.innerHTML = barList(low.sort((a, b) => a[1] - b[1]).slice(0, 8), v => v === 0 ? 'HABIS' : `sisa ${v}`);

  const pay = {};
  valid.forEach(o => { pay[o.paymentMethod] = (pay[o.paymentMethod] || 0) + orderTotal(o); });
  document.getElementById('reportPayments').innerHTML = barList(Object.entries(pay).sort((a, b) => b[1] - a[1]), formatRupiah);

  const st = {};
  orders.forEach(o => { const l = STATUS_LABEL[orderStep(o)]; st[l] = (st[l] || 0) + 1; });
  document.getElementById('reportStatuses').innerHTML = barList(Object.entries(st), v => `${v} pesanan`);
}

/* ==========================================================================
   INITIALIZATION
   ========================================================================== */
document.addEventListener('DOMContentLoaded', () => {
  updateNavbar();
  renderCart();
  initCatalog();
  initCheckoutPage();
  initDashboardPage();
  initAdminPanelPage();
  checkLoginUrlParameters();
});
