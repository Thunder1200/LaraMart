<?php

/**
 * Hızlı Yerel Sunucu Yönlendiricisi (PHP Built-in Server).
 * Statik görsel, CSS ve JS dosyalarına tarayıcı önbelleği (Cache-Control + ETag/304)
 * ve metin varlıklarına gzip sıkıştırması ekler.
 */
$uri = urldecode(parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?: '/');
$publicRoot = realpath(__DIR__);
$filePath = $publicRoot !== false ? realpath($publicRoot.$uri) : false;

if ($uri !== '/' && $filePath !== false && $publicRoot !== false && str_starts_with($filePath, $publicRoot) && is_file($filePath)) {
    $ext = strtolower(pathinfo($filePath, PATHINFO_EXTENSION));
    $mimeTypes = [
        'webp' => 'image/webp',
        'jpg' => 'image/jpeg',
        'jpeg' => 'image/jpeg',
        'png' => 'image/png',
        'gif' => 'image/gif',
        'svg' => 'image/svg+xml',
        'ico' => 'image/x-icon',
        'css' => 'text/css; charset=utf-8',
        'js' => 'application/javascript; charset=utf-8',
        'json' => 'application/json; charset=utf-8',
        'woff2' => 'font/woff2',
        'woff' => 'font/woff',
    ];

    if (isset($mimeTypes[$ext])) {
        $mtime = filemtime($filePath) ?: time();
        $size = filesize($filePath) ?: 0;
        $etag = '"'.md5($mtime.'-'.$size).'"';

        header('Content-Type: '.$mimeTypes[$ext]);
        header('Cache-Control: public, max-age=604800, immutable');
        header('ETag: '.$etag);
        header('Last-Modified: '.gmdate('D, d M Y H:i:s', $mtime).' GMT');

        if (($_SERVER['HTTP_IF_NONE_MATCH'] ?? '') === $etag) {
            http_response_code(304);

            return true;
        }

        $compressible = in_array($ext, ['css', 'js', 'svg', 'json'], true);
        $acceptsGzip = str_contains((string) ($_SERVER['HTTP_ACCEPT_ENCODING'] ?? ''), 'gzip');

        if ($compressible && $acceptsGzip && function_exists('gzencode')) {
            $compressed = gzencode((string) file_get_contents($filePath), 6);
            if ($compressed !== false) {
                header('Content-Encoding: gzip');
                header('Vary: Accept-Encoding');
                header('Content-Length: '.strlen($compressed));
                echo $compressed;

                return true;
            }
        }

        header('Content-Length: '.$size);
        readfile($filePath);

        return true;
    }

    return false;
}

require_once __DIR__.'/index.php';
