<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Module extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'slug',
        'url',
        'icon',
        'status',
        'tab_type',
    ];

    public function moduleAccesses()
    {
        return $this->hasMany(ModuleAccess::class);
    }
}
