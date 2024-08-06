<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Rekening extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'bank',
        'no_rek'
    ];

    protected static function boot()
    {
        parent::boot();

        static::saving(function ($model) {
            if ($model->is_active) {
                static::where('is_active', true)
                    ->where('id', '!=', $model->id)
                    ->update(['is_active' => false]);
            }
        });
    }
}
