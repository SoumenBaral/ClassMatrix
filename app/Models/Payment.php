<?php

namespace App\Models;

use App\Enums\PaymentMethod;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Payment extends Model
{
    protected $fillable = [
        'invoice_id', 'amount', 'method', 'transaction_id',
        'gateway', 'paid_at', 'received_by', 'receipt_no', 'notes',
    ];

    protected function casts(): array
    {
        return [
            'paid_at' => 'datetime',
            'method' => PaymentMethod::class,
            'amount' => 'decimal:2',
        ];
    }

    public function invoice(): BelongsTo
    {
        return $this->belongsTo(Invoice::class);
    }

    public function receiver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'received_by');
    }

    public static function generateReceiptNo(): string
    {
        $last = static::latest('id')->value('receipt_no');
        $num = $last ? ((int) substr($last, 4)) + 1 : 1;

        return 'RCP-' . str_pad($num, 6, '0', STR_PAD_LEFT);
    }
}
