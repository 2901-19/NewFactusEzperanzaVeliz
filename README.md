# FACTUS — Esperanza Veliz

Sistema POS para abasto general venezolano con inventario, precios mayor/unitario, conversión USD/Bs, créditos e impresión térmica.

## Requisitos

- PHP 8.2+
- PostgreSQL 16+
- Composer 2
- Node.js 20+ y npm

## Instalación

```bash
composer install
npm install
npm run build
cp .env.example .env
# Configurar BD en .env (DB_CONNECTION=pgsql, etc.)
php artisan key:generate
php artisan migrate --seed
php artisan serve
```

## Credenciales

| Usuario | Clave | Rol |
|---------|-------|-----|
| admin | admin123 | admin |
| cajero | cajero123 | cajero |

## Funcionalidades

- **POS**: Punto de venta con carrito Alpine.js, precios unitario/mayor, conversión USD→Bs por producto, IVA, descuento de stock (unidades y paquetes)
- **Productos**: CRUD con soft delete, restauración, stock fraccionado (paquetes/unidades)
- **Clientes**: CRUD para facturas a crédito
- **Créditos**: Listado y cobro de créditos pendientes
- **Impuestos / Tasas de Cambio**: CRUD con fuentes (promedio, dolar, bcv)
- **Reportes**: Dashboard, reporte de facturas por rango, balance mensual, stock bajo
- **Herramientas**: Export/import datos JSON, config impresora térmica, lista de precios PDF

## Stack

- Laravel 12, PHP 8.2, PostgreSQL
- Bootstrap 5 + Bootstrap Icons
- Alpine.js
- mike42/escpos-php (impresión térmica)
- dompdf (listas de precios PDF)
