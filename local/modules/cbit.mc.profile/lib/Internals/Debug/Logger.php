<?php
/**
 * ==================================================
 * Developer: Alexey Nazarov
 * E-mail: alsnazarov@1cbit.ru
 * Copyright (c) 2019 - 2022
 * ==================================================
 * mc-portal - Logger.php
 * 31.10.2022 17:26
 * ==================================================
 */

namespace Cbit\Mc\Profile\Internals\Debug;

use Cbit\Mc\Profile\Config\Constants;

/**
 * @class Logger
 * @package Cbit\Mc\Profile\Internals\Debug
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