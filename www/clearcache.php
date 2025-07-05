<?php
namespace ClearCache;

class ClearCache {

    const CACHE_DIR = __DIR__ . '/../temp/cache';

    public static function clearCache() {
        if(is_dir(static::CACHE_DIR)) {
            static::rrmdir(static::CACHE_DIR);

            echo "cache smazána";
        } else {
            echo "cache neexistuje";
        }
    }

    protected static function rrmdir($directoryPath) {
        $directory = opendir($directoryPath);
        while(false !== ($fileName = readdir($directory))) {
            if (($fileName != '.') && ($fileName != '..')) {
                $newFilePath = $directoryPath . '/' . $fileName;
                if (is_dir($newFilePath)) {
                    static::rrmdir($newFilePath);
                } else {
                    unlink($newFilePath);
                }
            }
        }
        closedir($directory);
        rmdir($directoryPath);
    }
}

ClearCache::clearCache();

