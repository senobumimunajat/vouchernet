<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class InventoryMovement extends Model
{
    use HasFactory;

    protected $fillable = [
        'inventory_item_id',
        'type',
        'quantity',
        'stock_before',
        'stock_after',
        'purpose',
        'recipient',
        'movement_date',
        'notes',
        'expense_id',
        'created_by',
    ];

    protected function casts(): array
    {
        return [
            'quantity' => 'integer',
            'stock_before' => 'integer',
            'stock_after' => 'integer',
            'movement_date' => 'date',
        ];
    }

    public function item()
    {
        return $this->belongsTo(InventoryItem::class, 'inventory_item_id')->withTrashed();
    }

    public function expense()
    {
        return $this->belongsTo(Expense::class, 'expense_id');
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function getTypeLabelAttribute(): string
    {
        return match($this->type) {
            'in' => 'Barang Masuk',
            'out' => 'Barang Keluar',
            'adjustment' => 'Penyesuaian Stok',
            default => ucfirst($this->type),
        };
    }

    public function getTypeThemeAttribute(): array
    {
        return match($this->type) {
            'in' => [
                'badge' => 'bg-emerald-50 text-emerald-700 border-emerald-200',
                'sign' => '+',
                'color' => 'text-emerald-600',
                'icon' => '⬇️',
            ],
            'out' => [
                'badge' => 'bg-rose-50 text-rose-700 border-rose-200',
                'sign' => '-',
                'color' => 'text-rose-600',
                'icon' => '⬆️',
            ],
            default => [
                'badge' => 'bg-blue-50 text-blue-700 border-blue-200',
                'sign' => '~',
                'color' => 'text-blue-600',
                'icon' => '🔄',
            ],
        };
    }
}
