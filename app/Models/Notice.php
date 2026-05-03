<?php

namespace App\Models;

use App\Enums\NoticeTarget;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Notice extends Model
{
    protected $fillable = [
        'title', 'body', 'target', 'target_id',
        'attachment', 'published_at', 'expires_at', 'created_by',
    ];

    protected function casts(): array
    {
        return [
            'target' => NoticeTarget::class,
            'published_at' => 'datetime',
            'expires_at' => 'datetime',
        ];
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function scopePublished($query)
    {
        return $query->whereNotNull('published_at')
            ->where('published_at', '<=', now());
    }

    public function scopeActive($query)
    {
        return $query->published()
            ->where(fn ($q) => $q->whereNull('expires_at')->orWhere('expires_at', '>', now()));
    }

    public function isPublished(): bool
    {
        return $this->published_at && $this->published_at->lte(now());
    }
}
