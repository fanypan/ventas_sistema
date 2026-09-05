<?php

namespace App\Services\Media;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Str;
use RuntimeException;

class ImageOptimizer
{
    public function encode(UploadedFile $file, string $directory = 'products'): EncodedImage
    {
        $source = $this->load($file);
        $source = $this->fit($source, (int) config('media.max_image_edge', 1000), fill: [255, 255, 255]);

        ob_start();
        imagejpeg($source, null, max(10, min(95, (int) config('media.jpeg_quality', 80))));
        $binary = (string) ob_get_clean();
        imagedestroy($source);

        return new EncodedImage(
            $this->path($directory, 'jpg'),
            $binary,
            'image/jpeg',
        );
    }

    public function encodePng(UploadedFile $file, string $directory = 'branding'): EncodedImage
    {
        $source = $this->load($file);
        if (! imageistruecolor($source)) {
            imagepalettetotruecolor($source);
        }
        imagesavealpha($source, true);
        $source = $this->fit($source, (int) config('media.logo_max_edge', 800), fill: null);

        ob_start();
        imagepng($source, null, 6);
        $binary = (string) ob_get_clean();
        imagedestroy($source);

        return new EncodedImage(
            $this->path($directory, 'png'),
            $binary,
            'image/png',
        );
    }

    private function load(UploadedFile $file): \GdImage
    {
        $contents = file_get_contents($file->getRealPath());
        if ($contents === false) {
            throw new RuntimeException('No se pudo leer la imagen subida.');
        }

        $source = @imagecreatefromstring($contents);
        if ($source === false) {
            throw new RuntimeException('La imagen no se pudo procesar.');
        }

        return $source;
    }

    /**
     * @param  array{0: int, 1: int, 2: int}|null  $fill
     */
    private function fit(\GdImage $source, int $maxEdge, ?array $fill): \GdImage
    {
        $width = imagesx($source);
        $height = imagesy($source);
        $maxEdge = max(1, $maxEdge);

        if ($width <= $maxEdge && $height <= $maxEdge) {
            return $source;
        }

        $scale = $maxEdge / max($width, $height);
        $targetWidth = max(1, (int) round($width * $scale));
        $targetHeight = max(1, (int) round($height * $scale));
        $canvas = imagecreatetruecolor($targetWidth, $targetHeight);

        if ($fill === null) {
            imagealphablending($canvas, false);
            imagesavealpha($canvas, true);
            $background = imagecolorallocatealpha($canvas, 0, 0, 0, 127);
            imagefilledrectangle($canvas, 0, 0, $targetWidth, $targetHeight, $background);
            imagealphablending($canvas, true);
        } else {
            imagealphablending($canvas, true);
            imagesavealpha($canvas, false);
            $background = imagecolorallocate($canvas, $fill[0], $fill[1], $fill[2]);
            imagefilledrectangle($canvas, 0, 0, $targetWidth, $targetHeight, $background);
        }

        imagecopyresampled($canvas, $source, 0, 0, 0, 0, $targetWidth, $targetHeight, $width, $height);
        imagedestroy($source);

        return $canvas;
    }

    private function path(string $directory, string $extension): string
    {
        return trim($directory, '/').'/'.Str::uuid()->toString().'.'.$extension;
    }
}
