<?php
/**
 * ==================================================
 * Developer: Alexey Nazarov
 * E-mail: alsnazarov@1cbit.ru
 * Copyright (c) 2019 - 2022
 * ==================================================
 * mc-portal - Configuration.php
 * 10.11.2022 22:00
 * ==================================================
 */


namespace Cbit\Mc\Profile\Config;

use Bitrix\Iblock\IblockTable;
use Exception;

/**
 * Class Configuration
 * @package Cbit\Mc\Profile\Config
 */
class Configuration
{
    private static ?Configuration $instance = null;
    private ?int $projectSegmentsIblockId = null;


    private function __construct(){}

    public static function getInstance(): Configuration
    {
        if (static::$instance === null)
        {
            static::$instance = new static();
        }
        return static::$instance;
    }

    private function __clone(){}
    public function __wakeup(){}
}