<?php
declare(strict_types=1);

namespace Manager;

class CacheManager
{
    public static string $cachePath = __DIR__ . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . 'temp' . DIRECTORY_SEPARATOR . 'cache';


    public static function load(string $filename, ?\DateTime $dateStart, ?\DateTime $dateEnd): ?array
    {
        $filePath = self::$cachePath . DIRECTORY_SEPARATOR . $filename . '.txt';

        if(!file_exists($filePath))
        {
            return null;
        }

        $fileContent = json_decode(file_get_contents($filePath), true);

        if(!$fileContent || !is_array($fileContent))
        {
            return null;
        }

        $startKey = $dateStart ? array_search($dateStart->format('Y-m-d'), array_keys($fileContent), true) : null;
        $endKey = $dateEnd ? array_search($dateEnd->format('Y-m-d'), array_keys($fileContent), true) : null;

        if($startKey)
        {
            $fileContent = array_slice($fileContent, $startKey, $endKey ? $endKey - $startKey + 1 : null, true);
        }

        return $fileContent;
    }


    public static function save(string $filename, array $content): void
    {
        $filePath = self::$cachePath . DIRECTORY_SEPARATOR . $filename . '.txt';

        file_put_contents($filePath, json_encode($content));
    }
}