<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Expense extends Model
{
    use HasFactory;

    protected $fillable = [
        'expense_number',
        'title',
        'category',
        'amount',
        'quantity',
        'unit',
        'unit_price',
        'expense_date',
        'notes',
        'receipt_file',
        'is_inventory',
        'inventory_item_id',
        'inventory_quantity',
        'created_by',
    ];

    protected function casts(): array
    {
        return [
            'amount' => 'decimal:2',
            'quantity' => 'integer',
            'unit_price' => 'decimal:2',
            'expense_date' => 'date',
            'is_inventory' => 'boolean',
            'inventory_quantity' => 'integer',
        ];
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function inventoryItem()
    {
        return $this->belongsTo(InventoryItem::class, 'inventory_item_id')->withTrashed();
    }

    public function inventoryMovements()
    {
        return $this->hasMany(InventoryMovement::class, 'expense_id');
    }
}
