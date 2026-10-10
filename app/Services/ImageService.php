<?php

namespace App\Services;

use Illuminate\Support\Facades\Storage;
use RuntimeException;

/** Optimizuje otpremljene slike i pravi manje WebP verzije (GD). */
class ImageService
{
    public const SIZES = ['sm' => 480, 'md' => 960, 'lg' => 1600];

    public function __construct(private string $disk = 'public') {}

    public function variantPath(string $path, string $size): string
    {
        $info = pathinfo($path);

        return ($info['dirname'] !== '.' ? $info['dirname'].'/' : '').$info['filename'].'-'.$size.'.webp';
    }

    /** Pravi sm/md/lg WebP verzije iz izvorne slike. Vraća putanju lg verzije. */
    public function generateVariants(string $path): void
    {
        $disk = Storage::disk($this->disk);
        $src = @imagecreatefromstring($disk->get($path));
        if ($src === false) {
            throw new RuntimeException("Slika nije čitljiva: {$path}");
        }
        $w = imagesx($src);
        $h = imagesy($src);

        foreach (self::SIZES as $size => $maxWidth) {
            $nw = min($w, $maxWidth);
            $nh = (int) round($h * ($nw / $w));
            $dst = imagecreatetruecolor($nw, $nh);
            imagealphablending($dst, false);
            imagesavealpha($dst, true);
            imagecopyresampled($dst, $src, 0, 0, 0, 0, $nw, $nh, $w, $h);
            ob_start();
            imagewebp($dst, null, 82);
            $disk->put($this->variantPath($path, $size), ob_get_clean());
            imagedestroy($dst);
        }
        imagedestroy($src);
    }

    public function deleteVariants(string $path): void
    {
        $disk = Storage::disk($this->disk);
        foreach (array_keys(self::SIZES) as $size) {
            $disk->delete($this->variantPath($path, $size));
        }
    }
}
