<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    public const CANCELLED = 0;
    public const CONFIRMED = 1;
    public const PACKING   = 2;
    public const SHIPPED   = 3;
    public const DONE      = 4;

    public const LABELS = [
        0 => 'DIBATALKAN', 1 => 'DIKONFIRMASI', 2 => 'SEDANG DIKEMAS', 3 => 'DALAM PERJALANAN', 4 => 'SELESAI',
    ];

    public const WA_LABELS = [
        0 => 'Dibatalkan',
        1 => 'Dikonfirmasi & Masuk Antrean',
        2 => 'Sedang Dikemas Rapi (Double Bubble Wrap)',
        3 => 'Sedang Dalam Perjalanan bersama Kurir J&T',
        4 => 'Telah Sampai di Alamat Tujuan',
    ];

    public const TRACK_STEPS = [
        ['icon' => 'fa-check',         'title' => 'Pesanan Dikonfirmasi',                   'desc' => 'Pesanan masuk dan terverifikasi'],
        ['icon' => 'fa-box-open',      'title' => 'Sedang Dikemas (Double Bubble Wrap)',    'desc' => 'Botol disegel rapat dengan garansi anti pecah'],
        ['icon' => 'fa-truck-fast',    'title' => 'Paket Diserahkan ke Kurir',              'desc' => 'Paket sedang bergerak menuju kotamu'],
        ['icon' => 'fa-house-chimney', 'title' => 'Paket Sampai & Diterima',                'desc' => 'Siapkan uang pas (jika memilih opsi COD)'],
    ];

    protected $fillable = [
        'order_code', 'user_id', 'recipient_name', 'recipient_phone', 'recipient_email', 'shipping_address',
        'payment_method_id', 'status', 'courier', 'tracking_number', 'subtotal', 'shipping_fee',
        'cancelled_at', 'completed_at',
    ];

    protected $casts = ['cancelled_at' => 'datetime', 'completed_at' => 'datetime'];

    /** URL memakai order_code, bukan id numerik. */
    public function getRouteKeyName(): string
    {
        return 'order_code';
    }

    public function user()          { return $this->belongsTo(User::class); }
    public function items()         { return $this->hasMany(OrderItem::class); }
    public function paymentMethod() { return $this->belongsTo(PaymentMethod::class); }
    public function statusLogs()    { return $this->hasMany(OrderStatusLog::class); }

    public static function generateCode(): string
    {
        do {
            $code = 'NS-' . random_int(10000, 99999);
        } while (static::where('order_code', $code)->exists());
        return $code;
    }

    public function getTotalAttribute(): int
    {
        return $this->subtotal + $this->shipping_fee;
    }

    public function getStatusLabelAttribute(): string
    {
        return self::LABELS[$this->status] ?? '-';
    }

    public function getIsActiveAttribute(): bool
    {
        return $this->status >= self::CONFIRMED && $this->status <= self::SHIPPED;
    }

    public function getCanBeCancelledAttribute(): bool
    {
        return in_array($this->status, [self::CONFIRMED, self::PACKING], true);
    }

    public function getDateLabelAttribute(): string
    {
        return $this->created_at->locale('id')->translatedFormat('j M Y');
    }

    public function getItemsSummaryAttribute(): string
    {
        return $this->items->map(fn ($i) => "{$i->product_title} ({$i->variant_label}) x{$i->qty}")->implode('; ');
    }

    /** Link WhatsApp pelanggan -> admin berisi rincian pesanan baru. */
    public function waNewOrderUrl(): string
    {
        $rp = fn ($n) => 'Rp ' . number_format($n, 0, ',', '.');
        $msg  = "*PESANAN BARU - NGESCENT*\nID Pesanan: *#{$this->order_code}*\n----------------------------------\n";
        $msg .= "👤 *Nama:* {$this->recipient_name}\n📧 *Email:* {$this->recipient_email}\n";
        $msg .= "📱 *WhatsApp:* 0{$this->recipient_phone}\n📍 *Alamat:* {$this->shipping_address}\n";
        $msg .= "💳 *Metode Pembayaran:* " . ($this->paymentMethod->name ?? '-') . "\n\n*PRODUK DIPESAN:*\n";
        foreach ($this->items as $i => $it) {
            $msg .= ($i + 1) . ". {$it->product_title} ({$it->variant_label}) x{$it->qty} = " . $rp($it->line_total) . "\n";
        }
        $msg .= "----------------------------------\nSubtotal: " . $rp($this->subtotal) . "\n";
        $msg .= 'Ongkir: ' . ($this->shipping_fee ? $rp($this->shipping_fee) : 'GRATIS') . "\n";
        $msg .= '*TOTAL TAGIHAN: ' . $rp($this->total) . "*\n\nMohon segera diproses. Saya akan cek update resi di dashboard web NgeScent.";

        return 'https://wa.me/' . config('shop.admin_wa') . '?text=' . rawurlencode($msg);
    }

    /** Link WhatsApp admin -> pelanggan berisi update status. */
    public function waUpdateUrl(): string
    {
        $msg  = "Halo Kak *{$this->recipient_name}*! 👋\n\nUpdate pesanan parfum kamu di *NgeScent.* (Order #{$this->order_code}):\n";
        $msg .= '📍 *Status Paket:* ' . self::WA_LABELS[$this->status] . "\n";
        $msg .= $this->tracking_number
            ? "📦 *Nomor Resi J&T:* *{$this->tracking_number}*\n\n"
            : "📦 *Nomor Resi:* akan diinfokan setelah paket diserahkan ke kurir\n\n";
        $msg .= "Kamu bisa cek detail dan pelacakan langsung kapan saja lewat dashboard akun NgeScent.\n\nTerima kasih banyak sudah berbelanja wewangian di NgeScent! ✨";

        return 'https://wa.me/62' . $this->recipient_phone . '?text=' . rawurlencode($msg);
    }
}
