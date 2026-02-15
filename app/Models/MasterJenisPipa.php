<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class MasterJenisPipa extends Model
{
    protected $fillable = ['name'];

    public function templateKegiatans(): HasMany
    {
        return $this->hasMany(TemplateKegiatan::class);
    }
}
