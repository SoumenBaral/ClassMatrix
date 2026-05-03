<?php

namespace App\Models;

use App\Enums\InvoiceStatus;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Invoice extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'student_id', 'academic_year_id', 'term_id',
        'invoice_no', 'total_amount', 'discount_amount', 'paid_amount',
        'due_date', 'status', 'generated_at', 'notes',
    ];

    protected function casts(): array
    {
        return [
            'due_date' => 'date',
            'generated_at' => 'datetime',
            'status' => InvoiceStatus::class,
            'total_amount' => 'decimal:2',
            'discount_amount' => 'decimal:2',
            'paid_amount' => 'decimal:2',
        ];
    }

    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class);
    }

    public function academicYear(): BelongsTo
    {
        return $this->belongsTo(AcademicYear::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(InvoiceItem::class);
    }

    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class);
    }

    public function balanceDue(): float
    {
        return max(0, $this->total_amount - $this->discount_amount - $this->paid_amount);
    }

    public function recalculateStatus(): void
    {
        $balance = $this->balanceDue();

        if ($balance <= 0) {
            $this->status = InvoiceStatus::Paid;
        } elseif ($this->paid_amount > 0) {
            $this->status = InvoiceStatus::Partial;
        } elseif ($this->due_date->isPast()) {
            $this->status = InvoiceStatus::Overdue;
        } else {
            $this->status = InvoiceStatus::Unpaid;
        }

        $this->save();
    }

    public static function generateInvoiceNo(): string
    {
        $last = static::withTrashed()->latest('id')->value('invoice_no');
        $num = $last ? ((int) substr($last, 4)) + 1 : 1;

        return 'INV-' . str_pad($num, 6, '0', STR_PAD_LEFT);
    }
}
