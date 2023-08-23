<?php
/**
 * ==================================================
 * Developer: Alexey Nazarov
 * E-mail: alsnazarov@1cbit.ru
 * Copyright (c) 2019 - 2022
 * ==================================================
 * mc-portal - FunctionsRegistry.php
 * 14.12.2022 13:03
 * ==================================================
 */
namespace Cbit\Mc\Timesheets\Helper;

use Bitrix\Main\Result;
use Cbit\Mc\Core\Config\Configuration as CoreConfig;
use Cbit\Mc\Core\Helper\Iblock\IblockElement;
use Cbit\Mc\Core\Tools\Utils;
use Exception;

/**
 * Class FunctionsRegistry
 * @package Cbit\Mc\Timesheets\Helper
 */
class FunctionsRegistry
{
    /**
     * @param array $item
     * @return \Bitrix\Main\Result
     * @throws \Exception
     */
    public static function processFunctionItem(array $item): Result
    {
        if (empty($item['UUID'])){
            throw new Exception('UUID is required field in processFunctionItem.');
        }
        if (empty($item['Name'])){
            throw new Exception('Name is required field in processFunctionItem.');
        }

        $iblockId = CoreConfig::getInstance()->getFunctionsIBlockId();

        $elementData = [
            'XML_ID' => $item['UUID'],
            'NAME'   => $item['Name']
        ];

        $propsData = [
            "FUNCTION_COLOR" => Utils::generateRandomColor()
        ];

        $existingId = IblockElement::getElementIdByXmlId($iblockId, $elementData['XML_ID']);
        if ($existingId > 0)
        {
            //not update color
            return IblockElement::updateElement($iblockId, $existingId, $elementData);
        }
        else
        {
            return IblockElement::createElement($iblockId, $elementData, $propsData);
        }
    }
}