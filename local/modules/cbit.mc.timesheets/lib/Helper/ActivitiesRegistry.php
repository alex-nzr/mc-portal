<?php
/**
 * ==================================================
 * Developer: Alexey Nazarov
 * E-mail: alsnazarov@1cbit.ru
 * Copyright (c) 2019 - 2022
 * ==================================================
 * mc-portal - ActivitiesRegistry.php
 * 13.12.2022 15:30
 * ==================================================
 */
namespace Cbit\Mc\Timesheets\Helper;

use Bitrix\Main\Result;
use Cbit\Mc\Core\Helper\Iblock\IblockElement;
use Cbit\Mc\Core\Config\Configuration as CoreConfig;
use Exception;

/**
 * Class ActivitiesRegistry
 * @package Cbit\Mc\Timesheets\Helper
 */
class ActivitiesRegistry
{
    /**
     * @param array $item
     * @return \Bitrix\Main\Result
     * @throws \Exception
     */
    public static function processActivityItem(array $item): Result
    {
        if (empty($item['UUID'])){
            throw new Exception('UUID is required field in processActivityItem.');
        }
        if (empty($item['Name'])){
            throw new Exception('Name is required field in processActivityItem.');
        }

        $iblockId = CoreConfig::getInstance()->getActivitiesIBlockId();

        $elementData = [
            'XML_ID' => $item['UUID'],
            'NAME'   => $item['Name']
        ];

        $propsData = [
            "ALLOW_STAFFING" => $item['AllowStaffing']
                ? IblockElement::getEnumPropertyIdByValue($iblockId, 'ALLOW_STAFFING', 'Y')
                : IblockElement::getEnumPropertyIdByValue($iblockId, 'ALLOW_STAFFING', 'N'),
            "ALLOW_RESTAFFING" => $item['AllowReStaffing']
                ? IblockElement::getEnumPropertyIdByValue($iblockId, 'ALLOW_RESTAFFING', 'Y')
                : IblockElement::getEnumPropertyIdByValue($iblockId, 'ALLOW_RESTAFFING', 'N'),
        ];

        $existingId = IblockElement::getElementIdByXmlId($iblockId, $elementData['XML_ID']);
        if ($existingId > 0)
        {
            return IblockElement::updateElement($iblockId, $existingId, $elementData, $propsData);
        }
        else
        {
            return IblockElement::createElement($iblockId, $elementData, $propsData);
        }
    }
}