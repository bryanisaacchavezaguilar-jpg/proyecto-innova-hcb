<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Patient extends Model
{
    use HasFactory;

    protected $fillable = [
        'first_name', 'last_name', 'document_type', 'document_number', 'date_of_birth',
        'sex', 'phone', 'email', 'address', 'emergency_contact', 'allergies',
        'medical_history', 'is_active', 'created_by',
    ];

    protected function casts(): array
    {
        return ['date_of_birth' => 'date', 'is_active' => 'boolean'];
    }

    public function getFullNameAttribute(): string
    {
        return trim($this->first_name.' '.$this->last_name);
    }

    public function appointments(): HasMany
    {
        return $this->hasMany(Appointment::class);
    }

    public function consultations(): HasMany
    {
        return $this->hasMany(Consultation::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function portalAccount(): HasOne
    {
        return $this->hasOne(User::class);
    }
}