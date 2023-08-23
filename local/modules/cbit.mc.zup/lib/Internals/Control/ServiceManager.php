<?php
/**
 * ==================================================
 * Developer: Alexey Nazarov
 * E-mail: alsnazarov@1cbit.ru
 * Copyright (c) 2019 - 2022
 * ==================================================
 * mc-portal - ServiceManager.php
 * 21.11.2022 12:11
 * ==================================================
 */


namespace Cbit\Mc\Zup\Internals\Control;

use Bitrix\Main\Loader;
use Bitrix\Main\UI\Extension;
use Exception;
use function GetModuleID;

/**
 * Class ServiceManager
 * @package Cbit\Mc\Zup\Internals\Control
 */
class ServiceManager
{
    protected static ?ServiceManager $instance = null;

    private function __construct(){}

    public static function getInstance(): ServiceManager
    {
        if (static::$instance === null)
        {
            static::$instance = new static();
        }
        return static::$instance;
    }

    /**
     * @throws \Exception
     */
    public function includeModuleDependencies()
    {
        $this->includeDependentModules();
        $this->includeDependentExtensions();
    }

    /**
     * @throws \Exception
     */
    public function includeDependentModules(): void
    {
        $dependencies = [
            'cbit.mc.core',
        ];

        foreach ($dependencies as $dependency) {
            if (!Loader::includeModule($dependency)){
                throw new Exception("Can not include module '$dependency'");
            }
        }
    }

    /**
     * @return void
     * @throws \Bitrix\Main\LoaderException
     */
    public function includeDependentExtensions(): void
    {
        $dependencies = [
            'ui',
        ];

        foreach ($dependencies as $dependency) {
            Extension::load($dependency);
        }
    }

    /**
     * @return string
     */
    public static function getModuleId(): string
    {
        return (string)GetModuleID(__FILE__);
    }

    private function __clone(){}
    public function __wakeup(){}
}