# Guia de Impresora Termica — Xprinter XP-E200M / E260M / E300M

Paso a paso para configurar la impresora termica de recibos por conexion **USB directa** con el sistema FACTUS.

## Impresora compatible

| Modelo | Interfaz | Velocidad | Ancho papel |
|--------|----------|-----------|-------------|
| XP-E200M | USB (algunas variantes: USB+Serial) | 200 mm/s | 80 mm |
| XP-E260M | USB + Serial + LAN | 260 mm/s | 80 mm |
| XP-E300M | USB + Serial + LAN | 300 mm/s | 80 mm (switchable a 58 mm) |

- Emulacion: **ESC/POS** (mismo protocolo que usa el sistema)
- Cortador automatico: si (parcial)
- Soporte de codigos de barras y QR: si

## Requisitos

- Windows 10/11 en la PC caja (servidor local)
- PHP 8.2+ corriendo con `php artisan serve`
- La impresora conectada por **USB** al mismo equipo donde corre PHP

---

## Paso 1 — Instalar el driver de Windows

1. Conectar la impresora por USB y encenderla.
2. Descargar el driver oficial desde:
   [https://es.xprintertech.com/xp-e200m-xp-e300m](https://es.xprintertech.com/xp-e200m-xp-e300m)
   Seccion **"Descargar"** → **"Conductores"** → **"Controlador de impresora de recibos para Windows"**.
3. Ejecutar el instalador como **Administrador** (clic derecho → Ejecutar como administrador).
4. Seguir el wizard hasta que detecte la USB y finalice.
5. Verificar que aparezca en `Configuracion → Bluetooth y dispositivos → Impresoras y escaneres`.

---

## Paso 2 — Probar el driver en Windows (sin tocar PHP)

Esto confirma que el driver quedo bien antes de integrar con el sistema.

1. En `Impresoras y escaneres`, clic en la Xprinter.
2. Boton **"Imprimir pagina de prueba"** (mas abajo en la pagina).
3. Debe imprimir un ticket con texto de prueba.
4. Si imprime → driver funciona, pasar al paso 3.
5. Si no imprime → revisar cable USB, rollo de papel (lado termico contra el cabezal) o reinstalar driver antes de seguir.

---

## Paso 3 — Compartir la impresora en Windows

PHP necesita ver la impresora como recurso compartido local para enviar comandos ESC/POS.

1. En `Impresoras y escaneres`, clic en la Xprinter → **"Propiedades de la impresora"**.
2. Pestana **"Compartir"**.
3. Marcar **"Compartir esta impresora"**.
4. Asignar un nombre corto **sin espacios ni caracteres raros**. Ejemplo: `XP-E300M`.
5. Anotar el nombre exacto que se escribio (se usara en el paso 5).

> **Importante:** el nombre debe ser identico al que se escribe aqui. PHP lo buscare exactamente asi.

---

## Paso 4 — Permisos del recurso compartido

Si PHP no puede acceder a la impresora, es un problema de permisos.

1. Volver a **"Propiedades de la impresora"** de la Xprinter.
2. Pestana **"Seguridad"**.
3. Verificar que el grupo `Everyone` tenga permiso **"Imprimir"**.
4. Si no aparece `Everyone`, clic **"Agregar"** → escribir `Everyone` → "Comprobar nombres" → Aceptar → marcar **"Imprimir"**.
5. Aceptar.

---

## Paso 5 — Configurar el sistema FACTUS

1. Abrir el navegador en `http://localhost:8000` (o el puerto de `php artisan serve`).
2. Iniciar sesion como **administrador**.
3. En el sidebar, ir a **Herramientas** → **Configuracion Impresora**.
4. En el formulario:
   - **Tipo de Conexion:** `Windows (USB/COM)`
   - **Nombre de la Impresora:** el nombre del paso 3 (ej: `XP-E300M`)
5. Clic **"Guardar"**.
6. Debe aparecer el mensaje verde "Configuracion guardada correctamente".

---

## Paso 6 — Probar impresion desde el sistema

1. En la misma pantalla de Configuracion Impresora, tarjeta **"Prueba de Impresion"**.
2. Clic **"Imprimir Prueba"**.
3. Debe imprimir:
   ```
                  FACTUS
              Esperanza Veliz
              Impresion exitosa!
   ```
4. Si imprime → listo.
5. Si no imprime → ver seccion Solucion de problemas.

---

## Paso 7 — Probar con una factura real

1. Sidebar → **POS** (Punto de Venta).
2. Seleccionar productos, definir cantidades.
3. Agregar un cliente o elegir uno existente.
4. Pulsa **"Cobrar / Facturar"**.
5. En la factura registrada, buscar el boton **"Imprimir Ticket"**.
6. Debe salir el recibo completo: nombre del negocio, correlativo, fecha, cajero, productos, totales en Bs/USD, leyenda de credito si aplica, y corte parcial al final.

---

## Solucion de problemas

| Sintoma | Causa probable | Solucion |
|---------|----------------|----------|
| Error "No se pudo conectar" al imprimir | Nombre compartido mal escrito | Verificar el nombre en PowerShell (`Get-Printer`) y copiarlo exacto |
| Imprime letra ilegible / basura | Driver generico equivocado | Reinstalar driver oficial del modelo exacto |
| Imprime en blanco | Cable USB flojo o papel al revés | Reasentar cable, voltear el rollo (cara termica contra el cabezal) |
| Imprime muy lento / letra por letra | Driver en modo spool | Propiedades de impresora → Avanzado → desmarcar "Spool" / elegir "Imprimir directamente" |
| Test dice "Exito" pero factura no imprime | Se imprime sobre la seleccion actual | Imprimir despues de guardar la factura (paso 7) |
| "Acceso denegado" en el log | Falta permiso de impresion | Repetir paso 4 |
| Sale un cuadrado negro despues del texto | Falta papel o rollo al revés | Colocar nuevo rollo, verificar orientacion |
| El cortador no corta | Modelo sin cortador o deshabilitado | Verificar hardware; activar en utilidad de Xprinter |

---

## Verificacion desde PowerShell

Para confirmar que Windows ve la impresora:

```powershell
Get-Printer
```

Debe aparecer la Xprinter en la lista. Tomar nota del nombre original.

Para verificar el recurso compartido:

```powershell
Get-Printer -Name "XP-E300M" -ErrorAction SilentlyContinue
```

Si aparece → todo esta bien configurado en Windows.

---

## Notas tecnicas

- El sistema usa la libreria `mike42/escpos-php` v5.0 con emulacion ESC/POS nativa.
- La conexion por USB funciona a traves del driver de Windows compartido (`WindowsPrintConnector`).
- La impresora debe estar en el **mismo equipo** donde corre PHP (`php artisan serve`).
- Si en el futuro se necesita impresion remota por red, se puede usar la opcion `Red (TCP/IP)` del formulario configurando IP + puerto 9100.
