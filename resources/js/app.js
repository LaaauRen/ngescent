/* ==========================================================================
   NgeScent. — JS antarmuka saja.
   Semua data (produk, keranjang, pesanan, user) sekarang di server Laravel;
   file ini hanya mengurus drawer, tab, filter, modal, dan toast.
   ========================================================================== */

const $  = (s, r = document) => r.querySelector(s);
const $$ = (s, r = document) => Array.from(r.querySelectorAll(s));
const NS = window.NS || {};

const rp = n => 'Rp ' + Number(n || 0).toLocaleString('id-ID');
const esc = s => String(s ?? '').replace(/[&<>"']/g, c => ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[c]));
const csrf = () => document.querySelector('meta[name="csrf-token"]')?.content || '';

/* ---------- Toast ---------- */
function showToast(message, type = 'success') {
  if (!message) return;
  let box = $('#toastBox');
  if (!box) {
    box = document.createElement('div');
    box.id = 'toastBox';
    box.className = 'toast-box';
    document.body.appendChild(box);
  }
  const t = document.createElement('div');
  t.className = 'toast ' + type;
  t.textContent = message;
  box.appendChild(t);
  setTimeout(() => t.classList.add('show'), 10);
  setTimeout(() => { t.classList.remove('show'); setTimeout(() => t.remove(), 300); }, 3200);
}

function showFlash() {
  const f = $('#flash');
  if (!f) return;
  if (f.dataset.success) showToast(f.dataset.success, 'success');
  if (f.dataset.error) showToast(f.dataset.error, 'error');
}

/* ---------- HTTP helper (JSON + CSRF) ---------- */
async function api(url, { method = 'GET', body } = {}) {
  const res = await fetch(url, {
    method,
    credentials: 'same-origin',
    headers: {
      'Accept': 'application/json',
      'Content-Type': 'application/json',
      'X-Requested-With': 'XMLHttpRequest',
      'X-CSRF-TOKEN': csrf(),
    },
    body: body ? JSON.stringify(body) : undefined,
  });
  const data = await res.json().catch(() => ({}));
  return { ok: res.ok, status: res.status, data };
}

/* ==========================================================================
   KERANJANG (state di session server; di sini hanya tampilan)
   ========================================================================== */
let cart = { items: [], count: 0, subtotal: 0, threshold: NS.threshold || 0 };

function renderCart() {
  const list = $('#cartItemsList');
  const badge = $('#cartBadge');
  const header = $('#cartCountHeader');
  const subtotal = $('#cartSubtotalText');
  const bar = $('#shippingProgress');
  const status = $('#shippingStatusText');

  if (badge) badge.textContent = cart.count;
  if (header) header.textContent = cart.count;
  if (subtotal) subtotal.textContent = rp(cart.subtotal);
  if (!list) return;

  if (!cart.items.length) {
    list.innerHTML = `
      <div style="text-align:center;color:#78716C;padding:4rem 1rem;">
        <i class="fa-solid fa-bag-shopping" style="font-size:3rem;margin-bottom:1rem;opacity:.3;"></i>
        <p style="font-size:.95rem;">Keranjang belanja kamu masih kosong.</p>
        <button type="button" class="btn btn-secondary btn-sm" data-close-cart style="margin-top:1rem;">Mulai Belanja</button>
      </div>`;
  } else {
    list.innerHTML = cart.items.map(i => `
      <div class="cart-item">
        <img src="${esc(i.image)}" alt="${esc(i.title)}" class="cart-item-img">
        <div class="cart-item-details">
          <h4>${esc(i.title)}</h4>
          <p class="cart-item-size">${esc(i.size)}</p>
          <p class="cart-item-price">${rp(i.price)}</p>
          <div class="qty-control">
            <button type="button" class="qty-btn" data-qty data-variant-id="${i.variant_id}" data-delta="-1">-</button>
            <span class="qty-num">${i.qty}</span>
            <button type="button" class="qty-btn" data-qty data-variant-id="${i.variant_id}" data-delta="1">+</button>
          </div>
        </div>
      </div>`).join('');
  }

  if (bar && status) {
    const pct = Math.min((cart.subtotal / cart.threshold) * 100, 100);
    bar.style.width = pct + '%';
    if (cart.subtotal >= cart.threshold) {
      status.innerHTML = '🎉 Selamat! Kamu mendapatkan <strong>GRATIS ONGKIR</strong>.';
      status.style.color = '#2e7d32';
    } else {
      status.innerHTML = `Tambah <strong>${rp(cart.threshold - cart.subtotal)}</strong> lagi untuk <strong>Free Ongkir</strong>!`;
      status.style.color = '';
    }
  }
}

function applyCart(data) {
  cart = { ...cart, ...data };
  renderCart();
  if (data.notices?.length) showToast(data.notices[0], 'error');
}

async function loadCart() {
  const r = await api(NS.cartUrl);
  if (r.ok) applyCart(r.data);
}

async function cartRequest(url, body) {
  const r = await api(url, { method: 'POST', body });
  if (r.status === 419) { showToast('Sesi berakhir. Muat ulang halaman.', 'error'); return false; }
  if (r.data && r.data.items) applyCart(r.data);
  if (!r.ok) { showToast(r.data?.message || 'Terjadi kesalahan.', 'error'); return false; }
  return true;
}

function openCart() {
  const drawer = $('#cartDrawer'), backdrop = $('#cartBackdrop');
  if (!drawer || !backdrop) return;
  renderCart();
  drawer.classList.add('active');
  backdrop.classList.add('active');
  document.body.style.overflow = 'hidden';
}

function closeCart() {
  $('#cartDrawer')?.classList.remove('active');
  $('#cartBackdrop')?.classList.remove('active');
  document.body.style.overflow = '';
}

async function addVariant(variantId) {
  if (await cartRequest(NS.addUrl, { variant_id: Number(variantId) })) openCart();
}

function checkout() {
  if (!cart.count) { showToast('Keranjang belanjamu masih kosong! Silakan pilih parfum terlebih dahulu.', 'error'); return; }
  window.location.href = NS.checkoutUrl;   // server yang menentukan: login dulu atau lanjut checkout
}

/* ==========================================================================
   KATALOG (filter momen, pilih ukuran, tombol beli)
   ========================================================================== */
let catalogFilter = 'all';

function applyCatalogFilter() {
  const grid = $('#productGrid');
  if (!grid) return;
  let visible = 0;
  $$('.product-card', grid).forEach(card => {
    const show = catalogFilter === 'all' || card.dataset.category === catalogFilter;
    card.style.display = show ? 'flex' : 'none';
    if (show) visible++;
  });
  const empty = $('#catalogFilterEmpty');
  if (empty) empty.style.display = visible === 0 ? 'block' : 'none';
}

function stockHint(stock) {
  return stock > 0 && stock <= 5 ? `Sisa ${stock} stok` : '';
}

/* ==========================================================================
   TAB DASHBOARD (pelanggan & admin)
   ========================================================================== */
function initDashTabs() {
  const tabs = $$('.dash-tab');
  if (!tabs.length) return;
  const key = 'ns_tab:' + location.pathname;

  const activate = name => {
    const btn = tabs.find(t => t.dataset.tab === name);
    if (!btn) return false;
    tabs.forEach(b => b.classList.remove('active'));
    $$('.tab-pane').forEach(p => p.classList.remove('active'));
    btn.classList.add('active');
    $('#tab-' + name)?.classList.add('active');
    try { sessionStorage.setItem(key, name); } catch (e) { /* abaikan */ }
    return true;
  };

  tabs.forEach(b => b.addEventListener('click', () => activate(b.dataset.tab)));

  // Setelah form di-submit / halaman dimuat ulang, kembali ke tab terakhir (UI saja)
  const fromHash = location.hash.replace('#', '');
  let saved = null;
  try { saved = sessionStorage.getItem(key); } catch (e) { /* abaikan */ }
  if (!activate(fromHash)) { if (saved) activate(saved); }
}

/* ==========================================================================
   MODAL ADMIN
   ========================================================================== */
function openModal(id) { $('#' + id)?.classList.add('active'); }
function closeModals() { $$('.modal-backdrop.active').forEach(m => m.classList.remove('active')); }

let variantIdx = 0;

function variantRowHTML(v = {}) {
  const i = variantIdx++;
  return `
    <div class="variant-row">
      <input type="hidden" name="variants[${i}][id]" value="${esc(v.id ?? '')}">
      <input type="text"   name="variants[${i}][label]" class="vr-label" placeholder="Ukuran, mis. 50ml" value="${esc(v.label ?? '')}" required>
      <input type="number" name="variants[${i}][price]" class="vr-price" placeholder="Harga (Rp)" min="1" step="500" value="${esc(v.price ?? '')}" required>
      <input type="number" name="variants[${i}][stock]" class="vr-stock" placeholder="Stok" min="0" step="1" value="${esc(v.stock ?? '')}" required>
      <button type="button" class="btn-icon danger" title="Hapus varian" data-remove-variant><i class="fa-solid fa-xmark"></i></button>
    </div>`;
}

function addVariantRow(v) {
  $('#variantRows').insertAdjacentHTML('beforeend', variantRowHTML(v));
}

function setPreview(src) {
  const img = $('#pfImagePreview');
  if (img) img.src = src || img.dataset.default || img.src;
}

function openProductModal(btn) {
  const form = $('#productForm');
  const payload = btn.dataset.product ? JSON.parse(btn.dataset.product) : null;
  const set = (id, val) => { const el = $('#' + id); if (el) el.value = val ?? ''; };

  form.reset();
  variantIdx = 0;
  $('#variantRows').innerHTML = '';
  $('#pfImagePreview').dataset.default = $('#pfImagePreview').dataset.default || $('#pfImagePreview').src;

  $('#productModalTitle').textContent = payload ? 'Ubah Produk' : 'Tambah Produk Baru';
  form.action = payload ? payload.update_url : btn.dataset.storeUrl;
  $('#productMethod').value = payload ? 'PUT' : 'POST';

  set('pfBrand', payload?.brand);
  set('pfName', payload?.name);
  set('pfCategory', payload?.category || $('#pfCategory').options[0]?.value);
  set('pfBadge', payload?.badge);
  set('pfVibe', payload?.vibe);
  set('pfNotes', payload?.notes);
  set('pfLongevityLabel', payload?.longevity_label);
  set('pfLongevityPct', payload?.longevity_pct ?? 70);
  set('pfSillageLabel', payload?.sillage_label);
  set('pfSillagePct', payload?.sillage_pct ?? 70);
  set('pfImageUrl', payload?.image_url);
  $('#pfActive').checked = payload ? !!payload.is_active : true;
  setPreview(payload?.image_src);

  const rows = payload?.variants?.length ? payload.variants : [{ label: '50ml' }, { label: 'Decant 5ml' }];
  rows.forEach(addVariantRow);
  openModal('productModal');
}

/* ==========================================================================
   EVENT DELEGATION (satu tempat untuk semua klik)
   ========================================================================== */
document.addEventListener('click', e => {
  const t = e.target;

  if (t.closest('[data-open-cart]')) return openCart();
  if (t.closest('[data-close-cart]')) return closeCart();
  if (t.closest('[data-checkout]')) return checkout();

  const qty = t.closest('[data-qty]');
  if (qty) return void cartRequest(NS.changeUrl, { variant_id: Number(qty.dataset.variantId), delta: Number(qty.dataset.delta) });

  const addBtn = t.closest('[data-add-variant]');
  if (addBtn) return void addVariant(addBtn.dataset.addVariant);

  // Katalog: pilih ukuran
  const opt = t.closest('.size-opt');
  if (opt && opt.closest('#productGrid')) {
    if (opt.classList.contains('disabled')) return;
    const card = opt.closest('.product-card');
    $$('.size-opt', card).forEach(o => o.classList.remove('active'));
    opt.classList.add('active');
    $('.current-price', card).textContent = rp(opt.dataset.price);
    const hint = $('.stock-hint', card);
    if (hint) hint.textContent = stockHint(Number(opt.dataset.stock));
    return;
  }

  // Katalog: tombol beli
  const buy = t.closest('[data-buy]');
  if (buy) {
    const active = $('.size-opt.active', buy.closest('.product-card'));
    if (active && !active.classList.contains('disabled')) addVariant(active.dataset.variantId);
    return;
  }

  // Katalog: filter momen
  const filterBtn = t.closest('.filter-tabs .tab-btn');
  if (filterBtn) {
    $$('.filter-tabs .tab-btn').forEach(b => b.classList.remove('active'));
    filterBtn.classList.add('active');
    catalogFilter = filterBtn.dataset.filter;
    return applyCatalogFilter();
  }

  // Salin resi
  const copy = t.closest('[data-copy]');
  if (copy) {
    navigator.clipboard?.writeText(copy.dataset.copy);
    return showToast('Nomor resi disalin: ' + copy.dataset.copy);
  }

  // Admin: modal
  const detail = t.closest('[data-order-detail]');
  if (detail) {
    const tpl = $('#tpl-order-' + detail.dataset.orderDetail);
    if (tpl) { $('#orderModalBody').innerHTML = ''; $('#orderModalBody').appendChild(tpl.content.cloneNode(true)); openModal('orderModal'); }
    return;
  }
  const pm = t.closest('[data-product-modal]');
  if (pm) return openProductModal(pm);
  if (t.closest('#addVariantBtn')) return addVariantRow();
  if (t.closest('[data-remove-variant]')) {
    if ($$('#variantRows .variant-row').length <= 1) return showToast('Minimal harus ada 1 varian ukuran.', 'error');
    return t.closest('.variant-row').remove();
  }
  if (t.closest('[data-close-modal]') || t.classList.contains('modal-backdrop')) return closeModals();

  // Checkout: kartu metode pembayaran
  const card = t.closest('.payment-card');
  if (card) {
    $$('.payment-card').forEach(c => c.classList.remove('active'));
    card.classList.add('active');
  }
});

document.addEventListener('keydown', e => { if (e.key === 'Escape') { closeCart(); closeModals(); } });

document.addEventListener('change', e => {
  if (e.target.id === 'pfImageFile' && e.target.files[0]) {
    $('#pfImageUrl').value = '';
    setPreview(URL.createObjectURL(e.target.files[0]));
  }
  if (e.target.id === 'pfImageUrl' && e.target.value.trim()) {
    $('#pfImageFile').value = '';
    setPreview(e.target.value.trim());
  }
});

document.addEventListener('DOMContentLoaded', () => {
  showFlash();
  initDashTabs();
  if ($('#cartBadge')) loadCart();
});
