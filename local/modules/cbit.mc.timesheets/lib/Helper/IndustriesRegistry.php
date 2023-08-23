<?php
/**
 * ==================================================
 * Developer: Alexey Nazarov
 * E-mail: alsnazarov@1cbit.ru
 * Copyright (c) 2019 - 2022
 * ==================================================
 * mc-portal - IndustriesRegistry.php
 * 14.12.2022 12:19
 * ==================================================
 */
namespace Cbit\Mc\Timesheets\Helper;

use Bitrix\Main\Result;
use Cbit\Mc\Core\Config\Configuration as CoreConfig;
use Cbit\Mc\Core\Helper\Iblock\IblockElement;
use Cbit\Mc\Core\Tools\Utils;
use Exception;

/**
 * Class IndustriesRegistry
 * @package Cbit\Mc\Timesheets\Helper
 */
class IndustriesRegistry
{
    /**
     * @param array $item
     * @return \Bitrix\Main\Result
     * @throws \Exception
     */
    public static function processIndustryItem(array $item): Result
    {
        if (empty($item['UUID'])){
            throw new Exception('UUID is required field in processIndustryItem.');
        }
        if (empty($item['Name'])){
            throw new Exception('Name is required field in processIndustryItem.');
        }

        $iblockId = CoreConfig::getInstance()->getIndustriesIblockId();

        $elementData = [
            'XML_ID' => $item['UUID'],
            'NAME'   => $item['Name']
        ];

        $propsData = [
            "INDUSTRY_COLOR" => Utils::generateRandomColor()
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