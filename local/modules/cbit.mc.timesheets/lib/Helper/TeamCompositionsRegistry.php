<?php
/**
 * ==================================================
 * Developer: Alexey Nazarov
 * E-mail: alsnazarov@1cbit.ru
 * Copyright (c) 2019 - 2022
 * ==================================================
 * mc-portal - TeamCompositionsRegistry.php
 * 14.12.2022 13:09
 * ==================================================
 */
namespace Cbit\Mc\Timesheets\Helper;

use Bitrix\Main\Result;
use Cbit\Mc\Core\Config\Configuration as CoreConfig;
use Cbit\Mc\Core\Helper\Iblock\IblockElement;
use Exception;

/**
 * Class TeamCompositionsRegistry
 * @package Cbit\Mc\Timesheets\Helper
 */
class TeamCompositionsRegistry
{
    /**
     * @param array $item
     * @return \Bitrix\Main\Result
     * @throws \Exception
     */
    public static function processTeamCompositionItem(array $item): Result
    {
        if (empty($item['UUID'])){
            throw new Exception('UUID is required field in processTeamCompositionItem.');
        }
        if (empty($item['Name'])){
            throw new Exception('Name is required field in processTeamCompositionItem.');
        }

        $iblockId = CoreConfig::getInstance()->getTeamCompositionsIBlockId();

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