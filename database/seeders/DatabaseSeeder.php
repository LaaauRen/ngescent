<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\PaymentMethod;
use App\Models\Product;
use App\Models\StockMovement;
use App\Models\User;
use Illuminate\Database\Seeder;
use RuntimeException;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        foreach ([
            ['ngantor', 'Daily Ngantor & Kampus', 1],
            ['dating',  'Kencan Malam (Date Night)', 2],
            ['outdoor', 'Segar Outdoor / Tropis', 3],
        ] as [$slug, $name, $order]) {
            Category::updateOrCreate(['slug' => $slug], ['name' => $name, 'sort_order' => $order]);
        }

        foreach (['QRIS', 'Transfer Virtual Account', 'COD (Bayar di Tempat)'] as $name) {
            PaymentMethod::firstOrCreate(['name' => $name]);
        }

        // Akun owner: password WAJIB dari .env (tidak ada password bawaan yang lemah)
        $pw = config('shop.admin_password');
        if (!$pw || strlen($pw) < 8) {
            throw new RuntimeException('Isi ADMIN_PASSWORD (min. 8 karakter) di file .env sebelum menjalankan seeder.');
        }
        User::updateOrCreate(
            ['email' => config('shop.admin_email')],
            [
                'name' => config('shop.admin_name'), 'username' => config('shop.admin_username'),
                'phone' => '81299998888', 'password' => $pw, 'role' => 'admin',
                'address' => 'Headquarters Store NgeScent, Jakarta',
            ]
        );

        if (Product::withTrashed()->exists()) return;   // seed produk hanya sekali

        $cat = fn ($slug) => Category::where('slug', $slug)->value('id');

        $products = [
            ['dating', 'HMNS', 'Senja di Ubud', 'Viral #1', 'Sore hangat di kafe kayu bernuansa tenang', '8-10 Jam', 85, 'Sedang-Tinggi', 75,
                'Fig, Sandalwood, Warm Bourbon Amber', 'https://images.unsplash.com/photo-1541643600914-78b084683601?auto=format&fit=crop&w=700&q=80',
                [['50ml', 389000, 25], ['Decant 5ml', 49000, 60]]],
            ['ngantor', 'SAFF & CO.', 'S.O.T.B Extrait', null, 'Kemeja putih rapi, segar, memikat di ruangan ber-AC', '10-12 Jam', 95, 'Tinggi', 80,
                'Mandarin, Sweet Vanilla Orchid, White Musk', 'https://images.unsplash.com/photo-1523293182086-7651a899d37f?auto=format&fit=crop&w=700&q=80',
                [['30ml', 249000, 30], ['Decant 5ml', 42000, 60]]],
            ['dating', 'MYKONOS', 'Aphrodite Extrait', 'Paling Manis', 'Manis misterius aroma kue kayu manis panggang yang lezat', '9-11 Jam', 90, 'Semerbak', 85,
                'Jasmine, Burnt Cinnamon, Warm Amber, Caramel', 'https://images.unsplash.com/photo-1592945403244-b3fbafd7f539?auto=format&fit=crop&w=700&q=80',
                [['50ml', 219000, 25], ['Decant 5ml', 39000, 60]]],
            ['outdoor', 'PROJECT 1945', 'Hujan di Bandung', null, 'Udara segar sejuk setelah gerimis, pepohonan basah', '6-8 Jam', 75, 'Fresh & Bersih', 70,
                'Rain Petrichor, Clean Cedarwood, Patchouli', 'https://images.unsplash.com/photo-1588405748880-12d1d2a59f75?auto=format&fit=crop&w=700&q=80',
                [['50ml', 369000, 25], ['Decant 5ml', 48000, 60]]],
        ];

        foreach ($products as [$c, $brand, $name, $badge, $vibe, $lLabel, $lPct, $sLabel, $sPct, $notes, $img, $variants]) {
            $p = Product::create([
                'category_id' => $cat($c), 'brand' => $brand, 'name' => $name, 'badge' => $badge, 'vibe' => $vibe,
                'longevity_label' => $lLabel, 'longevity_pct' => $lPct, 'sillage_label' => $sLabel, 'sillage_pct' => $sPct,
                'notes' => $notes, 'image' => $img, 'is_active' => true,
            ]);
            $this->variants($p, $variants);
        }

        // Discovery Set: produk khusus (is_set) -> tidak masuk grid, tampil di section Discovery
        $set = Product::create([
            'category_id' => null, 'brand' => 'NgeScent', 'name' => 'Discovery Set Viral (4x 5ml)',
            'image' => 'https://images.unsplash.com/photo-1608571423902-eed4a5ad8108?auto=format&fit=crop&w=700&q=80',
            'is_active' => true, 'is_set' => true,
        ]);
        $this->variants($set, [['Discovery Box', 149000, 100]]);
    }

    private function variants(Product $p, array $rows): void
    {
        foreach ($rows as $i => [$label, $price, $stock]) {
            $v = $p->variants()->create(['label' => $label, 'price' => $price, 'stock' => $stock, 'sort_order' => $i + 1]);
            StockMovement::create(['variant_id' => $v->id, 'change_qty' => $stock, 'reason' => 'initial']);
        }
    }
}
