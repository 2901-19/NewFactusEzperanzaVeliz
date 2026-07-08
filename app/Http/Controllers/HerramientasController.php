<?php

namespace App\Http\Controllers;

use App\Models\Producto;
use App\Models\Cliente;
use App\Models\Impuesto;
use App\Models\TasaCambio;
use App\Models\Categoria;
use App\Models\Configuracion;
use App\Services\PrinterService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class HerramientasController extends Controller
{
    // ========== DATOS (Export/Import) ==========

    public function datos()
    {
        $tiposDisponibles = [
            'precios' => 'Precios de productos',
            'inventario' => 'Inventario de productos',
            'clientes' => 'Clientes',
            'tasas_cambio' => 'Tasas de cambio',
            'categorias' => 'Categorías',
        ];

        return view('herramientas.datos', compact('tiposDisponibles'));
    }

    public function exportar(Request $request)
    {
        $tipos = $request->input('tipos', []);
        $data = ['exportado_en' => now()->toDateTimeString()];

        if (in_array('precios', $tipos)) {
            $data['precios'] = Producto::all()->map(fn ($p) => [
                'nombre' => $p->nombre,
                'precio_unitario_usd' => $p->precio_unitario_usd,
                'precio_mayor_usd' => $p->precio_mayor_usd,
                'cantidad_minima_mayor' => $p->cantidad_minima_mayor,
                'tiene_iva' => $p->tiene_iva,
                'fuente_tasa' => $p->fuente_tasa,
            ]);
        }

        if (in_array('inventario', $tipos)) {
            $data['inventario'] = Producto::all()->map(fn ($p) => [
                'nombre' => $p->nombre,
                'categoria_id' => $p->categoria_id,
                'descripcion' => $p->descripcion,
                'imagen' => $p->imagen,
                'unidades_por_paquete' => $p->unidades_por_paquete,
                'estado' => $p->estado,
            ]);
        }

        if (in_array('clientes', $tipos)) {
            $data['clientes'] = Cliente::all();
        }

        if (in_array('tasas_cambio', $tipos)) {
            $data['tasas_cambio'] = TasaCambio::all();
        }

        if (in_array('categorias', $tipos)) {
            $data['categorias'] = Categoria::all();
        }

        $json = json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
        $filename = 'backup_' . now()->format('Y_m_d_His') . '.json';

        return response($json, 200, [
            'Content-Type' => 'application/json',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ]);
    }

    public function importar(Request $request)
    {
        if (auth()->user()->rol !== 'admin') {
            return back()->withErrors(['archivo' => 'Solo el administrador puede importar datos.']);
        }

        $request->validate([
            'archivo' => 'required|file|mimes:json,txt|max:10240',
        ]);

        $contenido = file_get_contents($request->file('archivo')->getRealPath());
        $data = json_decode($contenido, true);

        if (!$data) {
            return back()->withErrors(['archivo' => 'El archivo JSON no tiene el formato correcto.']);
        }

        // Detectar formato antiguo (con 'productos' en lugar de 'precios'/'inventario')
        $formatoAntiguo = isset($data['productos']) && !isset($data['precios']) && !isset($data['inventario']);

        $tipos = $request->input('tipos', []);

        // Si no se seleccionó ningún tipo, procesar todo lo disponible
        if (empty($tipos)) {
            if ($formatoAntiguo) {
                $tipos = ['productos', 'clientes', 'impuestos', 'tasas_cambio'];
            } else {
                $tipos = array_keys(array_intersect_key($data, array_flip(['precios', 'inventario', 'clientes', 'tasas_cambio', 'categorias'])));
            }
        }

        DB::beginTransaction();
        try {
            $contadores = ['precios' => 0, 'inventario' => 0, 'clientes' => 0, 'tasas_cambio' => 0, 'categorias' => 0];

            foreach ($tipos as $key) {
                if (!isset($data[$key]) || !is_array($data[$key])) continue;

                switch ($key) {
                    case 'precios':
                        foreach ($data['precios'] as $item) {
                            $producto = Producto::where('nombre', $item['nombre'])->first();
                            if ($producto) {
                                $producto->update([
                                    'precio_unitario_usd' => $item['precio_unitario_usd'] ?? $producto->precio_unitario_usd,
                                    'precio_mayor_usd' => $item['precio_mayor_usd'] ?? $producto->precio_mayor_usd,
                                    'cantidad_minima_mayor' => $item['cantidad_minima_mayor'] ?? $producto->cantidad_minima_mayor,
                                    'tiene_iva' => $item['tiene_iva'] ?? $producto->tiene_iva,
                                    'fuente_tasa' => $item['fuente_tasa'] ?? $producto->fuente_tasa,
                                ]);
                            } else {
                                Producto::create([
                                    'nombre' => $item['nombre'],
                                    'precio_unitario_usd' => $item['precio_unitario_usd'] ?? 0,
                                    'precio_mayor_usd' => $item['precio_mayor_usd'] ?? 0,
                                    'cantidad_minima_mayor' => $item['cantidad_minima_mayor'] ?? 1,
                                    'tiene_iva' => $item['tiene_iva'] ?? true,
                                    'fuente_tasa' => $item['fuente_tasa'] ?? 'paralelo',
                                    'stock_paquetes' => 0,
                                    'stock_unidades' => 0,
                                    'unidades_por_paquete' => 1,
                                    'estado' => 'disponible',
                                ]);
                            }
                            $contadores['precios']++;
                        }
                        break;

                    case 'inventario':
                        foreach ($data['inventario'] as $item) {
                            $existe = Producto::where('nombre', $item['nombre'])->exists();
                            if (!$existe) {
                                Producto::create([
                                    'nombre' => $item['nombre'],
                                    'categoria_id' => $item['categoria_id'] ?? null,
                                    'descripcion' => $item['descripcion'] ?? '',
                                    'imagen' => $item['imagen'] ?? null,
                                    'unidades_por_paquete' => $item['unidades_por_paquete'] ?? 1,
                                    'estado' => $item['estado'] ?? 'disponible',
                                    'stock_paquetes' => 0,
                                    'stock_unidades' => 0,
                                    'precio_unitario_usd' => 0,
                                    'precio_mayor_usd' => 0,
                                    'cantidad_minima_mayor' => 1,
                                    'tiene_iva' => true,
                                    'fuente_tasa' => 'paralelo',
                                ]);
                                $contadores['inventario']++;
                            }
                        }
                        break;

                    case 'clientes':
                        foreach ($data['clientes'] as $item) {
                            $fillable = (new Cliente)->getFillable();
                            $item = array_intersect_key($item, array_flip($fillable));
                            unset($item['id']);

                            $existe = Cliente::where('ci', $item['ci'] ?? '')->exists();
                            if (!$existe) {
                                Cliente::create($item);
                                $contadores['clientes']++;
                            }
                        }
                        break;

                    case 'tasas_cambio':
                        foreach ($data['tasas_cambio'] as $item) {
                            $fillable = (new TasaCambio)->getFillable();
                            $item = array_intersect_key($item, array_flip($fillable));
                            unset($item['id']);
                            TasaCambio::create($item);
                            $contadores['tasas_cambio']++;
                        }
                        break;

                    case 'categorias':
                        foreach ($data['categorias'] as $item) {
                            $existe = Categoria::where('nombre', $item['nombre'])->exists();
                            if (!$existe) {
                                Categoria::create([
                                    'nombre' => $item['nombre'],
                                    'descripcion' => $item['descripcion'] ?? '',
                                ]);
                                $contadores['categorias']++;
                            }
                        }
                        break;

                    // Compatibilidad con formato antiguo
                    case 'productos':
                        foreach ($data['productos'] as $item) {
                            $fillable = (new Producto)->getFillable();
                            $item = array_intersect_key($item, array_flip($fillable));
                            unset($item['id']);
                            Producto::create($item);
                            $contadores['precios']++;
                        }
                        break;

                    case 'impuestos':
                        foreach ($data['impuestos'] as $item) {
                            $fillable = (new Impuesto)->getFillable();
                            $item = array_intersect_key($item, array_flip($fillable));
                            unset($item['id']);
                            Impuesto::create($item);
                        }
                        break;
                }
            }

            DB::commit();

            $partes = [];
            if ($contadores['precios']) $partes[] = $contadores['precios'] . ' precios';
            if ($contadores['inventario']) $partes[] = $contadores['inventario'] . ' inventarios';
            if ($contadores['clientes']) $partes[] = $contadores['clientes'] . ' clientes';
            if ($contadores['tasas_cambio']) $partes[] = $contadores['tasas_cambio'] . ' tasas';
            if ($contadores['categorias']) $partes[] = $contadores['categorias'] . ' categorías';

            $mensaje = 'Importación completada: ' . ($partes ? implode(', ', $partes) : 'no se procesaron datos');

            return back()->with('success', $mensaje);
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withErrors(['archivo' => 'Error durante la importación: ' . $e->getMessage()]);
        }
    }

    // ========== IMPRESIÓN ==========

    public function imprimirConfig()
    {
        $config = [
            'tipo' => config('impresora.tipo', 'network'),
            'host' => config('impresora.host', '192.168.1.100'),
            'port' => config('impresora.port', 9100),
            'nombre' => config('impresora.nombre', ''),
        ];

        return view('herramientas.impresora', compact('config'));
    }

    public function imprimirGuardar(Request $request)
    {
        $request->validate([
            'tipo' => 'required|in:network,windows',
            'host' => 'required_if:tipo,network|ip|nullable',
            'port' => 'required_if:tipo,network|integer|nullable',
            'nombre' => 'required_if:tipo,windows|string|nullable',
        ]);

        $config = [
            'tipo' => $request->tipo,
            'host' => $request->host ?? '',
            'port' => $request->port ?? 9100,
            'nombre' => $request->nombre ?? '',
        ];

        file_put_contents(
            storage_path('app/impresora.json'),
            json_encode($config, JSON_PRETTY_PRINT)
        );

        return back()->with('success', 'Configuración de impresora guardada.');
    }

    public function imprimirTest(Request $request)
    {
        $config = $this->getPrinterConfig();
        $service = new PrinterService();

        $ok = $service->connect($config['tipo'], $config['host'], $config['port'], $config['nombre']);

        if (!$ok) {
            return back()->withErrors(['error' => 'No se pudo conectar a la impresora. Verifique la configuración.']);
        }

        $ok = $service->printTest();

        if (!$ok) {
            return back()->withErrors(['error' => 'Error al imprimir la prueba.']);
        }

        return back()->with('success', 'Prueba de impresión enviada correctamente.');
    }

    private function getPrinterConfig()
    {
        $path = storage_path('app/impresora.json');
        if (file_exists($path)) {
            return json_decode(file_get_contents($path), true);
        }
        return [
            'tipo' => 'network',
            'host' => '192.168.1.100',
            'port' => 9100,
            'nombre' => '',
        ];
    }

    public function imprimirFactura($factura)
    {
        $factura = \App\Models\Factura::with('cliente', 'items.producto')->findOrFail($factura);
        $items = $factura->items->map(function ($item) {
            return [
                'nombre' => $item->producto->nombre ?? 'Producto',
                'precio_unitario' => $item->precio_unitario_bs,
                'cantidad' => $item->cantidad,
                'total' => $item->subtotal,
            ];
        })->toArray();

        $config = $this->getPrinterConfig();
        $service = new PrinterService();
        $ok = $service->connect($config['tipo'], $config['host'], $config['port'], $config['nombre']);

        if (!$ok) {
            return back()->withErrors(['error' => 'No se pudo conectar a la impresora.']);
        }

        $ok = $service->printReceipt($factura, $items, auth()->user()->usuario);

        if (!$ok) {
            return back()->withErrors(['error' => 'Error al imprimir el ticket.']);
        }

        return back()->with('success', 'Ticket impreso correctamente.');
    }

    // ========== PDF LISTA DE PRECIOS ==========

    public function precios(Request $request)
    {
        $productos = Producto::whereNull('deleted_at')
            ->where('estado', 'disponible')
            ->orderBy('nombre')
            ->get();

        $tasas = \App\Models\TasaCambio::pluck('monto', 'tipo');

        if ($request->query('export') === 'json') {
            $data = $productos->map(fn ($p) => [
                'nombre' => $p->nombre,
                'precio_unitario_usd' => $p->precio_unitario_usd,
                'precio_mayor_usd' => $p->precio_mayor_usd,
                'cantidad_minima_mayor' => $p->cantidad_minima_mayor,
                'tiene_iva' => $p->tiene_iva,
                'fuente_tasa' => $p->fuente_tasa,
                'precio_unitario_bs' => round($p->precio_unitario_usd * ($tasas[$p->fuente_tasa] ?? 1), 2),
                'precio_mayor_bs' => round($p->precio_mayor_usd * ($tasas[$p->fuente_tasa] ?? 1), 2),
            ]);

            return response()->json($data);
        }

        return view('herramientas.precios', compact('productos', 'tasas'));
    }

    public function preciosPdf()
    {
        $productos = Producto::whereNull('deleted_at')
            ->where('estado', 'disponible')
            ->orderBy('nombre')
            ->get();

        $tasas = \App\Models\TasaCambio::pluck('monto', 'tipo');
        $fecha = now()->format('d/m/Y H:i');

        $pdf = Pdf::loadView('herramientas.precios-pdf', compact('productos', 'tasas', 'fecha'));
        $pdf->setPaper('letter', 'portrait');

        return $pdf->download('lista_precios_' . now()->format('Y_m_d') . '.pdf');
    }

    // ========== CONFIGURACIÓN DEL NEGOCIO ==========

    public function configuracion()
    {
        $configs = Configuracion::pluck('valor', 'clave')->toArray();
        return view('herramientas.configuracion', compact('configs'));
    }

    public function configuracionGuardar(Request $request)
    {
        $request->validate([
            'nombre_negocio' => 'required|string|max:255',
            'rif' => 'required|string|max:20',
            'direccion' => 'required|string|max:255',
            'telefono' => 'required|string|max:20',
        ]);

        foreach (['nombre_negocio', 'rif', 'direccion', 'telefono'] as $clave) {
            Configuracion::updateOrCreate(
                ['clave' => $clave],
                ['valor' => $request->$clave]
            );
        }

        return back()->with('success', 'Configuración guardada correctamente.');
    }
}
