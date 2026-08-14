Add-Type -AssemblyName System.Drawing

$tam = 256
$bmp = New-Object System.Drawing.Bitmap $tam, $tam
$g = [System.Drawing.Graphics]::FromImage($bmp)
$g.SmoothingMode = [System.Drawing.Drawing2D.SmoothingMode]::AntiAlias
$g.TextRenderingHint = [System.Drawing.Text.TextRenderingHint]::AntiAlias
$g.Clear([System.Drawing.Color]::Transparent)

$colorFondo = [System.Drawing.Color]::FromArgb(13, 110, 253)
$brush = [System.Drawing.SolidBrush]::new($colorFondo)

$margen = 10
$r = [System.Drawing.Rectangle]::new($margen, $margen, $tam - (2 * $margen), $tam - (2 * $margen))
$g.FillRectangle($brush, $r)

$fuente = [System.Drawing.Font]::new('Segoe UI', 150, [System.Drawing.FontStyle]::Bold, [System.Drawing.GraphicsUnit]::Pixel)
$sf = [System.Drawing.StringFormat]::new()
$sf.Alignment = [System.Drawing.StringAlignment]::Center
$sf.LineAlignment = [System.Drawing.StringAlignment]::Center
$area = [System.Drawing.RectangleF]::new(0, 0, $tam, $tam)
$g.DrawString('F', $fuente, [System.Drawing.Brushes]::White, $area, $sf)

$destino = Join-Path $PSScriptRoot '..\build\icon.ico'
New-Item -ItemType Directory -Path (Split-Path $destino) -Force | Out-Null

$icon = [System.Drawing.Icon]::FromHandle($bmp.GetHicon())
$fs = [System.IO.File]::Open($destino, [System.IO.FileMode]::Create)
$icon.Save($fs)
$fs.Close()

$g.Dispose()
$bmp.Dispose()
$icon.Dispose()

Write-Host "Icono generado en $destino"
