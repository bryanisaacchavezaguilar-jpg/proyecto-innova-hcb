<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Prescription extends Model
{
    protected $fillable = ['consultation_id', 'medication', 'dosage', 'frequency', 'duration', 'instructions'];

    public function consultation(): BelongsTo
    {
        return $this->belongsTo(Consultation::class);
    }
}