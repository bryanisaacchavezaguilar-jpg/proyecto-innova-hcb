<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Consultation extends Model
{
    protected $fillable = [
        'appointment_id', 'patient_id', 'professional_id', 'created_by', 'consultation_date',
        'chief_complaint', 'diagnosis', 'treatment_plan', 'clinical_notes', 'blood_pressure',
        'temperature', 'weight_kg', 'height_cm', 'follow_up_date',
    ];

    protected function casts(): array
    {
        return ['consultation_date' => 'datetime', 'follow_up_date' => 'date'];
    }

    public function appointment(): BelongsTo
    {
        return $this->belongsTo(Appointment::class);
    }

    public function patient(): BelongsTo
    {
        return $this->belongsTo(Patient::class);
    }

    public function professional(): BelongsTo
    {
        return $this->belongsTo(Professional::class);
    }

    public function prescriptions(): HasMany
    {
        return $this->hasMany(Prescription::class);
    }
}