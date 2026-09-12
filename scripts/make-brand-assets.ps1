<#
  Generator aset brand "ikon kode" (tanpa tulisan).
  Menghasilkan:
    public/apple-touch-icon.png  (180x180)
    public/favicon.ico           (16, 32, 48 - payload PNG)
  Jalankan: powershell -NoProfile -ExecutionPolicy Bypass -File scripts/make-brand-assets.ps1
  Sumber desain: public/logo-code.svg (kotak gradien #60a5fa -> #2563eb, chevron + underscore putih)
#>
$ErrorActionPreference = 'Stop'
Add-Type -AssemblyName System.Drawing

$root   = Split-Path -Parent (Split-Path -Parent $MyInvocation.MyCommand.Path)
$public = Join-Path $root 'public'

function New-BrandBitmap([int]$size) {
    $bmp = New-Object System.Drawing.Bitmap($size, $size, [System.Drawing.Imaging.PixelFormat]::Format32bppArgb)
    $g   = [System.Drawing.Graphics]::FromImage($bmp)
    $g.SmoothingMode     = [System.Drawing.Drawing2D.SmoothingMode]::AntiAlias
    $g.InterpolationMode = [System.Drawing.Drawing2D.InterpolationMode]::HighQualityBicubic
    $g.Clear([System.Drawing.Color]::Transparent)

    $s = [double]$size

    # --- kotak membulat dengan gradien biru (setara <rect rx="16"> di viewBox 64) ---
    $r    = $s * (16.0 / 64.0)
    $d    = $r * 2
    $path = New-Object System.Drawing.Drawing2D.GraphicsPath
    $path.AddArc(0, 0, $d, $d, 180, 90)
    $path.AddArc($s - $d, 0, $d, $d, 270, 90)
    $path.AddArc($s - $d, $s - $d, $d, $d, 0, 90)
    $path.AddArc(0, $s - $d, $d, $d, 90, 90)
    $path.CloseFigure()

    $c1    = [System.Drawing.ColorTranslator]::FromHtml('#60a5fa')
    $c2    = [System.Drawing.ColorTranslator]::FromHtml('#2563eb')
    $brush = New-Object System.Drawing.Drawing2D.LinearGradientBrush(
        (New-Object System.Drawing.PointF(0, 0)),
        (New-Object System.Drawing.PointF($s, $s)),
        $c1, $c2)
    $g.FillPath($brush, $path)

    # --- glyph: chevron ">" + garis bawah "_" (viewBox 64 -> skala) ---
    $k   = $s / 64.0
    $pen = New-Object System.Drawing.Pen([System.Drawing.Color]::White, [float](5.5 * $k))
    $pen.StartCap = [System.Drawing.Drawing2D.LineCap]::Round
    $pen.EndCap   = [System.Drawing.Drawing2D.LineCap]::Round
    $pen.LineJoin = [System.Drawing.Drawing2D.LineJoin]::Round

    $pts = [System.Drawing.PointF[]]@(
        (New-Object System.Drawing.PointF([float](18 * $k), [float](21 * $k))),
        (New-Object System.Drawing.PointF([float](30 * $k), [float](32 * $k))),
        (New-Object System.Drawing.PointF([float](18 * $k), [float](43 * $k)))
    )
    $g.DrawLines($pen, $pts)
    $g.DrawLine($pen,
        (New-Object System.Drawing.PointF([float](35 * $k), [float](43 * $k))),
        (New-Object System.Drawing.PointF([float](47 * $k), [float](43 * $k))))

    $pen.Dispose(); $brush.Dispose(); $path.Dispose(); $g.Dispose()
    return $bmp
}

function Get-PngBytes([System.Drawing.Bitmap]$bmp) {
    $ms = New-Object System.IO.MemoryStream
    $bmp.Save($ms, [System.Drawing.Imaging.ImageFormat]::Png)
    $bytes = $ms.ToArray()
    $ms.Dispose()
    return ,$bytes
}

# --- apple-touch-icon.png ---
$touch = New-BrandBitmap 180
$touch.Save((Join-Path $public 'apple-touch-icon.png'), [System.Drawing.Imaging.ImageFormat]::Png)
$touch.Dispose()
Write-Host 'OK  apple-touch-icon.png (180x180)'

# --- favicon.ico (multi-size, payload PNG) ---
$sizes = @(16, 32, 48)
$images = @()
foreach ($sz in $sizes) {
    $b = New-BrandBitmap $sz
    $images += ,@{ Size = $sz; Data = (Get-PngBytes $b) }
    $b.Dispose()
}

$icoPath = Join-Path $public 'favicon.ico'
$fs = [System.IO.File]::Create($icoPath)
$bw = New-Object System.IO.BinaryWriter($fs)

$bw.Write([uint16]0)              # reserved
$bw.Write([uint16]1)              # type = icon
$bw.Write([uint16]$images.Count)  # count

$offset = 6 + (16 * $images.Count)
foreach ($img in $images) {
    $bw.Write([byte]$img.Size)   # width
    $bw.Write([byte]$img.Size)   # height
    $bw.Write([byte]0)           # color count
    $bw.Write([byte]0)           # reserved
    $bw.Write([uint16]1)         # planes
    $bw.Write([uint16]32)        # bpp
    $bw.Write([uint32]$img.Data.Length)
    $bw.Write([uint32]$offset)
    $offset += $img.Data.Length
}
foreach ($img in $images) { $bw.Write($img.Data) }

$bw.Flush(); $fs.Close()
Write-Host 'OK  favicon.ico (16/32/48)'

Get-ChildItem (Join-Path $public 'apple-touch-icon.png'), (Join-Path $public 'favicon.ico') |
    Select-Object Name, Length | Format-Table -AutoSize
