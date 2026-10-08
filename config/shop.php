<?php

return [
    'free_shipping_threshold' => (int) env('SHOP_FREE_SHIPPING', 500000),
    'shipping_fee'            => (int) env('SHOP_SHIPPING_FEE', 20000),
    'low_stock_limit'         => (int) env('SHOP_LOW_STOCK', 5),
    'admin_wa'                => env('SHOP_ADMIN_WA', '6281299998888'),
    'courier'                 => 'J&T Express (Reguler)',
    'default_image'           => 'https://images.unsplash.com/photo-1594035910387-fea47794261f?auto=format&fit=crop&w=700&q=80',

    // dipakai hanya oleh DatabaseSeeder untuk membuat akun owner
    'admin_name'     => env('ADMIN_NAME', 'Owner / Admin Toko'),
    'admin_email'    => env('ADMIN_EMAIL', 'owner.ngescent@gmail.com'),
    'admin_username' => env('ADMIN_USERNAME', 'admin_ngescent'),
    'admin_password' => env('ADMIN_PASSWORD'),
];
