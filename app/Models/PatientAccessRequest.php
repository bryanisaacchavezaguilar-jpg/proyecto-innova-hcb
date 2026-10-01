<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PatientAccessRequest extends Model
{
    protected $fillable = [
        'user_id', 'document_type', 'document_number', 'date_of_birth', 'phone',
        'status', 'reviewed_by', 'reviewed_at',
    ];

    protected function casts(): array
    {
        return ['date_of_birth' => 'date', 'reviewed_at' => 'datetime'];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function reviewer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }
}