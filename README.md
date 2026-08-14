# FACTUS — Esperanza Veliz

Sistema POS para abasto general venezolano con inventario, precios mayor/unitario, conversión USD/Bs, créditos e impresión térmica.

## Requisitos

- PHP 8.2+ (probado en 8.3)
- PostgreSQL 16+ (probado en 18)
- Composer 2
- Node.js 20+ y npm (probado en 24)
- Git

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

> `migrate --seed` solo siembra el usuario `admin`; los roles y usuarios adicionales (p. ej. `cajero`)
> se crean desde la app en **Roles** y **Usuarios** (los roles son dinámicos).

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

## Despliegue en la PC del cliente (lanzador)

Para usar FACTUS en la PC caja sin terminales: doble clic en `launcher/FACTUS.vbs` levanta el
servidor (`php artisan serve`), abre la app en una ventana de navegador dedicada y al cerrarla apaga
todo y cierra la sesión. Ver `docs/LANZADOR.md`.

## Guía de instalación desde cero (Windows 10/11)

Guía completa "de un equipo limpio a listo para usar", con versiones probadas, configuración de
`.env`, despliegue en la PC del cliente (sin Node/Composer/Git) y respaldos: **`docs/INSTALACION.md`**.
