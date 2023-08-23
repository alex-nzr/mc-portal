<?php
/**
 * ==================================================
 * Developer: Alexey Nazarov
 * E-mail: alsnazarov@1cbit.ru
 * Copyright (c) 2019 - 2022
 * ==================================================
 * mc-portal - ServiceManager.php
 * 17.01.2023 12:11
 * ==================================================
 */


namespace Cbit\Mc\Staffing\Internals\Control;

use Bitrix\Main\Config\Configuration;
use Bitrix\Main\Context;
use Bitrix\Main\DI\ServiceLocator;
use Bitrix\Main\Loader;
use Bitrix\Main\Request;
use Bitrix\Main\UI\Extension;
use Cbit\Mc\Staffing\Config\Constants;
use Cbit\Mc\Staffing\Controller;
use Cbit\Mc\Staffing\Entity;
use Cbit\Mc\Staffing\Filter\FilterFactory;
use Cbit\Mc\Staffing\Service\Container;
use Cbit\Mc\Staffing\Service\Integration\Intranet\CustomSectionProvider;
use Cbit\Mc\Staffing\Service\Router;
use Exception;
use function GetModuleID;

/**
 * Class ServiceManager
 * @package Cbit\Mc\Staffing\Internals\Control
 */
class ServiceManager
{
    protected static ?ServiceManager $instance = null;
    protected static ?string $moduleId = null;

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
    public function includeModule()
    {
        $this->includeControllers();
        $this->includeDependentModules();
        $this->includeDependentExtensions();
        $this->includeCustomServices();
        $this->checkAjaxRequest();
    }

    /**
     * @throws \Exception
     */
    private function includeControllers(): void
    {
        $arControllers = [
            Controller\Base::class  => 'lib/Controller/Base.php',
        ];

        Loader::registerAutoLoadClasses(static::getModuleId(), $arControllers);
    }

    /**
     * @throws \Exception
     */
    private function includeDependentModules(): void
    {
        $dependencies = [
            'cbit.mc.core', 'cbit.mc.timesheets',
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
    private function includeDependentExtensions(): void
    {
        $dependencies = [
            'ui'
        ];

        foreach ($dependencies as $dependency) {
            Extension::load($dependency);
        }
    }

    /**
     * @throws \Exception
     */
    private function includeCustomServices(): void
    {
        if (Container::getInstance()->getRouter()->isInDynamicTypeSection())
        {
            $this->addCustomCrmServices();
            $this->addCustomSectionProvider();
        }
    }

    private function addCustomCrmServices(): void
    {
        ServiceLocator::getInstance()->addInstance('crm.service.container', new Container());
        ServiceLocator::getInstance()->addInstance('crm.service.router', new Router());
        ServiceLocator::getInstance()->addInstance('crm.filter.factory', new FilterFactory());
    }

    private static function addCustomSectionProvider(): void
    {
        $crmConfig = Configuration::getInstance('crm');
        $customSectionConfig = $crmConfig->get('intranet.customSection');
        if (is_array($customSectionConfig))
        {
            $customSectionConfig['provider'] = CustomSectionProvider::class;
        }
        else
        {
            $customSectionConfig = [
                'provider' => CustomSectionProvider::class,
            ];
        }
        $crmConfig->add('intranet.customSection', $customSectionConfig);
    }

    /**
     * @return string
     */
    public static function getModuleId(): string
    {
        if (empty(static::$moduleId))
        {
            static::$moduleId = (string)GetModuleID(__FILE__);
        }
        return static::$moduleId;
    }

    /**
     * @return void
     */
    public function addListPageExtensions(): void
    {
    }

    /**
     * @return void
     */
    public function addDetailPageUI(): void
    {
    }

    /**
     * @return void
     */
    private function checkAjaxRequest(): void
    {
        try
        {
            $request = Context::getCurrent()->getRequest();

            if (static::isModuleInstallingNow())
            {
                return;
            }

            $entityTypeId = Entity\Dynamic::getInstance()->getEntityTypeId();

            if ($request->isAjaxRequest())
            {
                $entityTypeIdCondition = ( (int)$request->get('entityTypeId') === $entityTypeId )
                                      || ( (int)$request->get('ENTITY_TYPE_ID') === $entityTypeId )
                                      || ( (int)$request->get('entityTypeID') === $entityTypeId );
                if ($entityTypeIdCondition)
                {
                    $this->addCustomCrmServices();
                }
                else
                {
                    if ($this->findDynamicSignsInRequest($request))
                    {
                        $this->addCustomCrmServices();
                    }
                }
            }
        }
        catch (Exception $e)
        {
            //log error
        }
    }

    /**
     * @param \Bitrix\Main\Request $request
     * @return bool
     * @throws \Exception
     */
    private function findDynamicSignsInRequest(Request $request): bool
    {
        $params       = $request->getValues();
        $typeId       = Entity\Dynamic::getInstance()->getTypeId();
        $entityTypeId = Entity\Dynamic::getInstance()->getEntityTypeId();

        if (is_string($params['FORM']) &&
            (
                (strpos($params['FORM'], 'UF_CRM_' . $typeId) !== false)
                || (strpos($params['FORM'], 'DYNAMIC_' . $entityTypeId) !== false)
            )
        ){
            return true;
        }

        if (is_array($params['FIELDS']))
        {
            $founded = false;
            foreach ($params['FIELDS'] as $field)
            {
                if ( !empty($field['ENTITY_ID']) && ($field['ENTITY_ID'] === 'CRM_'.$typeId) )
                {
                    $founded = true;
                    break;
                }
            }
            if ($founded){
                return true;
            }
        }

        if (is_array($params['data']))
        {
            $founded = false;
            foreach ($params['data'] as $key => $value)
            {
                if ( strpos($key, 'UF_CRM_' . $typeId) !== false )
                {
                    $founded = true;
                    break;
                }
            }
            if ($founded){
                return true;
            }
        }

        return false;
    }

    /**
     * @return bool
     */
    public static function isModuleInstallingNow(): bool
    {
        $request = Context::getCurrent()->getRequest();
        return $request->get('id') === static::getModuleId()
                && ($request->get('install') === 'Y' || $request->get('uninstall') === 'Y');
    }


    private function __clone(){}
    public function __wakeup(){}
}