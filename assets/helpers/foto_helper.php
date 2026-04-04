<?php

if (!function_exists('resolveFotoWebPath')) {
    function resolveFotoWebPath(?string $filename): string
    {
        $baseFsPath = __DIR__ . '/../Foto/';
        $baseWebPath = '/PJBL-main/assets/Foto/';

        if (!empty($filename) && (str_starts_with($filename, 'http://') || str_starts_with($filename, 'https://'))) {
            return $filename;
        }

        $cleanFilename = trim((string) $filename);
        $cleanFilename = str_replace('\\', '/', $cleanFilename);
        $cleanFilename = ltrim($cleanFilename, '/');

        if ($cleanFilename === '') {
            return $baseWebPath . 'ui/background.png';
        }

        $directPath = $baseFsPath . str_replace('/', DIRECTORY_SEPARATOR, $cleanFilename);
        if (is_file($directPath)) {
            return $baseWebPath . $cleanFilename;
        }

        $basename = basename($cleanFilename);
        $iterator = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($baseFsPath, FilesystemIterator::SKIP_DOTS)
        );

        foreach ($iterator as $fileInfo) {
            if ($fileInfo->isFile() && strcasecmp($fileInfo->getFilename(), $basename) === 0) {
                $relativePath = str_replace('\\', '/', substr($fileInfo->getPathname(), strlen($baseFsPath)));
                return $baseWebPath . ltrim($relativePath, '/');
            }
        }

        return $baseWebPath . 'ui/background.png';
    }
}
