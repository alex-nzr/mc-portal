<?php
/**
 * ==================================================
 * Developer: Alexey Nazarov
 * E-mail: alsnazarov@1cbit.ru
 * Copyright (c) 2019 - 2023
 * ==================================================
 * mc-portal - Logger.php
 * 16.01.2023 20:03
 * ==================================================
 */


namespace Cbit\Mc\Core\Internals\Debug;

use Bitrix\Main\Application;
use Bitrix\Main\Diag\Debug;

/**
 * @class Logger
 * @package Cbit\Mc\Core\Internals\Debug
 */
class Logger extends Debug
{
    /**
     * @param ...$vars
     */
    public static function print(...$vars){
        foreach ($vars as $key => $var) {
            echo "$key---------------------------------------<pre>";
            print_r($var);
            echo "</pre>";
        }
    }

    /**
     * @param $var
     * @param string $varName
     * @param string $fileName
     */
    public static function writeToFile($var, $varName = "", $fileName = "")
    {
        if (!is_dir(Application::getDocumentRoot() . '/local/logs'))
        {
            mkdir(Application::getDocumentRoot() . '/local/logs', 0777, true);
        }
        parent::writeToFile($var, $varName, $fileName);
    }
}