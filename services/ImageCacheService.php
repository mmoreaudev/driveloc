<?php
declare(strict_types=1);

final class ImageCacheService
{
    private const CACHE_RELATIVE_DIR = '/assets/cache/vehicles';
    private const BASE_MAX_WIDTH = 1280;
    private const BASE_WEBP_QUALITY = 72;
    private const BASE_JPEG_QUALITY = 70;
    private const THUMB_MAX_BYTES = 71680;
    private const THUMB_START_WIDTH = 640;
    private const THUMB_MIN_WIDTH = 180;
    private const THUMB_START_QUALITY = 72;
    private const THUMB_MIN_QUALITY = 20;
    private const DOWNLOAD_TIMEOUT_SECONDS = 4;

    public static function cacheThumbnail(?string $url): ?string
    {
        if (!function_exists('imagewebp')) {
            return self::cacheBaseImage($url);
        }

        [$cacheDir, $fileBase, $normalizedUrl] = self::prepareCache($url);
        if ($cacheDir === null || $fileBase === null || $normalizedUrl === null) {
            return $url;
        }

        $thumbFile = $cacheDir . '/' . $fileBase . '_thumb.webp';
        if (is_file($thumbFile) && filesize($thumbFile) !== false && filesize($thumbFile) <= self::THUMB_MAX_BYTES) {
            return APP_URL . self::CACHE_RELATIVE_DIR . '/' . basename($thumbFile);
        }

        $source = self::loadImageFromUrl($normalizedUrl);
        if ($source === null) {
            return $url;
        }

        $srcW = imagesx($source);
        $srcH = imagesy($source);
        if ($srcW <= 0 || $srcH <= 0) {
            imagedestroy($source);
            return $url;
        }

        $bestData = null;
        $currentW = min(self::THUMB_START_WIDTH, $srcW);

        while ($currentW >= self::THUMB_MIN_WIDTH) {
            $dst = self::createResizedCanvas($source, $srcW, $srcH, $currentW);

            for ($q = self::THUMB_START_QUALITY; $q >= self::THUMB_MIN_QUALITY; $q -= 4) {
                $data = self::encodeWebpToString($dst, $q);
                if ($data === null) {
                    continue;
                }

                if ($bestData === null || strlen($data) < strlen($bestData)) {
                    $bestData = $data;
                }

                if (strlen($data) <= self::THUMB_MAX_BYTES) {
                    @file_put_contents($thumbFile, $data);
                    imagedestroy($dst);
                    imagedestroy($source);
                    return APP_URL . self::CACHE_RELATIVE_DIR . '/' . basename($thumbFile);
                }
            }

            imagedestroy($dst);
            $currentW = (int) floor($currentW * 0.85);
        }

        imagedestroy($source);

        if ($bestData !== null) {
            @file_put_contents($thumbFile, $bestData);
            return APP_URL . self::CACHE_RELATIVE_DIR . '/' . basename($thumbFile);
        }

        return $url;
    }

    public static function cacheBaseImage(?string $url): ?string
    {
        [$cacheDir, $fileBase, $normalizedUrl] = self::prepareCache($url);
        if ($cacheDir === null || $fileBase === null || $normalizedUrl === null) {
            return $url;
        }

        $preferredExt = function_exists('imagewebp') ? 'webp' : 'jpg';
        $baseFile = $cacheDir . '/' . $fileBase . '_base.' . $preferredExt;
        if (is_file($baseFile)) {
            return APP_URL . self::CACHE_RELATIVE_DIR . '/' . basename($baseFile);
        }

        $source = self::loadImageFromUrl($normalizedUrl);
        if ($source === null) {
            return $url;
        }

        $srcW = imagesx($source);
        $srcH = imagesy($source);
        if ($srcW <= 0 || $srcH <= 0) {
            imagedestroy($source);
            return $url;
        }

        $dstW = min(self::BASE_MAX_WIDTH, $srcW);
        $dst = self::createResizedCanvas($source, $srcW, $srcH, $dstW);

        if ($preferredExt === 'webp' && function_exists('imagewebp')) {
            $saved = @imagewebp($dst, $baseFile, self::BASE_WEBP_QUALITY);
        } else {
            $saved = @imagejpeg($dst, $baseFile, self::BASE_JPEG_QUALITY);
        }

        imagedestroy($dst);
        imagedestroy($source);

        if (!$saved) {
            return $url;
        }

        return APP_URL . self::CACHE_RELATIVE_DIR . '/' . basename($baseFile);
    }

    private static function prepareCache(?string $url): array
    {
        if (!is_string($url) || $url === '' || self::isLocalUrl($url) || !filter_var($url, FILTER_VALIDATE_URL)) {
            return [null, null, null];
        }

        $scheme = (string) parse_url($url, PHP_URL_SCHEME);
        if (!in_array(strtolower($scheme), ['http', 'https'], true)) {
            return [null, null, null];
        }

        $cacheDir = ROOT_PATH . self::CACHE_RELATIVE_DIR;
        if (!is_dir($cacheDir) && !@mkdir($cacheDir, 0775, true) && !is_dir($cacheDir)) {
            return [null, null, null];
        }

        return [$cacheDir, hash('sha256', $url), $url];
    }

    private static function loadImageFromUrl(string $url)
    {
        if (!function_exists('imagecreatefromstring')) {
            return null;
        }

        $context = stream_context_create([
            'http' => ['timeout' => self::DOWNLOAD_TIMEOUT_SECONDS, 'follow_location' => 1],
            'https' => ['timeout' => self::DOWNLOAD_TIMEOUT_SECONDS, 'follow_location' => 1],
        ]);

        $binary = @file_get_contents($url, false, $context);
        if ($binary === false || $binary === '') {
            return null;
        }

        $source = @imagecreatefromstring($binary);
        return $source === false ? null : $source;
    }

    private static function createResizedCanvas($source, int $srcW, int $srcH, int $dstW)
    {
        $dstW = max(1, $dstW);
        $dstH = max(1, (int) round(($srcH / $srcW) * $dstW));

        $destination = imagecreatetruecolor($dstW, $dstH);
        $white = imagecolorallocate($destination, 255, 255, 255);
        imagefill($destination, 0, 0, $white);

        imagecopyresampled($destination, $source, 0, 0, 0, 0, $dstW, $dstH, $srcW, $srcH);

        return $destination;
    }

    private static function encodeWebpToString($image, int $quality): ?string
    {
        ob_start();
        $ok = @imagewebp($image, null, $quality);
        $data = ob_get_clean();

        if (!$ok || !is_string($data) || $data === '') {
            return null;
        }

        return $data;
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
