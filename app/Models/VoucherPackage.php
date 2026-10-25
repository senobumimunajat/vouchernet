<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class VoucherPackage extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'duration_info',
        'printed_price',
        'owner_price',
        'reseller_profit',
        'stock',
        'is_active',
        'description',
    ];

    protected function casts(): array
    {
        return [
            'printed_price' => 'decimal:2',
            'owner_price' => 'decimal:2',
            'reseller_profit' => 'decimal:2',
            'stock' => 'integer',
            'is_active' => 'boolean',
        ];
    }

    public function orders()
    {
        return $this->hasMany(Order::class, 'package_id');
    }

    public function orderItems()
    {
        return $this->hasMany(OrderItem::class, 'package_id');
    }

    public function stockLogs()
    {
        return $this->hasMany(VoucherStockLog::class, 'voucher_package_id')->latest();
    }

    /**
     * Dapatkan tema warna visual yang unik berdasarkan durasi/nama paket
     */
    public function getThemeAttribute(): array
    {
        $text = strtolower($this->name . ' ' . $this->duration_info);

        if (str_contains($text, '3 jam') || str_contains($text, '1 jam') || str_contains($text, '2 jam') || str_contains($text, '4 jam')) {
            return [
                'key' => 'cyan',
                'name' => 'Sky Cyan',
                'gradient_bar' => 'from-cyan-400 via-sky-500 to-blue-500',
                'card_border' => 'border-sky-200 hover:border-sky-400',
                'icon_bg' => 'bg-gradient-to-br from-cyan-400 to-sky-600 shadow-sky-500/25',
                'icon_emoji' => '⚡',
                'badge_theme' => 'bg-sky-50 text-sky-700 border-sky-200',
                'stock_box' => 'bg-sky-50/60 border-sky-200/70',
                'stock_bar' => 'bg-sky-500',
                'btn_action' => 'bg-sky-600 hover:bg-sky-700 text-white shadow-sky-500/20',
                'accent_text' => 'text-sky-700',
            ];
        }

        if (str_contains($text, '6 jam') || str_contains($text, '8 jam') || str_contains($text, '12 jam')) {
            return [
                'key' => 'amber',
                'name' => 'Electric Amber',
                'gradient_bar' => 'from-amber-400 via-orange-500 to-amber-600',
                'card_border' => 'border-amber-200 hover:border-amber-400',
                'icon_bg' => 'bg-gradient-to-br from-amber-400 to-orange-600 shadow-amber-500/25',
                'icon_emoji' => '🔥',
                'badge_theme' => 'bg-amber-50 text-amber-700 border-amber-200',
                'stock_box' => 'bg-amber-50/60 border-amber-200/70',
                'stock_bar' => 'bg-amber-500',
                'btn_action' => 'bg-amber-600 hover:bg-amber-700 text-white shadow-amber-500/20',
                'accent_text' => 'text-amber-700',
            ];
        }

        if (str_contains($text, '24 jam') || str_contains($text, '1 hari') || str_contains($text, 'sehari') || str_contains($text, '2 hari')) {
            return [
                'key' => 'emerald',
                'name' => 'Emerald Green',
                'gradient_bar' => 'from-emerald-400 via-teal-500 to-emerald-600',
                'card_border' => 'border-emerald-200 hover:border-emerald-400',
                'icon_bg' => 'bg-gradient-to-br from-emerald-400 to-teal-600 shadow-emerald-500/25',
                'icon_emoji' => '🌿',
                'badge_theme' => 'bg-emerald-50 text-emerald-700 border-emerald-200',
                'stock_box' => 'bg-emerald-50/60 border-emerald-200/70',
                'stock_bar' => 'bg-emerald-500',
                'btn_action' => 'bg-emerald-600 hover:bg-emerald-700 text-white shadow-emerald-500/20',
                'accent_text' => 'text-emerald-700',
            ];
        }

        if (str_contains($text, '7 hari') || str_contains($text, 'minggu') || str_contains($text, 'mingguan') || str_contains($text, '14 hari')) {
            return [
                'key' => 'indigo',
                'name' => 'Royal Purple',
                'gradient_bar' => 'from-indigo-500 via-purple-500 to-violet-600',
                'card_border' => 'border-indigo-200 hover:border-indigo-400',
                'icon_bg' => 'bg-gradient-to-br from-indigo-500 to-purple-600 shadow-indigo-500/25',
                'icon_emoji' => '🚀',
                'badge_theme' => 'bg-indigo-50 text-indigo-700 border-indigo-200',
                'stock_box' => 'bg-indigo-50/60 border-indigo-200/70',
                'stock_bar' => 'bg-indigo-500',
                'btn_action' => 'bg-indigo-600 hover:bg-indigo-700 text-white shadow-indigo-500/20',
                'accent_text' => 'text-indigo-700',
            ];
        }

        if (str_contains($text, '30 hari') || str_contains($text, 'bulan') || str_contains($text, 'bulanan')) {
            return [
                'key' => 'rose',
                'name' => 'Sunset Rose',
                'gradient_bar' => 'from-rose-400 via-pink-500 to-rose-600',
                'card_border' => 'border-rose-200 hover:border-rose-400',
                'icon_bg' => 'bg-gradient-to-br from-rose-400 to-pink-600 shadow-rose-500/25',
                'icon_emoji' => '👑',
                'badge_theme' => 'bg-rose-50 text-rose-700 border-rose-200',
                'stock_box' => 'bg-rose-50/60 border-rose-200/70',
                'stock_bar' => 'bg-rose-500',
                'btn_action' => 'bg-rose-600 hover:bg-rose-700 text-white shadow-rose-500/20',
                'accent_text' => 'text-rose-700',
            ];
        }

        // Fallbacks rotation
        $fallbacks = [
            [
                'key' => 'blue',
                'name' => 'Sapphire Blue',
                'gradient_bar' => 'from-blue-500 via-indigo-500 to-blue-600',
                'card_border' => 'border-blue-200 hover:border-blue-400',
                'icon_bg' => 'bg-gradient-to-br from-blue-500 to-indigo-600 shadow-blue-500/25',
                'icon_emoji' => '📶',
                'badge_theme' => 'bg-blue-50 text-blue-700 border-blue-200',
                'stock_box' => 'bg-blue-50/60 border-blue-200/70',
                'stock_bar' => 'bg-blue-500',
                'btn_action' => 'bg-blue-600 hover:bg-blue-700 text-white shadow-blue-500/20',
                'accent_text' => 'text-blue-700',
            ],
            [
                'key' => 'teal',
                'name' => 'Teal Wave',
                'gradient_bar' => 'from-teal-400 via-cyan-500 to-teal-600',
                'card_border' => 'border-teal-200 hover:border-teal-400',
                'icon_bg' => 'bg-gradient-to-br from-teal-400 to-cyan-600 shadow-teal-500/25',
                'icon_emoji' => '🌐',
                'badge_theme' => 'bg-teal-50 text-teal-700 border-teal-200',
                'stock_box' => 'bg-teal-50/60 border-teal-200/70',
                'stock_bar' => 'bg-teal-500',
                'btn_action' => 'bg-teal-600 hover:bg-teal-700 text-white shadow-teal-500/20',
                'accent_text' => 'text-teal-700',
            ],
        ];

        return $fallbacks[($this->id ?? 0) % count($fallbacks)];
    }

    /**
     * Tambah stok voucher dan catat riwayat log
     */
    public function addStock(int $quantity, ?int $userId = null, ?string $notes = null): VoucherStockLog
    {
        return DB::transaction(function () use ($quantity, $userId, $notes) {
            $before = $this->stock;
            $after = $before + $quantity;

            $this->stock = $after;
            $this->save();

            return $this->stockLogs()->create([
                'user_id' => $userId,
                'quantity' => $quantity,
                'stock_before' => $before,
                'stock_after' => $after,
                'type' => 'restock',
                'notes' => $notes ?: 'Penambahan stok voucher fisik',
            ]);
        });
    }

    /**
     * Kurangi stok saat terjadi pesanan
     */
    public function deductStock(int $quantity, ?int $userId = null, ?string $notes = null): VoucherStockLog
    {
        return DB::transaction(function () use ($quantity, $userId, $notes) {
            $before = $this->stock;
            $after = max(0, $before - $quantity);

            $this->stock = $after;
            $this->save();

            return $this->stockLogs()->create([
                'user_id' => $userId,
                'quantity' => -$quantity,
                'stock_before' => $before,
                'stock_after' => $after,
                'type' => 'order',
                'notes' => $notes ?: 'Pengurangan stok karena pesanan voucher',
            ]);
        });
    }

    /**
     * Kembalikan stok jika pesanan dibatalkan atau dihapus
     */
    public function restoreStock(int $quantity, ?int $userId = null, ?string $notes = null): VoucherStockLog
    {
        return DB::transaction(function () use ($quantity, $userId, $notes) {
            $before = $this->stock;
            $after = $before + $quantity;

            $this->stock = $after;
            $this->save();

            return $this->stockLogs()->create([
                'user_id' => $userId,
                'quantity' => $quantity,
                'stock_before' => $before,
                'stock_after' => $after,
                'type' => 'refund',
                'notes' => $notes ?: 'Pengembalian stok dari pembatalan pesanan',
            ]);
        });
    }
}
