// Tab Masuk / Daftar pada halaman login (tanpa logika autentikasi: itu urusan server)
const box = document.getElementById('authBox');

function setAuthMode(mode) {
  const login = document.getElementById('formLogin');
  const register = document.getElementById('formRegister');
  if (!login || !register) return;
  const reg = mode === 'register';

  login.style.display = reg ? 'none' : 'block';
  register.style.display = reg ? 'block' : 'none';
  document.getElementById('tabBtnLogin').classList.toggle('active', !reg);
  document.getElementById('tabBtnRegister').classList.toggle('active', reg);
  document.getElementById('authHeading').textContent = reg ? 'Daftar Akun Baru' : 'Masuk ke Akun';
  document.getElementById('authSubheading').textContent = reg
    ? 'Lengkapi Nama, Username, Email, No. WA, dan Password.'
    : 'Gunakan Email/Username dan Password untuk melacak pesananmu.';
}

document.addEventListener('click', e => {
  const t = e.target.closest('[data-auth-mode]');
  if (t) setAuthMode(t.dataset.authMode);
});

if (box) setAuthMode(box.dataset.mode);
