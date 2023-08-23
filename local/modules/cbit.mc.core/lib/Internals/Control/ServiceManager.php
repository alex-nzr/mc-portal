<?php
/**
 * ==================================================
 * Developer: Alexey Nazarov
 * E-mail: alsnazarov@1cbit.ru
 * Copyright (c) 2019 - 2022
 * ==================================================
 * mc-portal - ServiceManager.php
 * 25.11.2022 12:11
 * ==================================================
 */


namespace Cbit\Mc\Core\Internals\Control;

use Bitrix\Main\Context;
use Bitrix\Main\Loader;
use Bitrix\Main\UI\Extension;
use Cbit\Mc\Core\Controller\Base;
use Exception;
use function GetModuleID;

/**
 * Class ServiceManager
 * @package Cbit\Mc\Core\Internals\Control
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
    public function includeModuleDependencies(): void
    {
        $this->includeControllers();
        $this->includeDependentModules();
        $this->includeDependentExtensions();
    }

    /**
     * @throws \Exception
     */
    private function includeControllers(): void
    {
        $arControllers = [
            Base::class  => 'lib/Controller/Base.php',
        ];

        Loader::registerAutoLoadClasses(static::getModuleId(), $arControllers);
    }

    /**
     * @throws \Exception
     */
    public function includeDependentModules(): void
    {
        $dependencies = [
            'im', 'ui', 'intranet', 'crm', 'pull', 'iblock', 'socialnetwork', 'tasks',
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
            'ui', 'ui.entity-selector',
            'ui.buttons', "ui.dialogs.messagebox",
            'ui.forms', 'calendar',
            'cbit.mc.core.main-ui',
            'cbit.mc.core.entity-detail-manager',
        ];

        if (Context::getCurrent()->getRequest()->isAdminSection())
        {
            $dependencies[] = 'cbit.mc.core.admin';
        }

        Extension::load($dependencies);
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