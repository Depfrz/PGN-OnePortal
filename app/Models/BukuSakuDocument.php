<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BukuSakuDocument extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'title',
        'description',
        'tags',
        // 'categories', // Removed
        'file_path',
        'file_type',
        'file_size',
        'status',
        'rejected_reason',
        'approved_by',
        'approved_at',
        'valid_until',
    ];

    protected $casts = [
        // 'categories' => 'array', // Removed
        'approved_at' => 'datetime',
        'valid_until' => 'date',
    ];

    public function scopeApproved($query)
    {
        return $query->where('status', 'approved');
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function approver()
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function favoritedBy()
    {
        return $this->belongsToMany(User::class, 'buku_saku_favorites', 'buku_saku_document_id', 'user_id')->withTimestamps();
    }
}
