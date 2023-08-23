<?php
/**
 * ==================================================
 * Developer: Alexey Nazarov
 * E-mail: alsnazarov@1cbit.ru
 * Copyright (c) 2019 - 2022
 * ==================================================
 * mc-portal - Configuration.php
 * 21.11.2022 12:00
 * ==================================================
 */
namespace Cbit\Mc\Timesheets\Config;

/**
 * Class Configuration
 * @package Cbit\Mc\Timesheets\Config
 */
class Configuration
{
    private static ?Configuration $instance = null;

    private function __construct(){}

    /**
     * @return \Cbit\Mc\Timesheets\Config\Configuration
     */
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