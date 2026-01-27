<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PengawasKegiatan extends Model
{
    use HasFactory;

    protected $table = 'pengawas_kegiatan';

    protected $fillable = [
        'pengawas_id',
        'nama_kegiatan',
        'tanggal_mulai',
        'deadline',
        'status',
        'deskripsi',
    ];

    protected $casts = [
        'tanggal_mulai' => 'date',
        'deadline' => 'date',
    ];
}
