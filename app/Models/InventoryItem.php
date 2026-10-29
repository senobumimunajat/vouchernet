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
        'photo',
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

    public function getPhotoUrlAttribute(): ?string
    {
        if ($this->photo) {
            return asset('storage/' . $this->photo);
        }
        return null;
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
        return $this->min_stock > 0 && $this->stock <= $this->min_stock && $this->stock > 0;
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
     * Style badge & tema kategori kartu voucher-like
     */
    public function getCategoryThemeAttribute(): array
    {
        $cat = strtolower($this->category);
        if (str_contains($cat, 'router') || str_contains($cat, 'mikrotik')) {
            return [
                'bg' => 'bg-indigo-50 text-indigo-700 border-indigo-200',
                'badge' => 'bg-indigo-50 text-indigo-700 border-indigo-200/80',
                'icon_bg' => 'bg-indigo-600',
                'gradient' => 'from-indigo-600 via-blue-600 to-indigo-500',
                'stock_box' => 'bg-indigo-50/50 border border-indigo-100/80',
                'card_border' => 'border-indigo-100',
                'stock_bar' => 'bg-indigo-500',
                'icon' => '🌐',
            ];
        }
        if (str_contains($cat, 'access') || str_contains($cat, 'wifi') || str_contains($cat, 'ap')) {
            return [
                'bg' => 'bg-cyan-50 text-cyan-700 border-cyan-200',
                'badge' => 'bg-cyan-50 text-cyan-700 border-cyan-200/80',
                'icon_bg' => 'bg-cyan-600',
                'gradient' => 'from-cyan-500 via-teal-500 to-blue-600',
                'stock_box' => 'bg-cyan-50/50 border border-cyan-100/80',
                'card_border' => 'border-cyan-100',
                'stock_bar' => 'bg-cyan-500',
                'icon' => '📶',
            ];
        }
        if (str_contains($cat, 'kabel') || str_contains($cat, 'fiber') || str_contains($cat, 'fo')) {
            return [
                'bg' => 'bg-amber-50 text-amber-700 border-amber-200',
                'badge' => 'bg-amber-50 text-amber-700 border-amber-200/80',
                'icon_bg' => 'bg-amber-600',
                'gradient' => 'from-amber-500 via-orange-500 to-yellow-500',
                'stock_box' => 'bg-amber-50/50 border border-amber-100/80',
                'card_border' => 'border-amber-100',
                'stock_bar' => 'bg-amber-500',
                'icon' => '➰',
            ];
        }
        if (str_contains($cat, 'switch') || str_contains($cat, 'hub')) {
            return [
                'bg' => 'bg-emerald-50 text-emerald-700 border-emerald-200',
                'badge' => 'bg-emerald-50 text-emerald-700 border-emerald-200/80',
                'icon_bg' => 'bg-emerald-600',
                'gradient' => 'from-emerald-500 via-teal-500 to-green-600',
                'stock_box' => 'bg-emerald-50/50 border border-emerald-100/80',
                'card_border' => 'border-emerald-100',
                'stock_bar' => 'bg-emerald-500',
                'icon' => '🔀',
            ];
        }
        if (str_contains($cat, 'power') || str_contains($cat, 'adaptor') || str_contains($cat, 'ups')) {
            return [
                'bg' => 'bg-rose-50 text-rose-700 border-rose-200',
                'badge' => 'bg-rose-50 text-rose-700 border-rose-200/80',
                'icon_bg' => 'bg-rose-600',
                'gradient' => 'from-rose-500 via-pink-500 to-red-500',
                'stock_box' => 'bg-rose-50/50 border border-rose-100/80',
                'card_border' => 'border-rose-100',
                'stock_bar' => 'bg-rose-500',
                'icon' => '⚡',
            ];
        }
        if (str_contains($cat, 'konverter') || str_contains($cat, 'htb') || str_contains($cat, 'sfp')) {
            return [
                'bg' => 'bg-purple-50 text-purple-700 border-purple-200',
                'badge' => 'bg-purple-50 text-purple-700 border-purple-200/80',
                'icon_bg' => 'bg-purple-600',
                'gradient' => 'from-purple-500 via-indigo-500 to-violet-500',
                'stock_box' => 'bg-purple-50/50 border border-purple-100/80',
                'card_border' => 'border-purple-100',
                'stock_bar' => 'bg-purple-500',
                'icon' => '🔄',
            ];
        }
        if (str_contains($cat, 'alat') || str_contains($cat, 'tang') || str_contains($cat, 'tool')) {
            return [
                'bg' => 'bg-orange-50 text-orange-700 border-orange-200',
                'badge' => 'bg-orange-50 text-orange-700 border-orange-200/80',
                'icon_bg' => 'bg-orange-600',
                'gradient' => 'from-orange-500 via-amber-500 to-yellow-600',
                'stock_box' => 'bg-orange-50/50 border border-orange-100/80',
                'card_border' => 'border-orange-100',
                'stock_bar' => 'bg-orange-500',
                'icon' => '🔧',
            ];
        }

        return [
            'bg' => 'bg-slate-100 text-slate-700 border-slate-200',
            'badge' => 'bg-slate-100 text-slate-700 border-slate-200/80',
            'icon_bg' => 'bg-slate-600',
            'gradient' => 'from-slate-600 via-slate-700 to-slate-800',
            'stock_box' => 'bg-slate-50 border border-slate-200/70',
            'card_border' => 'border-slate-200/70',
            'stock_bar' => 'bg-slate-500',
            'icon' => '📦',
        ];
    }
}
