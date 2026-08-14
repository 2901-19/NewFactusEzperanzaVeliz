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

## Despliegue en la PC del cliente (lanzador)

Para instalar el sistema en la PC caja sin terminales ni navegador, se empaqueta un instalador
Windows con Electron + PHP portable (`FACTUS-Setup.exe`): doble clic abre la app y al cerrar la
ventana se apaga todo el servidor. Ver `docs/LANZADOR.md`.

## Guía de instalación desde cero (Windows 10/11)

Esta guía recorre todo el proceso: instalar y configurar cada tecnología, dejar el sistema FACTUS
funcionando y generar el instalador del lanzador listo para la PC del cliente.

### 0. Tecnologías y versiones

| Tecnología | Versión mínima | Probada en este proyecto | Descarga |
|---|---|---|---|
| Windows 10/11 (x64) | — | 11 | — |
| PHP | 8.2 | 8.3.32 (ZTS x64) | https://windows.php.net/download/ |
| PostgreSQL | 16 | 18.4 | https://www.postgresql.org/download/windows/ |
| Composer | 2 | 2.10.2 | https://getcomposer.org/download/ |
| Node.js | 20 | 24.18.0 | https://nodejs.org/ (LTS) |
| Git | 2 | 2.55.0 | https://git-scm.com/ |

> El orden importa: PHP primero (Composer lo necesita), luego PostgreSQL, Composer, Node.js y Git.

### 1. Instalar y configurar cada tecnología

#### 1.1 PHP (en `C:\php`)

1. Descargar el **zip "Thread Safe x64"** de PHP 8.3.x desde https://windows.php.net/download/.
2. Descomprimir el contenido en **`C:\php`** (debe quedar ahí: `preparar-php.ps1` del lanzador
   copia esa carpeta tal cual al instalador).
3. Copiar `C:\php\php.ini-development` → `C:\php\php.ini`.
4. Editar `C:\php\php.ini` y activar (quitar el `;`):
   - `extension_dir = "ext"`
   - `extension=curl`, `extension=fileinfo`, `extension=gd`, `extension=intl`,
     `extension=mbstring`, `extension=openssl`, `extension=pdo_pgsql`, `extension=pdo_sqlite`,
     `extension=pgsql`, `extension=sqlite3`, `extension=zip`
   - `date.timezone = America/Caracas`
5. Agregar `C:\php` al **PATH del sistema** (o `C:\php\ext` no es necesario, solo el root):
   - Panel de control → Sistema → Configuración avanzada → Variables de entorno → `Path`
     del sistema → Nuevo → `C:\php`; o en PowerShell como administrador:
     `[Environment]::SetEnvironmentVariable('Path', $env:Path + ';C:\php', 'Machine')`
6. Verificar en una terminal nueva: `php -v` (debe mostrar 8.3.x) y `php -m` (debe listar `pgsql`
   y `pdo_pgsql`).

#### 1.2 PostgreSQL

