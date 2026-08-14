<?php

namespace App\Http\Controllers;

use App\Models\Permiso;
use App\Models\Rol;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class RolController extends Controller
{
    public function index()
    {
        $roles = Rol::withCount('permisos', 'usuarios')->orderBy('nombre')->get();

        return view('roles.index', compact('roles'));
    }

    public function create()
    {
        $permisos = Permiso::orderBy('nombre')->get();

        return view('roles.create', compact('permisos'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'nombre' => 'required|string|max:255',
            'descripcion' => 'nullable|string|max:500',
            'permisos' => 'nullable|array',
            'permisos.*' => 'exists:permisos,id',
        ]);

        $rol = Rol::create([
            'nombre' => $data['nombre'],
            'slug' => $this->generarSlug($data['nombre']),
            'descripcion' => $data['descripcion'] ?? null,
            'protegido' => false,
        ]);

        $rol->permisos()->sync($request->input('permisos', []));

        return redirect()->route('roles.index')
            ->with('success', 'Rol "'.$rol->nombre.'" creado correctamente.');
    }

    public function edit(Rol $rol)
    {
        if ($rol->protegido) {
            return redirect()->route('roles.index')
                ->withErrors(['error' => 'El rol "'.$rol->nombre.'" está protegido y no puede editarse.']);
        }

        $permisos = Permiso::orderBy('nombre')->get();
        $rol->load('permisos');

        return view('roles.edit', compact('rol', 'permisos'));
    }

    public function update(Request $request, Rol $rol)
    {
        if ($rol->protegido) {
            return redirect()->route('roles.index')
                ->withErrors(['error' => 'El rol "'.$rol->nombre.'" está protegido y no puede editarse.']);
        }

        $data = $request->validate([
            'nombre' => 'required|string|max:255',
            'descripcion' => 'nullable|string|max:500',
            'permisos' => 'nullable|array',
            'permisos.*' => 'exists:permisos,id',
        ]);

        $rol->update([
            'nombre' => $data['nombre'],
            'descripcion' => $data['descripcion'] ?? null,
        ]);

        $rol->permisos()->sync($request->input('permisos', []));

        return redirect()->route('roles.index')
            ->with('success', 'Rol "'.$rol->nombre.'" actualizado correctamente.');
    }

    public function destroy(Rol $rol)
    {
        if ($rol->protegido) {
            return redirect()->route('roles.index')
                ->withErrors(['error' => 'El rol "'.$rol->nombre.'" está protegido y no puede eliminarse.']);
        }

        if (User::where('rol', $rol->slug)->exists()) {
            return redirect()->route('roles.index')
                ->withErrors(['error' => 'No se puede eliminar el rol "'.$rol->nombre.'" porque tiene usuarios asignados.']);
        }

        DB::table('permiso_rol')->where('rol', $rol->slug)->delete();
        $rol->delete();

        return redirect()->route('roles.index')
            ->with('success', 'Rol eliminado correctamente.');
    }

    private function generarSlug(string $nombre): string
    {
        $base = Str::lower(Str::slug($nombre, '_'));
        $base = preg_replace('/[^a-z0-9_]/', '', $base);

        if ($base === '') {
            $base = 'rol';
        }

        $slug = $base;
        $i = 2;
        while (Rol::where('slug', $slug)->exists()) {
            $slug = $base.'_'.$i;
            $i++;
        }

        return $slug;
    }
}
