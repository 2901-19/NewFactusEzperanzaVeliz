# Lanzador de escritorio FACTUS (Electron) + Instalador Windows

Lanza el sistema como una **app de escritorio** para la PC del cliente: el usuario hace doble clic
en el acceso directo **FACTUS**, se abre una ventana sin pestañas de navegador y al cerrarla se
apaga por completo el servidor PHP (no queda nada corriendo en segundo plano).

## Cómo funciona

- El lanzador es una app **Electron** (`launcher/`) que, al abrirse:
  1. Levanta el servidor web con el **PHP portable** incluido (`php -S 127.0.0.1:8000 -t public public/index.php`).
  2. Espera a que responda y abre una ventana apuntando a `http://127.0.0.1:8000`.
  3. Al cerrar la ventana mata el árbol de procesos PHP (`taskkill /pid <pid> /T /F`) y sale.
- `electron-builder` produce un instalador NSIS por-usuario (sin permisos de admin) que instala
  en `%LocalAppData%\Programs\FACTUS`:
  - `FACTUS.exe` (Electron)
  - `resources\php\` → PHP portable (copiado de `C:\php`)
  - `resources\app\` → proyecto Laravel completo (vendor prod, `public\build`, `.env` cliente)
- **PostgreSQL no se incluye** en el instalador: debe estar instalado como servicio local en la PC
  (las credenciales de BD se toman del `.env` al momento de construir el instalador).

## Estructura

| Archivo | Rol |
|---|---|
| `launcher/main.js` | Proceso principal Electron (arranque, ventana, apagado) |
| `launcher/config.json` | `port`, `fullscreen`, rutas `phpPath`/`appPath` opcionales |
| `launcher/package.json` | Config electron-builder (NSIS, icono, extraResources) |
| `launcher/tools/preparar-php.ps1` | Copia `C:\php` → `launcher/php-bundle` |
| `launcher/tools/icon.ps1` | Genera `launcher/build/icon.ico` |
| `launcher/tools/empaquetar-app.ps1` | Prepara `launcher/app-bundle` (vendor prod + `.env` cliente) |

## Requisitos para construir (máquina de desarrollo)

- Windows x64, Node.js 20+ y npm
- PHP instalado en `C:\php` (PHP 8.2+ con `ext\pdo_pgsql`, `ext\pgsql`, `ext\gd`, `ext\mbstring`)
- Internet (descarga de Electron/NSIS la primera vez)
- Las credenciales de la BD cliente (`DB_*` del `.env`) deben estar configuradas en el `.env`
  del repo **antes** de empaquetar; el instalador copia esas credenciales.

## Cómo construir

```powershell
# 1) Instalar dependencias del lanzador (solo la primera vez)
cd launcher
npm install

# Si el binario de Electron no se descargó (postinstall bloqueado por npm):
node node_modules/electron/install.js

# 2) Generar el bundle PHP portable y el icono
powershell -ExecutionPolicy Bypass -File tools\preparar-php.ps1
powershell -ExecutionPolicy Bypass -File tools\icon.ps1

# 3) Probar en modo desarrollo (opcional): ventana + servidor + cierre limpio
npm start
# Modo smoke (automatizado, se cierra solo a los ~4s de cargar el servidor):
# $env:FACTUS_SMOKE='1'; $env:FACTUS_SMOKE_WINDOW='1'; npm start

# 4) Empaquetar el app Laravel de producción (vendor sin paquetes dev + .env cliente)
powershell -ExecutionPolicy Bypass -File tools\empaquetar-app.ps1

# 5) Generar el instalador
npm run dist:win
```

El instalador queda en **`launcher\dist\FACTUS-Setup-1.0.0.exe`** (~130 MB).

## Verificación rápida (smoke)

La variable de entorno `FACTUS_SMOKE=1` hace que el lanzador arranque el servidor y se cierre solo,
para probar el ciclo de vida sin interactuar:

```powershell
$env:FACTUS_SMOKE='1'; & "$env:LOCALAPPDATA\Programs\FACTUS\FACTUS.exe"
# Al terminar debe quedar libre el puerto 8000 y sin procesos php:
Get-NetTCPConnection -LocalPort 8000 -State Listen -ErrorAction SilentlyContinue
Get-Process php -ErrorAction SilentlyContinue
```

## Instalación en la PC del cliente

1. **PostgreSQL**: instalar PostgreSQL 16 como servicio de Windows y crear la base de datos que
   indique el `.env` empaquetado (por defecto `factus_esperanza_veliz` con usuario `postgres`).
   Restaurar un backup si es reemplazo de la PC actual.
2. Ejecutar `FACTUS-Setup-1.0.0.exe` → crear el acceso directo "FACTUS" en el escritorio.
3. Windows puede mostrar SmartScreen (instalador sin firma): **Más información → Ejecutar de todas formas**.
4. Doble clic en **FACTUS**: se abre la ventana con el login (`admin` / `admin123` o el usuario que corresponda).
5. Configurar la impresora térmica según `docs/IMPRESORA.md`.

## Comportamiento y configuración

- **Cerrar ventana = apagar todo**: al cerrar se mata PHP y el proceso sale; no quedan procesos.
- **Instancia única**: abrir FACTUS dos veces solo enfoca la ventana existente.
- **Puerto en uso**: si `8000` está ocupado por otra aplicación, muestra un aviso y sale.
- **PostgreSQL apagado**: si el servidor responde error 500, muestra un aviso con "Reintentar / Salir".
- **F11**: alterna pantalla completa. `config.json` permite arrancar a pantalla completa (`"fullscreen": true`)
  y cambiar el puerto (`"port"`).
- La app guarda su estado en `%APPDATA%\FACTUS` (incluye el `php.ini` generado para el PHP portable).

## Notas

- El instalador **no está firmado** (requiere certificado de código); el cliente verá SmartScreen.
- El `.env` del bundle se genera con `APP_ENV=production`, `APP_DEBUG=false`, `LOG_LEVEL=error` y
  las credenciales `DB_*` del `.env` del repo al momento de construir.
- `launcher/app-bundle`, `launcher/php-bundle`, `launcher/node_modules` y `launcher/dist` están en
  `.gitignore` (artefactos de build, no se versionan).
