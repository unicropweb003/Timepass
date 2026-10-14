<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Order extends Model
{
    /**
     * Delivery steps in order, with the timestamp column that records when each was reached.
     */
    public const STEPS = [
        'placed' => ['Order placed', 'created_at'],
        'packed' => ['Packed', 'packed_at'],
        'shipped' => ['Shipped', 'shipped_at'],
        'out_for_delivery' => ['Out for delivery', 'out_for_delivery_at'],
        'delivered' => ['Delivered', 'delivered_at'],
    ];

    protected $fillable = [
        'order_number', 'status', 'subtotal', 'shipping', 'discount', 'total', 'payment_method',
        'shipping_name', 'shipping_phone', 'shipping_address', 'courier', 'tracking_number',
        'expected_delivery', 'packed_at', 'shipped_at', 'out_for_delivery_at', 'delivered_at', 'cancelled_at',
    ];

    protected function casts(): array
    {
        return [
            'subtotal' => 'decimal:2',
            'shipping' => 'decimal:2',
            'discount' => 'decimal:2',
            'total' => 'decimal:2',
            'expected_delivery' => 'date',
            'packed_at' => 'datetime',
            'shipped_at' => 'datetime',
            'out_for_delivery_at' => 'datetime',
            'delivered_at' => 'datetime',
            'cancelled_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    public function transactions(): HasMany
    {
        return $this->hasMany(Transaction::class);
    }

    public function getRouteKeyName(): string
    {
        return 'order_number';
    }

    public function statusLabel(): string
    {
        return $this->status === 'cancelled' ? 'Cancelled' : (self::STEPS[$this->status][0] ?? ucfirst($this->status));
    }

    /**
     * Tailwind classes for the status badge.
     */
    public function statusClasses(): string
    {
        return match ($this->status) {
            'delivered' => 'bg-emerald-50 text-emerald-700 ring-emerald-200',
            'cancelled' => 'bg-red-50 text-red-700 ring-red-200',
            'shipped', 'out_for_delivery' => 'bg-blue-50 text-blue-700 ring-blue-200',
            default => 'bg-amber-50 text-amber-700 ring-amber-200',
        };
    }
}
