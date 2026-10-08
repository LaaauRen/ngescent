<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta name="csrf-token" content="{{ csrf_token() }}">
  <title>@yield('title', 'NgeScent.')</title>

  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:ital,wght@0,500;0,600;0,700;1,400;1,600&family=Plus+Jakarta+Sans:wght@300;400;500;600;700&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

  @vite(['resources/css/app.css', 'resources/js/app.js'])
  @stack('head')
</head>
<body class="@yield('body_class')">

  @yield('content')

  {{-- Pesan flash dari server -> ditampilkan sebagai toast oleh app.js --}}
  <div id="flash" hidden
       data-success="{{ session('success') }}"
       data-error="{{ session('error') ?? $errors->first() }}"></div>

  {{-- Konfigurasi untuk JS (URL endpoint, bukan data sensitif) --}}
  <script>
    window.NS = {
      cartUrl:    @json(route('cart.show')),
      addUrl:     @json(route('cart.add')),
      changeUrl:  @json(route('cart.change')),
      checkoutUrl:@json(route('checkout')),
      threshold:  @json(config('shop.free_shipping_threshold')),
    };
  </script>
  @stack('scripts')
</body>
</html>
