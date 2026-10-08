<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('categories', function (Blueprint $table) {
            $table->id();
            $table->string('slug', 40)->unique();      // dipakai sebagai data-filter di katalog
            $table->string('name', 80);
            $table->smallInteger('sort_order')->default(0);
        });

        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->foreignId('category_id')->nullable()->constrained('categories');
            $table->string('brand', 80);
            $table->string('name', 120);
            $table->string('badge', 24)->nullable();
            $table->string('vibe')->nullable();
            $table->string('longevity_label', 40)->nullable();
            $table->unsignedTinyInteger('longevity_pct')->default(70);
            $table->string('sillage_label', 40)->nullable();
            $table->unsignedTinyInteger('sillage_pct')->default(70);
            $table->string('notes')->nullable();
            $table->string('image', 500)->nullable();   // URL penuh ATAU path di disk "public"
            $table->boolean('is_active')->default(true);
            $table->boolean('is_set')->default(false);  // true = Discovery Set (tidak tampil di grid katalog)
            $table->softDeletes();
            $table->timestamps();

            $table->index(['is_active', 'deleted_at', 'category_id']);
            $table->index(['brand', 'name']);
        });

        Schema::create('product_variants', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained()->cascadeOnDelete();
            $table->string('label', 40);
            $table->string('sku', 40)->nullable()->unique();
            $table->unsignedInteger('price');           // Rupiah bulat
            $table->unsignedInteger('stock')->default(0);
            $table->smallInteger('sort_order')->default(0);
            $table->timestamps();

            $table->unique(['product_id', 'label']);
            $table->index('stock');
        });

        Schema::create('payment_methods', function (Blueprint $table) {
            $table->id();
            $table->string('name', 60)->unique();
            $table->boolean('is_active')->default(true);
        });

        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->string('order_code', 20)->unique();             // NS-48213
            $table->foreignId('user_id')->constrained();

            // snapshot data penerima saat checkout
            $table->string('recipient_name', 100);
            $table->string('recipient_phone', 16);
            $table->string('recipient_email', 150);
            $table->text('shipping_address');

            $table->foreignId('payment_method_id')->constrained('payment_methods');
            $table->unsignedTinyInteger('status')->default(1);      // 0 batal | 1 konfirmasi | 2 kemas | 3 jalan | 4 selesai
            $table->string('courier', 40)->default('J&T Express (Reguler)');
            $table->string('tracking_number', 40)->nullable()->index();

            $table->unsignedInteger('subtotal');
            $table->unsignedInteger('shipping_fee')->default(0);

            $table->timestamp('cancelled_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->timestamps();

            $table->index(['user_id', 'created_at']);
            $table->index(['status', 'created_at']);
        });

        Schema::create('order_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained()->cascadeOnDelete();
            $table->foreignId('product_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('variant_id')->nullable()->constrained('product_variants')->nullOnDelete();
            // SNAPSHOT: riwayat tetap benar walau produk diubah/dihapus
            $table->string('product_title', 220);
            $table->string('variant_label', 40);
            $table->string('image', 500)->nullable();
            $table->unsignedInteger('unit_price');
            $table->unsignedInteger('qty');
        });

        Schema::create('order_status_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained()->cascadeOnDelete();
            $table->unsignedTinyInteger('old_status')->nullable();
            $table->unsignedTinyInteger('new_status');
            $table->foreignId('changed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->string('note')->nullable();
            $table->timestamp('created_at')->useCurrent();
        });

        Schema::create('stock_movements', function (Blueprint $table) {
            $table->id();
            $table->foreignId('variant_id')->constrained('product_variants')->cascadeOnDelete();
            $table->foreignId('order_id')->nullable()->constrained()->nullOnDelete();
            $table->integer('change_qty');                           // negatif = keluar
            $table->enum('reason', ['initial', 'restock', 'order', 'cancel', 'reactivate', 'adjustment']);
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('created_at')->useCurrent();
        });
    }

    public function down(): void
    {
        foreach (['stock_movements', 'order_status_logs', 'order_items', 'orders', 'payment_methods', 'product_variants', 'products', 'categories'] as $t) {
            Schema::dropIfExists($t);
        }
    }
};
