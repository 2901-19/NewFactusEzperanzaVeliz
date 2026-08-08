<?php

namespace App\Services;

use Mike42\Escpos\CapabilityProfile;
use Mike42\Escpos\PrintConnectors\NetworkPrintConnector;
use Mike42\Escpos\PrintConnectors\WindowsPrintConnector;
use Mike42\Escpos\Printer;

class PrinterService
{
    protected $printer;

    public function connect($tipo = 'network', $host = '127.0.0.1', $port = 9100, $nombre = null)
    {
        try {
            if ($tipo === 'network') {
                $connector = new NetworkPrintConnector($host, $port);
            } elseif ($tipo === 'windows' && $nombre) {
                $connector = new WindowsPrintConnector($nombre);
            } else {
                throw new \Exception('Tipo de conexión no soportado.');
            }

            $profile = CapabilityProfile::load('simple');
            $this->printer = new Printer($connector, $profile);

            return true;
        } catch (\Exception $e) {
            return false;
        }
    }

    public function printReceipt($factura, $productos, $usuario)
    {
        if (! $this->printer) {
            return false;
        }

        try {
            $this->printer->setJustification(Printer::JUSTIFY_CENTER);
            $this->printer->setBold(true);
            $this->printer->setTextSize(2, 2);
            $this->printer->text("FACTUS\n");
            $this->printer->setBold(false);
            $this->printer->setTextSize(1, 1);
            $this->printer->text("Esperanza Veliz\n");
            $this->printer->feed();

            $this->printer->setJustification(Printer::JUSTIFY_LEFT);
            $this->printer->text("Correlativo: {$factura->correlativo}\n");
            $this->printer->text("Fecha: {$factura->fecha_venta}\n");
            $this->printer->text("Cajero: {$usuario}\n");
            $this->printer->feed();

            if ($factura->cliente) {
                $this->printer->text("Cliente: {$factura->cliente->nombre}\n");
                if ($factura->cliente->ci) {
                    $this->printer->text("Cédula: {$factura->cliente->ci}\n");
                }
                $this->printer->feed();
            }

            $detalItems = array_values(array_filter($productos, fn ($i) => ($i['tipo_venta'] ?? 'unitario') !== 'mayor'));
            $mayorItems = array_values(array_filter($productos, fn ($i) => ($i['tipo_venta'] ?? 'unitario') === 'mayor'));

            if (count($detalItems) > 0 && count($mayorItems) > 0) {
                $this->printItemsSection('DETAL', $detalItems);
                $this->printItemsSection('MAYOR', $mayorItems);
            } else {
                $this->printItemsSection(null, $productos);
            }

            $this->printer->setBold(true);
            $this->printer->setTextSize(2, 2);
            $this->printer->text(str_pad('TOTAL Bs:', 30).str_pad(number_format($factura->total_bs, 2), 16)."\n");
            $this->printer->setTextSize(1, 1);
            $this->printer->text(str_pad('TOTAL USD:', 30).str_pad('$'.number_format($factura->total_usd, 2), 16)."\n");
            $this->printer->setBold(false);
            $this->printer->feed();

            $nombresMetodo = [
                'efectivo' => 'Efectivo',
                'punto' => 'Punto de Venta',
                'biopago' => 'Biopago',
                'divisas' => 'Divisas',
                'pago_movil' => 'Pago Móvil',
                'transferencia' => 'Transferencia',
                'mixto' => 'Mixto',
            ];

            if ($factura->metodo_pago === 'mixto') {
                $this->printer->text(str_pad('Pago Mixto', 46)."\n");
                foreach ($factura->detalle_pago ?? [] as $pago) {
                    $nombre = $nombresMetodo[$pago['metodo']] ?? $pago['metodo'];
                    $this->printer->text(str_pad("  {$nombre}:", 30).str_pad('Bs '.number_format($pago['monto'], 2), 16)."\n");
                }
            } else {
                $nombre = $nombresMetodo[$factura->metodo_pago] ?? $factura->metodo_pago;
                $this->printer->text(str_pad("Pago: {$nombre}", 46)."\n");
            }
            $this->printer->feed();

            if ($factura->estado === 'credito') {
                $this->printer->text("** CRÉDITO PENDIENTE **\n");
                $this->printer->feed();
            }

            $this->printer->setJustification(Printer::JUSTIFY_CENTER);
            $this->printer->text("Gracias por su compra!\n");
            $this->printer->feed(3);
            $this->printer->cut();
            $this->printer->close();

            return true;
        } catch (\Exception $e) {
            return false;
        }
    }

    protected function printItemsSection(?string $titulo, array $items): void
    {
        if ($titulo) {
            $this->printer->setJustification(Printer::JUSTIFY_CENTER);
            $this->printer->setBold(true);
            $this->printer->text("{$titulo}\n");
            $this->printer->setBold(false);
            $this->printer->setJustification(Printer::JUSTIFY_LEFT);
        }

        $this->printer->setBold(true);
        $this->printer->text(str_pad('CANT', 5).str_pad('DESC', 20).str_pad('PREC U', 10, STR_PAD_LEFT).str_pad('PREC T', 10, STR_PAD_LEFT)."\n");
        $this->printer->setBold(false);
        $this->printer->text(str_repeat('-', 45)."\n");

        $subtotal = 0;
        foreach ($items as $item) {
            $precio = number_format($item['precio_unitario'], 2);
            $cant = $item['cantidad'];
            $total = number_format($item['total'], 2);
            $subtotal += $item['total'];
            $nombreRestante = $item['nombre'];
            $lineaPrimera = true;
            while ($nombreRestante !== '') {
                $trozo = mb_substr($nombreRestante, 0, $lineaPrimera ? 18 : 16);
                $nombreRestante = mb_substr($nombreRestante, mb_strlen($trozo));
                if ($lineaPrimera) {
                    $this->printer->text(str_pad("{$cant}", 5).str_pad($trozo, 20).str_pad("{$precio}", 10, STR_PAD_LEFT).str_pad("{$total}", 10, STR_PAD_LEFT)."\n");
                    $lineaPrimera = false;
                } else {
                    $this->printer->text(str_pad('', 5).str_pad($trozo, 16)."\n");
                }
            }
        }

        $this->printer->text(str_repeat('-', 45)."\n");

        if ($titulo) {
            $this->printer->text(str_pad("Subtotal {$titulo}:", 30).str_pad('Bs '.number_format($subtotal, 2), 16)."\n");
        }
    }

    public function printTest()
    {
        if (! $this->printer) {
            return false;
        }
        try {
            $this->printer->setJustification(Printer::JUSTIFY_CENTER);
            $this->printer->setBold(true);
            $this->printer->setTextSize(2, 2);
            $this->printer->text("PRUEBA\n");
            $this->printer->setBold(false);
            $this->printer->setTextSize(1, 1);
            $this->printer->text("Impresión exitosa!\n");
            $this->printer->feed(3);
            $this->printer->cut();
            $this->printer->close();

            return true;
        } catch (\Exception $e) {
            return false;
        }
    }
}
