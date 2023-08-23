<?php
/**
 * ==================================================
 * Developer: Alexey Nazarov
 * E-mail: alsnazarov@1cbit.ru
 * Copyright (c) 2019 - 2022
 * ==================================================
 * mc-portal - Logger.php
 * 17.01.2023 14:46
 * ==================================================
 */

namespace Cbit\Mc\RI\Internals\Debug;

use Cbit\Mc\RI\Config\Configuration;

/**
 * Class Logger
 * @package Cbit\Mc\RI\Internals\Debug
 */
class Logger extends \Cbit\Mc\Core\Internals\Debug\Logger
{
    /**
     * @param ...$vars
     */
    public static function printToFile(...$vars)
    {
        foreach ($vars as $key => $var) {
            static::writeToFile($var, $key.')---', Configuration::getInstance()->getLogFilePath());
        }
    }
}