<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class VoucherStockLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'voucher_package_id',
        'user_id',
        'quantity',
        'stock_before',
        'stock_after',
        'type',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'quantity' => 'integer',
            'stock_before' => 'integer',
            'stock_after' => 'integer',
        ];
    }

    public function package()
    {
        return $this->belongsTo(VoucherPackage::class, 'voucher_package_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
