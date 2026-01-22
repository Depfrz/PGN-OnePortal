<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BukuSakuFavorite extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'buku_saku_document_id',
    ];
}
