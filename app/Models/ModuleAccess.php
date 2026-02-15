<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ModuleAccess extends Model
{
    use HasFactory;

    protected $table = 'module_access';

    protected $fillable = [
        'user_id',
        'module_id',
        'can_read',
        'can_write',
        'can_delete',
        'show_on_dashboard',
        'extra_permissions',
    ];

    protected $casts = [
        'can_read' => 'boolean',
        'can_write' => 'boolean',
        'can_delete' => 'boolean',
        'show_on_dashboard' => 'boolean',
        'extra_permissions' => 'array',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function module()
    {
        return $this->belongsTo(Module::class);
    }
}
