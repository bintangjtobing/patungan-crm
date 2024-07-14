<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Product;

class Transaction extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'jenis_transaksi',
        'product_uuid',
        'description',
        'jumlah',
        'harga',
        'tanggal_waktu_transaksi_selesai',
        'status',
        'bukti_transaksi',
    ];


    public function product()
    {
        return $this->belongsTo(Product::class, 'product_uuid', 'uuid');
    }
}
