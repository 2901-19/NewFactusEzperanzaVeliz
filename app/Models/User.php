<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    public function username(): string
    {
        return 'usuario';
    }

    protected $fillable = [
        'name',
        'usuario',
        'email',
        'password',
        'rol',
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

    public function facturas()
    {
        return $this->hasMany(Factura::class);
    }

    public function permisosDirectos()
    {
        return $this->belongsToMany(Permiso::class);
    }

    public function permisos(): \Illuminate\Support\Collection
    {
        $permisosRol = Permiso::whereIn('id', function ($q) {
            $q->select('permiso_id')->from('permiso_rol')->where('rol', $this->rol);
        })->pluck('slug');

        $permisosUser = $this->permisosDirectos->pluck('slug');

        return $permisosRol->merge($permisosUser)->unique();
    }

    public function hasPermiso(string $slug): bool
    {
        return $this->permisos()->contains($slug);
    }

    public function esAdmin(): bool
    {
        return $this->rol === 'admin';
    }
}
