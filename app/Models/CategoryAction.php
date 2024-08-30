<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CategoryAction extends Model
{
    use HasFactory;

    protected $fillable = ['provider_id', 'action', 'category_id', 'category_name'];

    public function provider()
    {
        return $this->belongsTo(Provider::class);
    }
}
