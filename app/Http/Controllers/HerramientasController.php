<?php

namespace App\Http\Controllers;

use App\Models\Producto;
use App\Models\Cliente;
use App\Models\Impuesto;
use App\Models\TasaCambio;
use App\Services\PrinterService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;

class HerramientasController extends Controller
{
    // ========== DATOS (Export/Import) ==========

    public function datos()
    {
        return view('herramientas.datos');
    }

    public function exportar()
    {
        $data = [
            'productos' => Producto::all(),
            'clientes' => Cliente::all(),
            'impuestos' => Impuesto::all(),
            'tasas_cambio' => TasaCambio::all(),
            'exportado_en' => now()->toDateTimeString(),
        ];

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

        if (!$data || !isset($data['productos'])) {
            return back()->withErrors(['archivo' => 'El archivo JSON no tiene el formato correcto.']);
        }

        $validKeys = ['productos', 'clientes', 'impuestos', 'tasas_cambio'];

        DB::beginTransaction();
        try {
            $contadores = ['productos' => 0, 'clientes' => 0, 'impuestos' => 0, 'tasas_cambio' => 0];

            foreach ($validKeys as $key) {
                if (!isset($data[$key]) || !is_array($data[$key])) continue;

                $modelo = match ($key) {
                    'productos' => Producto::class,
                    'clientes' => Cliente::class,
                    'impuestos' => Impuesto::class,
                    'tasas_cambio' => TasaCambio::class,
                };

                $fillable = (new $modelo)->getFillable();

                foreach ($data[$key] as $item) {
                    $item = array_intersect_key($item, array_flip($fillable));
                    unset($item['id']);

                    $modelo::create($item);
                    $contadores[$key]++;
                }
            }

            DB::commit();

            return back()->with('success', 'Importación completada: ' .
                $contadores['productos'] . ' productos, ' .
                $contadores['clientes'] . ' clientes, ' .
                $contadores['impuestos'] . ' impuestos, ' .
                $contadores['tasas_cambio'] . ' tasas.');
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
                'precio_unitario' => $item->precio_unitario_usd,
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

    public function precios()
    {
        $productos = Producto::whereNull('deleted_at')
            ->where('estado', 'disponible')
            ->orderBy('nombre')
            ->get();

        return view('herramientas.precios', compact('productos'));
    }

    public function preciosPdf()
    {
        $productos = Producto::whereNull('deleted_at')
            ->where('estado', 'disponible')
            ->orderBy('nombre')
            ->get();

        $fecha = now()->format('d/m/Y H:i');

        $pdf = Pdf::loadView('herramientas.precios-pdf', compact('productos', 'fecha'));
        $pdf->setPaper('letter', 'portrait');

        return $pdf->download('lista_precios_' . now()->format('Y_m_d') . '.pdf');
    }
}
