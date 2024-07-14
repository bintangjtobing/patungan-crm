<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class KredentialCustomer extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'product_uuid',
        'email_akses',
        'profil_akses',
        'pin',
    ];

    protected $hidden = [
        'email_akses',
        'profil_akses',
        'pin',
    ];
}
