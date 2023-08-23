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


namespace Cbit\Mc\Expense\Internals\Control;

use Bitrix\Main\Config\Configuration;
use Bitrix\Main\Context;
use Bitrix\Main\DI\ServiceLocator;
use Bitrix\Main\Loader;
use Bitrix\Main\Request;
use Bitrix\Main\UI\Extension;
use Cbit\Mc\Expense\Config\Constants;
use Cbit\Mc\Expense\Controller;
use Cbit\Mc\Expense\Entity;
use Cbit\Mc\Expense\Filter\FilterFactory;
use Cbit\Mc\Expense\Helper\UI\Toolbar;
use Cbit\Mc\Expense\Service\Container;
use Cbit\Mc\Expense\Service\Integration\Intranet\CustomSectionProvider;
use Cbit\Mc\Expense\Service\Router;
use Exception;

/**
 * Class ServiceManager
 * @package Cbit\Mc\Expense\Internals\Control
 */
class ServiceManager
{
    private static ?ServiceManager $instance = null;
    private static ?string $moduleId = null;
    private static ?string $moduleParentDirectoryName = null;

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
    public function includeModule(): void
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
            'cbit.mc.core', 'cbit.mc.staffing', 'currency'
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
            'ui.alerts', 'cbit.mc.expense.main-ui'
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
        if ($this->isInDynamicTypeSection())
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
            $arr = explode(DIRECTORY_SEPARATOR, __FILE__);
            $i = array_search("modules", $arr);
            static::$moduleId = $arr[$i + 1];
        }
        return static::$moduleId;
    }

    /**
     * @return string
     */
    public static function getModuleParentDirectoryName(): string
    {
        if (empty(static::$moduleParentDirectoryName))
        {
            $arr = explode(DIRECTORY_SEPARATOR, __FILE__);
            $i = array_search("modules", $arr);
            static::$moduleParentDirectoryName = $arr[$i - 1];
        }
        return static::$moduleParentDirectoryName;
    }

    /**
     * @return bool
     * @throws \Exception
     */
    public function isInDynamicTypeSection(): bool
    {
        $needlePath_1 = '/crm/type/' . Entity\Dynamic::getInstance()->getEntityTypeId() . '/';
        $needlePath_2 = "/page/" . Constants::DYNAMIC_TYPE_CUSTOM_SECTION_CODE . "/";
        if ( (str_starts_with($this->getCurPage(), $needlePath_1)) || (str_starts_with($this->getCurPage(), $needlePath_2)) )
        {
            return true;
        }
        return false;
    }

    /**
     * @return void
     */
    public function addListPageExtensions(): void
    {
    }

    /**
     * @return void
     * @throws \Exception
     */
    public function addDetailPageUI(): void
    {
        Toolbar::addSplitAmountButton();
    }

    /**
     * called on event
     * @return void
     * @throws \Bitrix\Main\LoaderException
     */
    public static function addDetailPageExtensions(): void
    {
        Extension::load([
            'cbit.mc.core.entity-detail-manager',
            'cbit.mc.expense.ui-detail'
        ]);
    }

    /**
     * @return string
     */
    public function getCurPage(): string
    {
        return (string)Context::getCurrent()->getRequest()->getRequestedPage();
    }

    /**
     * @return void
     */
    private function checkAjaxRequest(): void
    {
        try
        {
            $request = Context::getCurrent()->getRequest();

            //break script while installing or uninstalling of module
            if ($request->get('id') === static::getModuleId()
                && ($request->get('install') === 'Y' || $request->get('uninstall') === 'Y')
            ){
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
        catch (Exception)
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
                (str_contains($params['FORM'], 'UF_CRM_' . $typeId))
                || (str_contains($params['FORM'], 'DYNAMIC_' . $entityTypeId))
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
                if (str_contains($key, 'UF_CRM_' . $typeId))
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

    private function __clone(){}
    public function __wakeup(){}
}