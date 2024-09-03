<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CategoryFilter extends Model
{
    use HasFactory;

    protected $fillable = ['provider_id', 'action', 'inclusion_pattern', 'exclusion_pattern'];

    public function provider()
    {
        return $this->belongsTo(Provider::class);
    }
}
