<?php
/**
 * ==================================================
 * Developer: Alexey Nazarov
 * E-mail: alsnazarov@1cbit.ru
 * Copyright (c) 2019 - 2022
 * ==================================================
 * mc-portal - Configuration.php
 * 15.12.2022 14:29
 * ==================================================
 */

namespace Cbit\Mc\RI\Service\Integration\Pull;

use Bitrix\Main\Loader;
use CPullOptions;
use Exception;

/**
 * Class Configuration
 * @package Cbit\Mc\RI\Service\Integration\Pull
 */
class Configuration
{
    private static string $error;

    /**
     * @return bool
     */
    public static function isAvailable(): bool
    {
        try
        {
            if (!Loader::includeModule('pull'))
            {
                throw new Exception('Can not include pull module');
            }

            if (!CPullOptions::GetQueueServerStatus())
            {
                throw new Exception('Pull server is unavailable');
            }

            return true;
        }
        catch (Exception $e)
        {
            static::$error = $e->getMessage();
            return false;
        }
    }

    /**
     * @return string
     */
    public static function getError(): string
    {
        return static::$error;
    }
}