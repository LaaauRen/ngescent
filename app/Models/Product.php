<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class Product extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'category_id', 'brand', 'name', 'badge', 'vibe',
        'longevity_label', 'longevity_pct', 'sillage_label', 'sillage_pct',
        'notes', 'image', 'is_active', 'is_set',
    ];

    protected $casts = ['is_active' => 'boolean', 'is_set' => 'boolean'];

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function variants()
    {
        return $this->hasMany(ProductVariant::class)->orderBy('sort_order')->orderBy('id');
    }

    /** Produk yang tampil di grid katalog publik. */
    public function scopeListed($q)
    {
        return $q->where('is_active', true)->where('is_set', false)->whereHas('variants');
    }

    public function getImageSrcAttribute(): string
    {
        $img = $this->image;
        if (!$img) return config('shop.default_image');
        return Str::startsWith($img, ['http://', 'https://']) ? $img : Storage::disk('public')->url($img);
    }

    public function getIsSoldOutAttribute(): bool
    {
        return $this->variants->every(fn ($v) => $v->stock <= 0);
    }
}
