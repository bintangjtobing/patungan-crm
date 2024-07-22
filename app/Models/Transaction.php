<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Product;
use Illuminate\Support\Carbon;

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

    protected static function boot()
    {
        parent::boot();

        // Event creating untuk mengatur nilai default tanggal_waktu_transaksi_selesai
        static::creating(function ($transaction) {
            if (is_null($transaction->tanggal_waktu_transaksi_selesai)) {
                $transaction->tanggal_waktu_transaksi_selesai = Carbon::now()->setTimezone('Asia/Jakarta');
            }
        });
    }

    public function getModifiedTransactionDateAttribute()
    {
        return Carbon::parse($this->tanggal_waktu_transaksi_selesai)->addMonth()->format('Y-m-d');
    }

    public function product()
    {
        return $this->belongsTo(Product::class, 'product_uuid', 'uuid');
    }
}