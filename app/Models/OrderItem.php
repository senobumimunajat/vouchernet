<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OrderItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'order_id',
        'package_id',
        'quantity',
        'printed_price_each',
        'owner_price_each',
        'total_printed_amount',
        'total_owner_amount',
        'total_reseller_profit',
    ];

    protected function casts(): array
    {
        return [
            'quantity' => 'integer',
            'printed_price_each' => 'decimal:2',
            'owner_price_each' => 'decimal:2',
            'total_printed_amount' => 'decimal:2',
            'total_owner_amount' => 'decimal:2',
            'total_reseller_profit' => 'decimal:2',
        ];
    }

    public function order()
    {
        return $this->belongsTo(Order::class, 'order_id');
    }

    public function package()
    {
        return $this->belongsTo(VoucherPackage::class, 'package_id');
    }
}
