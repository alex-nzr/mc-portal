<?php
/**
 * ==================================================
 * Developer: Alexey Nazarov
 * E-mail: alsnazarov@1cbit.ru
 * Copyright (c) 2019 - 2022
 * ==================================================
 * mc-portal - Logger.php
 * 21.11.2022 12:26
 * ==================================================
 */

namespace Cbit\Mc\Zup\Internals\Debug;

use Cbit\Mc\Zup\Config\Constants;

/**
 * Class Logger
 * @package Cbit\Mc\Zup\Internals\Debug
 */
class Logger extends \Cbit\Mc\Core\Internals\Debug\Logger
{
    /**
     * @param ...$vars
     */
    public static function printToFile(...$vars)
    {
        foreach ($vars as $key => $var) {
            static::writeToFile($var, $key.')---', Constants::PATH_TO_LOGFILE);
        }
    }
}