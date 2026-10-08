<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Product;
use App\Models\StockMovement;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class ProductController extends Controller
{
    public function store(Request $request)
    {
        return $this->save($request, new Product());
    }

    public function update(Request $request, Product $product)
    {
        return $this->save($request, $product);
    }

    public function toggle(Product $product)
    {
        $product->update(['is_active' => !$product->is_active]);
        return back()->with('success', "\"{$product->name}\" " . ($product->is_active ? 'ditayangkan di website' : 'disembunyikan dari website') . '.');
    }

    /** Soft delete: pesanan lama tetap merujuk produk ini. */
    public function destroy(Product $product)
    {
        $product->update(['is_active' => false]);
        $product->delete();
        return back()->with('success', "Produk \"{$product->name}\" dihapus.");
    }

    private function save(Request $request, Product $product)
    {
        $data = $request->validate([
            'brand'           => ['required', 'string', 'max:80'],
            'name'            => ['required', 'string', 'max:120'],
            'category'        => ['required', 'exists:categories,slug'],
            'badge'           => ['nullable', 'string', 'max:24'],
            'vibe'            => ['nullable', 'string', 'max:255'],
            'notes'           => ['nullable', 'string', 'max:255'],
            'longevity_label' => ['nullable', 'string', 'max:40'],
            'longevity_pct'   => ['nullable', 'integer', 'between:0,100'],
            'sillage_label'   => ['nullable', 'string', 'max:40'],
            'sillage_pct'     => ['nullable', 'integer', 'between:0,100'],
            'image_file'      => ['nullable', 'image', 'max:2048'],
            'image_url'       => ['nullable', 'url', 'max:500'],
            'variants'        => ['required', 'array', 'min:1'],
            'variants.*.id'    => ['nullable', 'integer'],
            'variants.*.label' => ['required', 'string', 'max:40'],
            'variants.*.price' => ['required', 'integer', 'min:1'],
            'variants.*.stock' => ['required', 'integer', 'min:0'],
        ], [
            'variants.*.label.required' => 'Nama ukuran varian wajib diisi.',
            'variants.*.price.min'      => 'Harga varian harus lebih dari 0.',
            'variants.*.stock.min'      => 'Stok tidak boleh minus.',
        ]);

        $labels = collect($data['variants'])->pluck('label')->map(fn ($l) => Str::lower(trim($l)));
        if ($labels->duplicates()->isNotEmpty()) {
            throw ValidationException::withMessages(['variants' => 'Ukuran varian tidak boleh ada yang kembar.']);
        }

        // Foto: upload > URL > foto lama > bawaan
        $image = $product->image;
        if ($request->hasFile('image_file')) {
            $this->deleteStoredImage($product->image);
            $image = $request->file('image_file')->store('products', 'public');
        } elseif (!empty($data['image_url'])) {
            if ($image !== $data['image_url']) $this->deleteStoredImage($image);
            $image = $data['image_url'];
        }

        DB::transaction(function () use ($request, $product, $data, $image) {
            $product->fill([
                'category_id'     => Category::where('slug', $data['category'])->value('id'),
                'brand'           => $data['brand'],
                'name'            => $data['name'],
                'badge'           => $data['badge'] ?? null,
                'vibe'            => $data['vibe'] ?? null,
                'notes'           => $data['notes'] ?? null,
                'longevity_label' => $data['longevity_label'] ?? null,
                'longevity_pct'   => $data['longevity_pct'] ?? 70,
                'sillage_label'   => $data['sillage_label'] ?? null,
                'sillage_pct'     => $data['sillage_pct'] ?? 70,
                'image'           => $image,
                'is_active'       => $request->boolean('is_active'),
            ])->save();

            $keep = [];
            foreach (array_values($data['variants']) as $i => $row) {
                $variant = !empty($row['id']) ? $product->variants()->whereKey($row['id'])->first() : null;
                $before = $variant?->stock ?? 0;

                $variant = $variant ?? $product->variants()->make();
                $variant->fill([
                    'label' => trim($row['label']), 'price' => $row['price'],
                    'stock' => $row['stock'], 'sort_order' => $i + 1,
                ])->save();
                $keep[] = $variant->id;

                if ($diff = $variant->stock - $before) {
                    StockMovement::create([
                        'variant_id' => $variant->id, 'change_qty' => $diff,
                        'reason' => $variant->wasRecentlyCreated ? 'initial' : 'adjustment',
                        'created_by' => $request->user()->id,
                    ]);
                }
            }
            $product->variants()->whereNotIn('id', $keep)->delete();   // varian yang dibuang dari form
        });

        return back()->with('success', $product->wasRecentlyCreated
            ? "Produk \"{$product->name}\" ditambahkan ke katalog."
            : "Produk \"{$product->name}\" diperbarui.");
    }

    private function deleteStoredImage(?string $path): void
    {
        if ($path && !Str::startsWith($path, ['http://', 'https://'])) {
            Storage::disk('public')->delete($path);
        }
    }
}