1. Ejecutar el instalador EDB de PostgreSQL 16+ (https://www.postgresql.org/download/windows/).
2. En el asistente: componentes **PostgreSQL Server** (pgAdmin y Stack Builder son opcionales),
   puerto **5432**, y definir la contraseña del superusuario `postgres` (anotarla; va al `.env`).
3. Al terminar, verificar que el servicio quedó corriendo:
   `Get-Service postgresql*` (debe aparecer `Running`).
4. Crear la base de datos del sistema:
   ```
   & "C:\Program Files\PostgreSQL\18\bin\psql.exe" -U postgres -h 127.0.0.1 -c "CREATE DATABASE factus_esperanza_veliz;"
   ```
   (te pedirá la contraseña de `postgres`; ajusta `18` a la versión instalada).
   También puede hacerse desde pgAdmin (clic derecho en *Databases* → *Create* → *Database*).

#### 1.3 Composer

1. Descargar y ejecutar el instalador `Composer-Setup.exe` de https://getcomposer.org/download/.
2. En el asistente, indicar el PHP instalado (`C:\php\php.exe`); el instalador agrega Composer al PATH.
3. Verificar en una terminal nueva: `composer --version`.

#### 1.4 Node.js

1. Descargar e instalar la versión **LTS** desde https://nodejs.org/ (incluye npm).
2. Verificar: `node -v` y `npm -v`.

#### 1.5 Git

1. Descargar e instalar desde https://git-scm.com/ (opciones por defecto).
2. Verificar: `git --version`.

### 2. Instalar el sistema FACTUS

En una terminal PowerShell (por ejemplo en `C:\Users\<usuario>\Documents\Projects`):

```powershell
# 1) Descargar el código
git clone https://github.com/2901-19/NewFactusEzperanzaVeliz.git
cd NewFactusEzperanzaVeliz

# 2) Dependencias de PHP (usa composer.lock)
composer install

# 3) Dependencias y assets de frontend
npm install
npm run build

# 4) Configuración del entorno
Copy-Item .env.example .env
```

5. Editar `.env` con los datos de la BD creada en el paso 1.2:

```
DB_CONNECTION=pgsql
DB_HOST=127.0.0.1
DB_PORT=5432
DB_DATABASE=factus_esperanza_veliz
DB_USERNAME=postgres
DB_PASSWORD=<la contraseña que pusiste en PostgreSQL>
```

6. Generar la clave de la aplicación, migrar y sembrar los datos iniciales:

```powershell
php artisan key:generate
php artisan migrate --seed
```

7. Probar en el navegador:

```powershell
php artisan serve
```

Abrir `http://localhost:8000` y entrar con `admin` / `admin123` (también existe `cajero` / `cajero123`).

### 3. Dejar listo el lanzador (FACTUS-Setup.exe)

Con el sistema funcionando, se genera el instalador de escritorio:

```powershell
# 1) Dependencias del lanzador (solo la primera vez)
cd launcher
npm install
# Si el binario de Electron no se descargó (postinstall bloqueado por npm):
node node_modules/electron/install.js

# 2) Bundle de PHP portable (copia C:\php → php-bundle) e icono
powershell -ExecutionPolicy Bypass -File tools\preparar-php.ps1
powershell -ExecutionPolicy Bypass -File tools\icon.ps1

# 3) Empaquetar la app Laravel de producción (vendor prod + .env cliente)
powershell -ExecutionPolicy Bypass -File tools\empaquetar-app.ps1

# 4) Generar el instalador
npm run dist:win
```

> **Antes del paso 3**: el `.env` del repo se copia (adaptado) al instalador, incluidas las
> credenciales `DB_*`. Si la BD de la PC del cliente usa otra contraseña, ajusta el `.env` del
> repo (o el `app-bundle\.env` generado) **antes** de empaquetar.

El instalador queda en `launcher\dist\FACTUS-Setup-1.0.0.exe` (~130 MB). Verificar el ciclo
completo con el smoke test (ver `docs/LANZADOR.md`).

### 4. Instalar en la PC del cliente (resumen)

1. Instalar PostgreSQL 16+ como servicio local y crear/restaurar la base de datos
   `factus_esperanza_veliz` con las credenciales que quedaron empaquetadas:
   - **Con datos previos**: restaurar un backup (`pg_dump`/`pg_restore` o pgAdmin).
   - **Sistema nuevo**: abrir PowerShell en `%LocalAppData%\Programs\FACTUS\resources\app` y ejecutar
     `resources\php\php.exe artisan migrate --seed` (usa el PHP del bundle).
2. Ejecutar `FACTUS-Setup-1.0.0.exe` → crea el acceso directo "FACTUS" en el escritorio.
3. Si Windows muestra SmartScreen (instalador sin firmar): **Más información → Ejecutar de todas formas**.
4. Doble clic en **FACTUS**: se abre la ventana con el login. Al cerrarla se apaga el servidor por completo.
5. Configurar la impresora térmica según `docs/IMPRESORA.md`.
