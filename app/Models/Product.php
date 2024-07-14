<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Ramsey\Uuid\Uuid;
use App\Models\Transaction;

class Product extends Model
{
    use HasFactory;

    protected $primaryKey = 'uuid';
    protected $keyType = 'string'; // Tentukan tipe kunci sebagai string jika menggunakan UUID
    public $incrementing = false; // Pastikan bahwa tidak ada peningkatan secara otomatis untuk UUID

    protected $fillable = [
        'uuid',
        'nama',
        'harga_jual',
        'harga_beli',
        'type',
        'description',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($product) {
            $product->uuid = (string) Uuid::uuid4();
        });
    }

    public function scopeOrdered($query)
    {
        return $query->orderBy('uuid', 'asc');
    }

    public function transactions()
    {
        return $this->hasMany(Transaction::class, 'product_uuid', 'uuid');
    }
}
