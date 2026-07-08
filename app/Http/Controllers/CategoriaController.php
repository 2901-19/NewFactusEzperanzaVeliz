<?php

namespace App\Http\Controllers;

use App\Models\Categoria;
use App\Models\Producto;
use Illuminate\Http\Request;

class CategoriaController extends Controller
{
    public function index()
    {
        $categorias = Categoria::withCount('productos')->orderBy('nombre')->get();
        return view('categorias.index', compact('categorias'));
    }

    public function create()
    {
        return view('categorias.form');
    }

    public function store(Request $request)
    {
        $request->validate([
            'nombre' => 'required|string|max:100|unique:categorias,nombre',
            'descripcion' => 'nullable|string|max:255',
        ]);

        Categoria::create($request->all());

        return redirect()->route('categorias.index')->with('success', 'Categoría creada correctamente.');
    }

    public function edit(Categoria $categoria)
    {
        $productos = Producto::whereNull('deleted_at')
            ->where(function ($q) use ($categoria) {
                $q->whereNull('categoria_id')
                  ->orWhere('categoria_id', $categoria->id);
            })
            ->orderBy('nombre')
            ->get();
        return view('categorias.form', compact('categoria', 'productos'));
    }

    public function asignarProductos(Request $request, Categoria $categoria)
    {
        $productoIds = $request->validate(['producto_ids' => 'nullable|array'])['producto_ids'] ?? [];

        Producto::where('categoria_id', $categoria->id)
            ->whereNotIn('id', $productoIds)
            ->update(['categoria_id' => null]);

        Producto::whereIn('id', $productoIds)->update(['categoria_id' => $categoria->id]);

        return redirect()->route('categorias.edit', $categoria)
            ->with('success', 'Productos asignados correctamente.');
    }

    public function update(Request $request, Categoria $categoria)
    {
        $request->validate([
            'nombre' => 'required|string|max:100|unique:categorias,nombre,' . $categoria->id,
            'descripcion' => 'nullable|string|max:255',
        ]);

        $categoria->update($request->all());

        return redirect()->route('categorias.index')->with('success', 'Categoría actualizada correctamente.');
    }

    public function destroy(Categoria $categoria)
    {
        if ($categoria->productos()->count() > 0) {
            return back()->withErrors(['error' => 'No se puede eliminar una categoría con productos asociados.']);
        }

        $categoria->delete();

        return redirect()->route('categorias.index')->with('success', 'Categoría eliminada.');
    }
}
