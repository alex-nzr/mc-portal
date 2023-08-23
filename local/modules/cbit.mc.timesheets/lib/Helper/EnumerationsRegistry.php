<?php
/**
 * ==================================================
 * Developer: Alexey Nazarov
 * E-mail: alsnazarov@1cbit.ru
 * Copyright (c) 2019 - 2022
 * ==================================================
 * mc-portal - EnumerationsRegistry.php
 * 14.12.2022 13:43
 * ==================================================
 */
namespace Cbit\Mc\Timesheets\Helper;

use Bitrix\Main\Result;
use Cbit\Mc\Core\Config\Configuration as CoreConfig;
use Cbit\Mc\Core\Helper\Iblock\IblockElement;
use Exception;

/**
 * Class EnumerationsRegistry
 * @package Cbit\Mc\Timesheets\Helper
 */
class EnumerationsRegistry
{
    /**
     * @param array $item
     * @return \Bitrix\Main\Result
     * @throws \Exception
     */
    public static function processPhaseItem(array $item): Result
    {
        if (empty($item['UUID'])){
            throw new Exception('UUID is required field in processPhaseItem.');
        }
        if (empty($item['Name'])){
            throw new Exception('Name is required field in processPhaseItem.');
        }

        $iblockId = CoreConfig::getInstance()->getProjectPhasesIBlockId();

        $elementData = [
            'XML_ID' => $item['UUID'],
            'NAME'   => $item['Name']
        ];

        $propsData = [];

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

    /**
     * @param array $item
     * @return \Bitrix\Main\Result
     * @throws \Exception
     */
    public static function processStateItem(array $item): Result
    {
        if (empty($item['UUID'])){
            throw new Exception('UUID is required field in processStateItem.');
        }
        if (empty($item['Name'])){
            throw new Exception('Name is required field in processStateItem.');
        }

        $iblockId = CoreConfig::getInstance()->getProjectStatesIBlockId();

        $elementData = [
            'XML_ID' => $item['UUID'],
            'NAME'   => $item['Name']
        ];

        $propsData = [];

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