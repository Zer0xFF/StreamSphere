<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CategoryAction extends Model
{
    use HasFactory;

    protected $fillable = ['provider_id', 'action', 'category_id', 'category_name', 'is_hidden'];

    public function provider()
    {
        return $this->belongsTo(Provider::class);
    }

    protected static function booted()
    {
        static::addGlobalScope('hidden', function ($builder) {
            $builder->where('is_hidden', true);
        });
    }
}
