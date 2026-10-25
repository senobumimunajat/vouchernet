<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    use HasFactory;

    protected $fillable = [
        'order_number',
        'tenant_id',
        'customer_type',
        'customer_name',
        'package_id',
        'quantity',
        'printed_price_each',
        'owner_price_each',
        'total_printed_amount',
        'total_owner_amount',
        'total_reseller_profit',
        'payment_method',
        'payment_proof_file',
        'voucher_pdf_file',
        'status',
        'confirmed_by',
        'confirmed_at',
        'delete_requested_by',
        'delete_reason',
        'delete_requested_at',
        'delete_approved_by',
        'delete_approved_at',
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
            'confirmed_at' => 'datetime',
            'delete_requested_at' => 'datetime',
            'delete_approved_at' => 'datetime',
        ];
    }

    public function tenant()
    {
        return $this->belongsTo(User::class, 'tenant_id');
    }

    public function items()
    {
        return $this->hasMany(OrderItem::class, 'order_id');
    }

    public function package()
    {
        return $this->belongsTo(VoucherPackage::class, 'package_id');
    }

    public function confirmedByUser()
    {
        return $this->belongsTo(User::class, 'confirmed_by');
    }

    public function deleteRequestedByUser()
    {
        return $this->belongsTo(User::class, 'delete_requested_by');
    }

    public function deleteApprovedByUser()
    {
        return $this->belongsTo(User::class, 'delete_approved_by');
    }

    public function getCustomerDisplayNameAttribute(): string
    {
        if ($this->customer_type === 'direct') {
            return $this->customer_name ?: 'Pelanggan Langsung (Walk-In)';
        }
        return $this->tenant ? ($this->tenant->shop_name ?: $this->tenant->name) : ($this->customer_name ?: 'Pelanggan Umum');
    }

    public function getPackageNamesSummaryAttribute(): string
    {
        $items = $this->relationLoaded('items') ? $this->items : $this->items()->with('package')->get();
        if ($items->isNotEmpty()) {
            return $items->map(function ($it) {
                $name = $it->package?->name ?? 'Paket';
                return "{$name} ({$it->quantity} lbr)";
            })->implode(', ');
        }
        return $this->package ? "{$this->package->name} ({$this->quantity} lbr)" : 'Voucher';
    }

    public function getEffectiveItemsAttribute()
    {
        $items = $this->relationLoaded('items') ? $this->items : $this->items()->with('package')->get();
        if ($items->isNotEmpty()) {
            return $items;
        }

        if ($this->package_id) {
            $item = new OrderItem([
                'order_id' => $this->id,
                'package_id' => $this->package_id,
                'quantity' => $this->quantity,
                'printed_price_each' => $this->printed_price_each,
                'owner_price_each' => $this->owner_price_each,
                'total_printed_amount' => $this->total_printed_amount,
                'total_owner_amount' => $this->total_owner_amount,
                'total_reseller_profit' => $this->total_reseller_profit,
            ]);
            $item->setRelation('package', $this->package);
            return collect([$item]);
        }

        return collect([]);
    }

    public function isDirectCustomer(): bool
    {
        return $this->customer_type === 'direct';
    }

    public function getStatusBadgeAttribute(): array
    {
        return match($this->status) {
            'lunas' => [
                'label' => 'Lunas',
                'bg' => 'bg-emerald-50 text-emerald-700 border-emerald-200',
            ],
            'menunggu_pembayaran' => [
                'label' => 'Menunggu Bayar',
                'bg' => 'bg-amber-50 text-amber-700 border-amber-200',
            ],
            'menunggu_approval_hapus' => [
                'label' => 'Pengajuan Hapus',
                'bg' => 'bg-rose-50 text-rose-700 border-rose-200',
            ],
            'dibatalkan' => [
                'label' => 'Dibatalkan',
                'bg' => 'bg-slate-100 text-slate-600 border-slate-300',
            ],
            default => [
                'label' => $this->status,
                'bg' => 'bg-slate-100 text-slate-600 border-slate-200',
            ],
        };
    }
}
