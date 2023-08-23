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

namespace Cbit\Mc\Staffing\Internals\Debug;

use Cbit\Mc\Staffing\Config\Constants;

/**
 * Class Logger
 * @package Cbit\Mc\Staffing\Internals\Debug
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