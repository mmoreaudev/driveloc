<?php
declare(strict_types=1);

final class ImageCacheService
{
    private const CACHE_RELATIVE_DIR = '/assets/cache/vehicles';
    private const MAX_WIDTH = 1400;
    private const JPEG_QUALITY = 76;

    public static function cacheExternalImage(?string $url): ?string
    {
        if (!is_string($url) || $url === '') {
            return $url;
        }

        if (self::isLocalUrl($url)) {
            return $url;
        }

        if (!filter_var($url, FILTER_VALIDATE_URL)) {
            return $url;
        }

        $scheme = (string) parse_url($url, PHP_URL_SCHEME);
        if (!in_array(strtolower($scheme), ['http', 'https'], true)) {
            return $url;
        }

        $cacheDir = ROOT_PATH . self::CACHE_RELATIVE_DIR;
        if (!is_dir($cacheDir) && !@mkdir($cacheDir, 0775, true) && !is_dir($cacheDir)) {
            return $url;
        }

        $fileBase = hash('sha256', $url);
        $cacheFile = $cacheDir . '/' . $fileBase . '.jpg';
        if (is_file($cacheFile)) {
            return APP_URL . self::CACHE_RELATIVE_DIR . '/' . basename($cacheFile);
        }

        $context = stream_context_create([
            'http' => ['timeout' => 8, 'follow_location' => 1],
            'https' => ['timeout' => 8, 'follow_location' => 1],
        ]);

        $binary = @file_get_contents($url, false, $context);
        if ($binary === false || $binary === '') {
            return $url;
        }

        if (!function_exists('imagecreatefromstring') || !function_exists('imagejpeg')) {
            if (@file_put_contents($cacheFile, $binary) === false) {
                return $url;
            }
            return APP_URL . self::CACHE_RELATIVE_DIR . '/' . basename($cacheFile);
        }

        $source = @imagecreatefromstring($binary);
        if ($source === false) {
            if (@file_put_contents($cacheFile, $binary) === false) {
                return $url;
            }
            return APP_URL . self::CACHE_RELATIVE_DIR . '/' . basename($cacheFile);
        }

        $srcW = imagesx($source);
        $srcH = imagesy($source);

        if ($srcW <= 0 || $srcH <= 0) {
            imagedestroy($source);
            return $url;
        }

        $dstW = min(self::MAX_WIDTH, $srcW);
        $dstH = (int) round(($srcH / $srcW) * $dstW);
        $destination = imagecreatetruecolor($dstW, max(1, $dstH));

        $white = imagecolorallocate($destination, 255, 255, 255);
        imagefill($destination, 0, 0, $white);

        imagecopyresampled($destination, $source, 0, 0, 0, 0, $dstW, max(1, $dstH), $srcW, $srcH);

        $saved = @imagejpeg($destination, $cacheFile, self::JPEG_QUALITY);

        imagedestroy($source);
        imagedestroy($destination);

        if (!$saved) {
            return $url;
        }

        return APP_URL . self::CACHE_RELATIVE_DIR . '/' . basename($cacheFile);
    }

    private static function isLocalUrl(string $url): bool
    {
        if (str_starts_with($url, '/')) {
            return true;
        }

        if (str_starts_with($url, APP_URL . '/')) {
            return true;
        }

        return !str_starts_with($url, 'http://') && !str_starts_with($url, 'https://');
    }
}
