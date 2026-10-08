@php
  $rows = collect($rows);
  $max = max(1, (int) $rows->max('total'));
  $mode = $mode ?? 'count';
@endphp
@forelse($rows as $r)
  @php
    $val = match ($mode) {
      'money' => 'Rp ' . number_format($r->total, 0, ',', '.'),
      'stock' => $r->total == 0 ? 'HABIS' : 'sisa ' . $r->total,
      'sold'  => $r->total . ' terjual',
      default => $r->total . ' pesanan',
    };
  @endphp
  <div class="bar-row">
    <div class="bar-label"><span>{{ $r->label }}</span><strong>{{ $val }}</strong></div>
    <div class="bar-track"><div class="bar-fill" style="width:{{ max(4, ($r->total / $max) * 100) }}%"></div></div>
  </div>
@empty
  <p class="text-muted" style="font-size:.85rem;">Belum ada data.</p>
@endforelse
