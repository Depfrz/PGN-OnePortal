<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable, HasRoles;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'profile_photo_path',
        'instansi',
        'jabatan',
        'last_session_id',
        'password',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    public function moduleAccesses()
    {
        return $this->hasMany(ModuleAccess::class);
    }

    public function favoriteDocuments()
    {
        return $this->belongsToMany(BukuSakuDocument::class, 'buku_saku_favorites', 'user_id', 'buku_saku_document_id')->withTimestamps();
    }

    public function hasModuleAccess($moduleName)
    {
        return $this->moduleAccesses->contains(function ($access) use ($moduleName) {
            return $access->module && $access->module->name === $moduleName;
        });
    }
}
