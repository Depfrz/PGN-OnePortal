<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TemplateKegiatan extends Model
{
    protected $fillable = ['master_jenis_pipa_id', 'nama_kegiatan'];

    public function masterJenisPipa(): BelongsTo
    {
        return $this->belongsTo(MasterJenisPipa::class);
    }
}
