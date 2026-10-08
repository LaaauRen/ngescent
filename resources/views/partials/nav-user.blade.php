@auth
  @if(auth()->user()->isAdmin())
    <a href="{{ route('admin.index') }}" class="nav-user-btn" id="navUserLink" aria-label="Akun Saya">
      <i class="fa-regular fa-user"></i><span id="navUserStatus">Admin Toko</span>
    </a>
  @else
    <a href="{{ route('dashboard') }}" class="nav-user-btn" id="navUserLink" aria-label="Akun Saya">
      <i class="fa-regular fa-user"></i><span id="navUserStatus">{{ auth()->user()->first_name }} (Lacak)</span>
    </a>
  @endif
@else
  <a href="{{ route('login') }}" class="nav-user-btn" id="navUserLink" aria-label="Akun Saya">
    <i class="fa-regular fa-user"></i><span id="navUserStatus">Masuk</span>
  </a>
@endauth
