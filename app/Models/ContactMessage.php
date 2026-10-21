<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ContactMessage extends Model
{
    // Follow-up status of a lead => label shown in the admin panel.
    public const STATUSES = [
        'new' => 'New',
        'contacted' => 'Contacted',
        'closed' => 'Closed',
    ];

    // Choices for the "Subject" dropdown on the contact form.
    public const SUBJECTS = [
        'Order or delivery',
        'Product question',
        'Returns and refunds',
        'Bulk or business enquiry',
        'Privacy or account deletion',
        'Other',
    ];

    protected $fillable = ['name', 'email', 'phone', 'subject', 'message', 'status', 'user_id', 'ip_address', 'read_at'];

    protected $attributes = ['status' => 'new'];

    protected function casts(): array
    {
        return ['read_at' => 'datetime'];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function statusLabel(): string
    {
        return self::STATUSES[$this->status] ?? ucfirst((string) $this->status);
    }

    // Phone digits for tel: / WhatsApp links. A bare 10-digit number is treated as Indian (+91).
    public function phoneDigits(): ?string
    {
        $digits = preg_replace('/\D+/', '', (string) $this->phone);
        if ($digits === '') {
            return null;
        }

        return strlen($digits) === 10 ? '91'.$digits : ltrim($digits, '0');
    }
}
