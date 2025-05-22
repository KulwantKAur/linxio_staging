<?php

namespace App\Twig;

use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;
use Twig\TwigFunction;

class AppExtension extends AbstractExtension
{
    public function getFilters()
    {
        return [
            new TwigFilter('cssClass', [$this, 'formatToCssClass']),
        ];
    }

    public function getFunctions()
    {
        return [
            new TwigFunction('getImageRotateDeg', [$this, 'getImageRotateDeg']),
            new TwigFunction('isImage', [$this, 'isImage']),
        ];
    }

    public function formatToCssClass(string $key)
    {
        return str_replace('.', '-', $key);
    }

    public function getImageRotateDeg($path)
    {
        try {
            if (!str_contains(get_headers($path)[0], "200 OK")) {
                return 0;
            }

            $extension = pathinfo($path, PATHINFO_EXTENSION);
            $imgExtArr = ['jpg', 'jpeg'];
            if (!in_array($extension, $imgExtArr)) {
                return 0;
            }

            $exif = exif_read_data($path, 0, true);
            if ($exif && ($exif['IFD0']['Orientation'] ?? null)) {
                return match ($exif['IFD0']['Orientation']) {
                    6 => 90,
                    8 => -90,
                    default => 0
                };
            }

            return 0;
        } catch (\Throwable $e) {
            return 0;
        }
    }

    public function isImage($path)
    {
        try {
            $extension = pathinfo($path, PATHINFO_EXTENSION);
            $imgExtArr = ['jpg', 'jpeg', 'png', 'gif'];
            return in_array($extension, $imgExtArr);

        } catch (\Throwable $e) {
            return false;
        }
    }
}