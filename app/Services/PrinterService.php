<?php

namespace App\Services;

use Mike42\Escpos\Printer;
use Mike42\Escpos\EscposImage;
use Mike42\Escpos\PrintConnectors\NetworkPrintConnector;
use Mike42\Escpos\PrintConnectors\FilePrintConnector;
use Mike42\Escpos\PrintConnectors\WindowsPrintConnector;
use Mike42\Escpos\CapabilityProfile;

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
                throw new \Exception("Tipo de conexión no soportado.");
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
        if (!$this->printer) {
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

            $this->printer->setBold(true);
            $this->printer->text(str_pad("PRODUCTO", 20) . str_pad("P/U", 10) . str_pad("CANT", 6) . str_pad("TOTAL", 10) . "\n");
            $this->printer->setBold(false);
            $this->printer->text(str_repeat("-", 46) . "\n");

            foreach ($productos as $item) {
                $nombre = mb_substr($item['nombre'], 0, 18);
                $precio = number_format($item['precio_unitario'], 2);
                $cant = $item['cantidad'];
                $total = number_format($item['total'], 2);
                $this->printer->text(str_pad($nombre, 20) . str_pad("{$precio}", 10) . str_pad("{$cant}", 6) . str_pad("{$total}", 10) . "\n");
            }

            $this->printer->text(str_repeat("-", 46) . "\n");

            $this->printer->setBold(true);
            $this->printer->setTextSize(2, 2);
            $this->printer->text(str_pad("TOTAL Bs:", 30) . str_pad(number_format($factura->total_bs, 2), 16) . "\n");
            $this->printer->setTextSize(1, 1);
            $this->printer->text(str_pad("TOTAL USD:", 30) . str_pad('$' . number_format($factura->total_usd, 2), 16) . "\n");
            $this->printer->setBold(false);
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

    public function printTest()
    {
        if (!$this->printer) {
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
