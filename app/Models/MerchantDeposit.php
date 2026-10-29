<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MerchantDeposit extends Model
{
    use HasFactory;

    protected $fillable = [
        'deposit_number',
        'tenant_id',
        'amount',
        'payment_type',
        'payment_method',
        'status',
        'actual_paid_amount',
        'merchant_profit',
        'payment_proof_file',
        'notes',
        'deposit_date',
        'confirmed_by',
        'confirmed_at',
        'settled_by',
        'settled_at',
        'created_by',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'actual_paid_amount' => 'decimal:2',
        'merchant_profit' => 'decimal:2',
        'deposit_date' => 'date',
        'confirmed_at' => 'datetime',
        'settled_at' => 'datetime',
    ];

    public function tenant()
    {
        return $this->belongsTo(User::class, 'tenant_id');
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function confirmer()
    {
        return $this->belongsTo(User::class, 'confirmed_by');
    }

    public function confirmedBy()
    {
        return $this->belongsTo(User::class, 'confirmed_by');
    }

    public function settler()
    {
        return $this->belongsTo(User::class, 'settled_by');
    }

    public function settledBy()
    {
        return $this->belongsTo(User::class, 'settled_by');
    }

    public function isLunas(): bool
    {
        return $this->status === 'lunas';
    }

    public function isTempo(): bool
    {
        return $this->status === 'tempo';
    }

    public function isMenungguKonfirmasi(): bool
    {
        return $this->status === 'menunggu_konfirmasi';
    }

    public function getStatusLabelAttribute(): string
    {
        return match ($this->status) {
            'menunggu_konfirmasi' => 'Menunggu Konfirmasi',
            'tempo' => 'Konsinyasi (Tempo)',
            'lunas' => 'Lunas',
            'dibatalkan' => 'Dibatalkan',
            default => $this->status,
        };
    }
}
