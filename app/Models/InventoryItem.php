<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class InventoryItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'item_code',
        'name',
        'category',
        'unit',
        'stock',
        'min_stock',
        'unit_price',
        'location',
        'description',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'stock' => 'integer',
            'min_stock' => 'integer',
            'unit_price' => 'decimal:2',
            'is_active' => 'boolean',
        ];
    }

    public function movements()
    {
        return $this->hasMany(InventoryMovement::class);
    }

    public function expenses()
    {
        return $this->hasMany(Expense::class);
    }

    public function isLowStock(): bool
    {
        return $this->stock <= $this->min_stock;
    }

    public static function generateCode(string $category = 'Umum'): string
    {
        $prefix = match(strtolower(trim($category))) {
            'router & gateway', 'router', 'mikrotik' => 'RTR',
            'access point / wifi', 'access point', 'ap' => 'APW',
            'switch & hub', 'switch' => 'SWT',
            'kabel & fiber optic', 'kabel fo', 'kabel' => 'KBL',
            'konverter & sfp', 'media converter', 'htb' => 'CVT',
            'power & adaptor', 'adaptor', 'ups' => 'PWR',
            'alat & tang crimping', 'tools', 'alat' => 'TLS',
            default => 'INV',
        };

        $yearMonth = date('ym');
        $random = strtoupper(Str::random(3));
        $code = "INV-{$prefix}-{$yearMonth}-{$random}";

        // Ensure unique
        while (static::where('item_code', $code)->exists()) {
            $random = strtoupper(Str::random(3));
            $code = "INV-{$prefix}-{$yearMonth}-{$random}";
        }

        return $code;
    }

    /**
     * Catat barang masuk (Restock / Pembelian)
     */
    public function addStock(
        int $quantity,
        ?string $purpose = null,
        ?string $recipient = null,
        ?int $expenseId = null,
        ?string $notes = null,
        ?int $createdBy = null,
        ?string $movementDate = null
    ): InventoryMovement {
        $stockBefore = $this->stock;
        $stockAfter = $stockBefore + $quantity;

        $this->stock = $stockAfter;
        $this->save();

        return $this->movements()->create([
            'type' => 'in',
            'quantity' => $quantity,
            'stock_before' => $stockBefore,
            'stock_after' => $stockAfter,
            'purpose' => $purpose ?? 'Barang Masuk / Restock',
            'recipient' => $recipient ?? 'Gudang Inventaris',
            'movement_date' => $movementDate ?? now()->toDateString(),
            'notes' => $notes,
            'expense_id' => $expenseId,
            'created_by' => $createdBy ?? auth()->id(),
        ]);
    }

    /**
     * Catat barang keluar (Pemasangan di mitra/warga, penggantian, dll)
     */
    public function deductStock(
        int $quantity,
        string $purpose,
        ?string $recipient = null,
        ?string $notes = null,
        ?int $createdBy = null,
        ?string $movementDate = null
    ): InventoryMovement {
        if ($quantity > $this->stock) {
            throw new \InvalidArgumentException("Stok tidak mencukupi! Sisa stok {$this->name} saat ini hanya {$this->stock} {$this->unit}.");
        }

        $stockBefore = $this->stock;
        $stockAfter = $stockBefore - $quantity;

        $this->stock = $stockAfter;
        $this->save();

        return $this->movements()->create([
            'type' => 'out',
            'quantity' => $quantity,
            'stock_before' => $stockBefore,
            'stock_after' => $stockAfter,
            'purpose' => $purpose,
            'recipient' => $recipient ?? 'Operasional Lapangan',
            'movement_date' => $movementDate ?? now()->toDateString(),
            'notes' => $notes,
            'created_by' => $createdBy ?? auth()->id(),
        ]);
    }

    /**
     * Style badge kategori
     */
    public function getCategoryThemeAttribute(): array
    {
        $cat = strtolower($this->category);
        if (str_contains($cat, 'router') || str_contains($cat, 'mikrotik')) {
            return ['bg' => 'bg-indigo-50 text-indigo-700 border-indigo-200', 'icon' => '🌐'];
        }
        if (str_contains($cat, 'access') || str_contains($cat, 'wifi') || str_contains($cat, 'ap')) {
            return ['bg' => 'bg-cyan-50 text-cyan-700 border-cyan-200', 'icon' => '📶'];
        }
        if (str_contains($cat, 'kabel') || str_contains($cat, 'fiber') || str_contains($cat, 'fo')) {
            return ['bg' => 'bg-amber-50 text-amber-700 border-amber-200', 'icon' => '➰'];
        }
        if (str_contains($cat, 'switch') || str_contains($cat, 'hub')) {
            return ['bg' => 'bg-emerald-50 text-emerald-700 border-emerald-200', 'icon' => '🔀'];
        }
        if (str_contains($cat, 'power') || str_contains($cat, 'adaptor') || str_contains($cat, 'ups')) {
            return ['bg' => 'bg-rose-50 text-rose-700 border-rose-200', 'icon' => '⚡'];
        }
        if (str_contains($cat, 'konverter') || str_contains($cat, 'htb') || str_contains($cat, 'sfp')) {
            return ['bg' => 'bg-purple-50 text-purple-700 border-purple-200', 'icon' => '🔄'];
        }
        if (str_contains($cat, 'alat') || str_contains($cat, 'tang') || str_contains($cat, 'tool')) {
            return ['bg' => 'bg-orange-50 text-orange-700 border-orange-200', 'icon' => '🔧'];
        }

        return ['bg' => 'bg-slate-100 text-slate-700 border-slate-200', 'icon' => '📦'];
    }
}
