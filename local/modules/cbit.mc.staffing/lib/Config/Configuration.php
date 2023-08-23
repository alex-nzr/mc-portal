<?php
/**
 * ==================================================
 * Developer: Alexey Nazarov
 * E-mail: alsnazarov@1cbit.ru
 * Copyright (c) 2019 - 2022
 * ==================================================
 * mc-portal - Configuration.php
 * 17.01.2023 12:00
 * ==================================================
 */


namespace Cbit\Mc\Staffing\Config;

use Cbit\Mc\Core\Config\Configuration as CoreConfig;
use Cbit\Mc\Core\Helper\Iblock\IblockElement;

/**
 * Class Configuration
 * @package Cbit\Mc\Staffing\Config
 */
class Configuration
{
    private static ?Configuration $instance = null;

    private function __construct(){}

    /**
     * @return \Cbit\Mc\Staffing\Config\Configuration
     */
    public static function getInstance(): Configuration
    {
        if (static::$instance === null)
        {
            static::$instance = new static();
        }
        return static::$instance;
    }

    /**
     * @return \string[][]
     */
    public function getCustomPagesMap(): array
    {
        return [
            Constants::CUSTOM_PAGE_BINDING => [
                'TITLE'     => 'Staffing binder',
                'COMPONENT' => 'cbit:mc.staffing.binder',
            ],
            Constants::CUSTOM_PAGE_LIST => [
                'TITLE'     => 'Staffing projects',
                'COMPONENT' => 'bitrix:crm.item.list',
            ],
        ];
    }

    /**
     * @return int
     * @throws \Exception
     */
    public function getProjectIndustriesIblockId(): int
    {
        return CoreConfig::getInstance()->getIndustriesIblockId();
    }

    /**
     * @return int
     * @throws \Exception
     */
    public function getProjectStatesIblockId(): int
    {
        return CoreConfig::getInstance()->getProjectStatesIBlockId();
    }

    /**
     * @return array
     * @throws \Exception
     */
    public function getProjectIndustriesList(): array
    {
        return CoreConfig::getInstance()->getIndustriesList();
    }

    /**
     * @return array
     * @throws \Exception
     */
    public function getProjectFunctionsList(): array
    {
        return CoreConfig::getInstance()->getFunctionsList();
    }

    /**
     * @return array
     * @throws \Exception
     */
    public function getPerDiemEditReasonsList(): array
    {
        $id = CoreConfig::getInstance()->getPerDiemEditReasonsIblockId();
        return IblockElement::getElementsListToFilter($id);
    }

    /**
     * @return array
     */
    public function getStaffingEmploymentTypes(): array
    {
        return [
            Constants::STAFFING_EMPLOYMENT_TYPE_STAFF,
            Constants::STAFFING_EMPLOYMENT_TYPE_BEACH,
        ];
    }

    /**
     * @return array
     */
    public function getStaffingUserRoles(): array
    {
        return CoreConfig::getInstance()->getUserPositionsEnAvailableInStaffing();
    }

    private function __clone(){}
    public function __wakeup(){}
}