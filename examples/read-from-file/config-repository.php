<?php

namespace MultiIvr\Examples;

require_once '../../vendor/autoload.php';

/**
 * Class ConfigRepository
 * @package MultiIvr\Examples
 */
class ConfigRepository
{
    private const FILE = 'config.txt';

    /**
     * @return string
     */
    public static function getConfig(): string
    {
        if (file_exists(self::FILE)) {
            return file_get_contents(self::FILE) ?: '';
        }
        return '';
    }

    /**
     * @param string $configTxt
     */
    public static function saveConfig(string $configTxt): void
    {
        file_put_contents(
            self::FILE,
            $configTxt
        );
    }

    /**
     * @return string[]
     */
    public static function getKeys(): array
    {
        return ['your key', 'your secret'];
    }
}
