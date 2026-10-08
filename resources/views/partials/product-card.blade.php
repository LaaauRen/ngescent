@php
  $variants = $p->variants;
  $firstIdx = max(0, $variants->search(fn ($v) => $v->stock > 0) ?: 0);
  $first = $variants[$firstIdx] ?? null;
  $soldOut = $variants->isEmpty() || $variants->every(fn ($v) => $v->stock <= 0);
  $pct = fn ($n) => max(0, min(100, (int) $n));
  $limit = config('shop.low_stock_limit');
@endphp
<article class="product-card" data-category="{{ $p->category?->slug }}" data-product-id="{{ $p->id }}">
  <div class="product-img-wrapper">
    @if($soldOut)
      <span class="badge-status soldout">Stok Habis</span>
    @elseif($p->badge)
      <span class="badge-status">{{ $p->badge }}</span>
    @endif
    <img src="{{ $p->image_src }}" alt="{{ $p->name }} {{ $p->brand }}" loading="lazy">
  </div>
  <div class="product-meta">
    <span class="brand-name">{{ $p->brand }}</span>
    <h3 class="product-title">{{ $p->name }}</h3>

    @if($p->vibe)
      <div class="scent-vibe"><i class="fa-regular fa-compass"></i> Suasana: <strong>{{ $p->vibe }}</strong></div>
    @endif

    <div class="performance-bar">
      <div class="perf-item">
        <span>Ketahanan:</span>
        <div class="bar-track"><div class="bar-fill" style="width: {{ $pct($p->longevity_pct) }}%;"></div></div>
        <small>{{ $p->longevity_label ?: '-' }}</small>
      </div>
      <div class="perf-item">
        <span>Jarak Sebar:</span>
        <div class="bar-track"><div class="bar-fill" style="width: {{ $pct($p->sillage_pct) }}%;"></div></div>
        <small>{{ $p->sillage_label ?: '-' }}</small>
      </div>
    </div>

    @if($p->notes)
      <p class="notes-line"><strong>Notes:</strong> {{ $p->notes }}</p>
    @endif

    <div class="size-selector">
      @foreach($variants as $i => $v)
        @php $out = $v->stock <= 0; @endphp
        <label class="size-opt{{ $i === $firstIdx ? ' active' : '' }}{{ $out ? ' disabled' : '' }}"
               data-variant-id="{{ $v->id }}" data-price="{{ $v->price }}" data-stock="{{ $v->stock }}">
          <input type="radio" name="size_{{ $p->id }}" {{ $i === $firstIdx ? 'checked' : '' }} {{ $out ? 'disabled' : '' }}>
          {{ $v->label }} ({{ $out ? 'Habis' : 'Rp ' . $v->short_price }})
        </label>
      @endforeach
    </div>
    <small class="stock-hint">{{ $first && $first->stock > 0 && $first->stock <= $limit ? "Sisa {$first->stock} stok" : '' }}</small>

    <div class="card-bottom">
      <span class="current-price">Rp {{ number_format($first->price ?? 0, 0, ',', '.') }}</span>
      <button type="button" class="btn-buy" data-buy {{ $soldOut ? 'disabled' : '' }}>{{ $soldOut ? 'Habis' : '+ Masukkan' }}</button>
    </div>
  </div>
</article>
