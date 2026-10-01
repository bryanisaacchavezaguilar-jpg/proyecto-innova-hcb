<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Specialty extends Model
{
    protected $fillable = ['name', 'is_active'];

    public function professionals(): HasMany
    {
        return $this->hasMany(Professional::class);
    }
}