<?php

namespace App\Support;

use Illuminate\Support\Facades\Storage;

/**
 * Generiše privremene (placeholder) slike vozila: jednostavna silueta, bez tuđih fotografija.
 * Zamenjuju se pravim slikama iz admin panela.
 */
class PlaceholderImage
{
    /** @param  string  $body  hatchback|sedan|suv|van */
    public static function make(string $path, string $bodyColor, string $body = 'sedan', int $variant = 0): string
    {
        $w = 1600;
        $h = 1000;
        $im = imagecreatetruecolor($w, $h);

        [$r, $g, $b] = sscanf(ltrim($bodyColor, '#'), '%02x%02x%02x');
        $bgTop = [[222, 230, 234], [214, 226, 232], [226, 228, 224]][$variant % 3];
        for ($y = 0; $y < $h; $y++) {
            $t = $y / $h;
            $c = imagecolorallocate($im, (int) ($bgTop[0] - 18 * $t), (int) ($bgTop[1] - 16 * $t), (int) ($bgTop[2] - 14 * $t));
            imageline($im, 0, $y, $w, $y, $c);
        }
        $ground = imagecolorallocate($im, 196, 206, 211);
        imagefilledrectangle($im, 0, 760, $w, $h, $ground);

        $shadow = imagecolorallocatealpha($im, 19, 34, 43, 100);
        imagefilledellipse($im, 800, 770, 1000, 50, $shadow);

        $bodyC = imagecolorallocate($im, $r, $g, $b);
        $dark = imagecolorallocate($im, (int) ($r * .7), (int) ($g * .7), (int) ($b * .7));
        $glass = imagecolorallocate($im, 188, 214, 226);
        $tyre = imagecolorallocate($im, 30, 38, 44);
        $rim = imagecolorallocate($im, 205, 212, 216);

        $shift = [0, -40, 30][$variant % 3];
        $profiles = [
            'hatchback' => ['low' => [[300, 700], [300, 560], [420, 520], [560, 380], [980, 380], [1190, 520], [1300, 560], [1300, 700]], 'win' => [[590, 410], [770, 410], [770, 520], [470, 520]]],
            'sedan' => ['low' => [[280, 700], [280, 570], [450, 530], [610, 390], [960, 390], [1120, 530], [1320, 570], [1320, 700]], 'win' => [[640, 420], [800, 420], [800, 525], [510, 525]]],
            'suv' => ['low' => [[290, 700], [290, 540], [400, 500], [520, 340], [1100, 340], [1230, 500], [1310, 540], [1310, 700]], 'win' => [[560, 370], [790, 370], [790, 500], [455, 500]]],
            'van' => ['low' => [[290, 700], [290, 520], [380, 330], [1130, 330], [1260, 470], [1310, 520], [1310, 700]], 'win' => [[430, 360], [790, 360], [790, 480], [395, 480]]],
        ];
        $p = $profiles[$body] ?? $profiles['sedan'];
        $flat = fn (array $pts) => array_merge(...array_map(fn ($q) => [$q[0] + $shift, $q[1]], $pts));

        imagefilledpolygon($im, $flat($p['low']), $bodyC);
        imagefilledpolygon($im, $flat($p['win']), $glass);
        $second = array_map(fn ($q) => [$q[0] + ($body === 'van' ? 360 : 330), $q[1] + 0], $p['win']);
        $second = array_map(fn ($q) => [min($q[0], 1090), $q[1]], $second);
        imagefilledrectangle($im, 820 + $shift, $p['win'][0][1], 1020 + $shift, $p['win'][2][1], $glass);
        imagefilledrectangle($im, 300 + $shift, 640, 1300 + $shift, 700, $dark);

        foreach ([470, 1130] as $cx) {
            imagefilledellipse($im, $cx + $shift, 700, 190, 190, $tyre);
            imagefilledellipse($im, $cx + $shift, 700, 100, 100, $rim);
            imagefilledellipse($im, $cx + $shift, 700, 30, 30, $tyre);
        }

        $label = imagecolorallocate($im, 88, 106, 115);
        $text = 'PRIVREMENA SLIKA / PLACEHOLDER';
        imagestring($im, 5, (int) (($w - strlen($text) * 9) / 2), 900, $text, $label);

        ob_start();
        imagejpeg($im, null, 88);
        $data = ob_get_clean();
        imagedestroy($im);

        Storage::disk('public')->put($path, $data);

        return $path;
    }
}
