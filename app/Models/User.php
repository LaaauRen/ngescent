<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    protected $fillable = ['name', 'username', 'email', 'phone', 'password', 'role', 'address', 'is_active'];
    protected $hidden = ['password', 'remember_token'];

    protected function casts(): array
    {
        return ['password' => 'hashed', 'is_active' => 'boolean'];
    }

    /** Simpan nomor HP tanpa 0 / +62 di depan (sama seperti frontend lama). */
    public function setPhoneAttribute($value): void
    {
        $digits = preg_replace('/\D+/', '', (string) $value);
        $digits = preg_replace('/^(62|0)/', '', $digits);
        $this->attributes['phone'] = $digits !== '' ? $digits : null;
    }

    public function isAdmin(): bool
    {
        return $this->role === 'admin';
    }

    public function getFirstNameAttribute(): string
    {
        return explode(' ', trim($this->name))[0] ?? $this->name;
    }

    public function scopeCustomers($q)
    {
        return $q->where('role', 'customer');
    }

    public function orders(): HasMany
    {
        return $this->hasMany(Order::class);
    }
}
