<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Permiso extends Model
{
    protected $fillable = ['nombre', 'slug', 'descripcion'];

    public function users()
    {
        return $this->belongsToMany(User::class);
    }
}
