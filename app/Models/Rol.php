<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Rol extends Model
{
    protected $table = 'roles';

    protected $fillable = [
        'nombre',
        'slug',
        'descripcion',
        'protegido',
    ];

    protected $casts = [
        'protegido' => 'boolean',
    ];

    public function usuarios(): HasMany
    {
        return $this->hasMany(User::class, 'rol', 'slug');
    }

    public function permisos(): BelongsToMany
    {
        return $this->belongsToMany(Permiso::class, 'permiso_rol', 'rol', 'permiso_id', 'slug', 'id')->withTimestamps();
    }
}
