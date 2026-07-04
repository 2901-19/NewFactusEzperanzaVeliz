<?php

namespace App\Http\Controllers;

use App\Models\Permiso;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class UserController extends Controller
{
    public function index()
    {
        $users = User::orderBy('name')->get();
        return view('usuarios.index', compact('users'));
    }

    public function create()
    {
        $permisos = Permiso::orderBy('nombre')->get();
        $permisosDelRol = \DB::table('permiso_rol')
            ->where('rol', 'cajero')
            ->pluck('permiso_id')
            ->toArray();
        return view('usuarios.create', compact('permisos', 'permisosDelRol'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'usuario' => 'required|string|max:255|unique:users',
            'email' => 'nullable|email|max:255|unique:users',
            'password' => 'required|string|min:6|confirmed',
            'rol' => 'required|in:admin,cajero',
            'permisos' => 'nullable|array',
            'permisos.*' => 'exists:permisos,id',
        ]);

        $data['password'] = bcrypt($data['password']);

        $usuario = User::create($data);

        if ($request->filled('permisos')) {
            $usuario->permisosDirectos()->sync($request->permisos);
        }

        return redirect()->route('usuarios.index')->with('success', 'Usuario creado correctamente.');
    }

    public function edit(User $usuario)
    {
        $permisos = Permiso::orderBy('nombre')->get();
        $usuario->load('permisosDirectos');
        $permisosDelRol = \DB::table('permiso_rol')
            ->where('rol', $usuario->rol)
            ->pluck('permiso_id')
            ->toArray();
        return view('usuarios.edit', compact('usuario', 'permisos', 'permisosDelRol'));
    }

    public function update(Request $request, User $usuario)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'usuario' => ['required', 'string', 'max:255', Rule::unique('users')->ignore($usuario->id)],
            'email' => ['nullable', 'email', 'max:255', Rule::unique('users')->ignore($usuario->id)],
            'password' => 'nullable|string|min:6|confirmed',
            'rol' => 'required|in:admin,cajero',
            'permisos' => 'nullable|array',
            'permisos.*' => 'exists:permisos,id',
        ]);

        if ($data['password']) {
            $data['password'] = bcrypt($data['password']);
        } else {
            unset($data['password']);
        }

        $usuario->update($data);

        if ($request->has('permisos')) {
            $usuario->permisosDirectos()->sync($request->permisos);
        } else {
            $usuario->permisosDirectos()->detach();
        }

        return redirect()->route('usuarios.index')->with('success', 'Usuario actualizado correctamente.');
    }

    public function destroy(User $usuario)
    {
        if ($usuario->id === auth()->id()) {
            return back()->withErrors(['error' => 'No puedes eliminar tu propio usuario.']);
        }

        $usuario->delete();

        return redirect()->route('usuarios.index')->with('success', 'Usuario eliminado correctamente.');
    }
}
