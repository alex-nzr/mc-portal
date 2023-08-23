<?php
/**
 * ==================================================
 * Developer: Alexey Nazarov
 * E-mail: alsnazarov@1cbit.ru
 * Copyright (c) 2019 - 2022
 * ==================================================
 * mc-portal - IblockElement.php
 * 28.11.2022 13:44
 * ==================================================
 */


namespace Cbit\Mc\Core\Helper\Iblock;

use Bitrix\Iblock\ElementTable;
use Bitrix\Iblock\PropertyEnumerationTable;
use Bitrix\Main\Error;
use Bitrix\Main\Result;
use CIBlockElement;
use Exception;

/**
 * Class IblockElement
 * @package Cbit\Mc\Core\Helper\Iblock
 */
class IblockElement
{
    /**
     * @param int $iblockId
     * @param array $elementData
     * @param array $propsData
     * @return \Bitrix\Main\Result
     */
    public static function createElement(int $iblockId, array $elementData, array $propsData = []): Result
    {
        $result = new Result();
        try
        {
            $fields = [
                "IBLOCK_ID"      => $iblockId,
                "NAME"           => $elementData['NAME'],
                "ACTIVE"         => "Y",
                "SORT"           => $elementData['SORT'] ?? 100,
                "XML_ID"         => $elementData['XML_ID'],
                "PROPERTY_VALUES"=> $propsData,
            ];

            $el = new CIBlockElement;
            $addRes = $el->Add($fields, false, false, false);
            if (!$addRes)
            {
                throw new Exception(
                    'Error on creation element in iBlock' . $iblockId
                    . ". Elem xmlId - " . $elementData["XML_ID"]
                    . ". Elem name - " . $elementData["NAME"]
                    . ". " . $el->LAST_ERROR);
            }
        }
        catch (Exception $e)
        {
            $result->addError(new Error($e->getMessage()));
        }

        return $result;
    }

    /**
     * @param int $iblockId
     * @param int $elemId
     * @param array $elementData
     * @param array $propsData
     * @return \Bitrix\Main\Result
     */
    public static function updateElement(int $iblockId, int $elemId, array $elementData, array $propsData = []): Result
    {
        $result = new Result();
        try
        {
            $fields = [
                "IBLOCK_ID"      => $iblockId,
                "NAME"           => $elementData['NAME'],
                "ACTIVE"         => "Y",
                "SORT"           => $elementData['SORT'] ?? 100,
                "XML_ID"         => $elementData['XML_ID'],
                "PROPERTY_VALUES"=> $propsData,
            ];

            $el = new CIBlockElement;
            $updRes = $el->Update(
                $elemId, $fields, false, false, false, false
            );
            if (!$updRes)
            {
                throw new Exception(
                    'Error on updating element in iBlock' . $iblockId
                    . ". Elem xmlId - " . $elementData["XML_ID"]
                    . ". Elem name - " . $elementData["NAME"]
                    . ". " . $el->LAST_ERROR);
            }
        }
        catch (Exception $e)
        {
            $result->addError(new Error($e->getMessage()));
        }

        return $result;
    }

    /**
     * @param int $iblockId
     * @param array $filter
     * @return array
     * @throws \Exception
     */
    public static function getElementsDataByFilter(int $iblockId, array $filter = []): array
    {
        $fieldCodes = ['ID', 'IBLOCK_ID', 'XML_ID', 'NAME', 'CODE'];
        $propCodes = Iblock::getIblockPropertyCodes($iblockId);

        $iblock = \Bitrix\Iblock\Iblock::wakeUp($iblockId);

        $elements = $iblock->getEntityDataClass()::query()
            ->setSelect(array_merge($fieldCodes, $propCodes))
            ->setFilter($filter)
            ->fetchCollection()
            ->getAll();

        $res = [];
        foreach ($elements as $element) {
            $el = [];
            foreach ($fieldCodes as $fieldCode) {
                $el[$fieldCode] = $element->get($fieldCode);
            }
            foreach ($propCodes as $propCode) {
                $propObject = $element->get($propCode);
                $el[$propCode] = $propObject ? $propObject->getValue() : '';
            }
            $res[] = $el;
        }
        return $res;
    }

    /**
     * @param int $iblockId
     * @param string $xmlId
     * @return array
     * @throws \Exception
     */
    public static function getElementDataByXmlId(int $iblockId, string $xmlId): array
    {
        $res = static::getElementsDataByFilter($iblockId, ['=XML_ID' => $xmlId]);
        return !empty($res) ? current($res) : $res;
    }

    /**
     * @param int $iblockId
     * @param string $xmlId
     * @return int
     * @throws \Exception
     */
    public static function getElementIdByXmlId(int $iblockId, string $xmlId): int
    {
        return (int)static::getElementDataByXmlId($iblockId, $xmlId)['ID'];
    }

    /**
     * @param int $enumId
     * @return string
     * @throws \Exception
     */
    public static function getEnumPropertyValueById(int $enumId): string
    {
        $res = PropertyEnumerationTable::query()
            ->setSelect(['VALUE'])
            ->setFilter(['=ID' => $enumId])
            ->fetch();
        return is_array($res) ? $res['VALUE'] : '';
    }

    /**
     * @param int $iblockId
     * @param string $propCode
     * @param string $value
     * @return int|null
     * @throws \Exception
     */
    public static function getEnumPropertyIdByValue(int $iblockId, string $propCode, string $value): ?int
    {
        $propertyId = Iblock::getIblockPropertyIdByCode($iblockId, $propCode);
        $res = PropertyEnumerationTable::query()
            ->setSelect(['ID'])
            ->setFilter([
                '=PROPERTY_ID' => $propertyId,
                '=VALUE'       => $value
            ])
            ->fetch();
        return is_array($res) ? (int)$res['ID'] : null;
    }

    /**
     * @param $id
     * @return array
     * @throws \Exception
     */
    public static function getElementById($id): array
    {
        if (empty($id))
        {
            return [];
        }

        $elem = ElementTable::query()->setSelect(['IBLOCK_ID'])->where('ID', '=', $id)->fetch();
        if (!empty($elem))
        {
            $res = static::getElementsDataByFilter((int)$elem['IBLOCK_ID'], ['=ID' => $id]);
            return !empty($res) ? current($res) : $res;
        }
        return [];
    }

    /**
     * @param int $iblockId
     * @return array
     * @throws \Exception
     */
    public static function getElementsListToFilter(int $iblockId): array
    {
        $items = ElementTable::query()
                ->setSelect(['ID', 'NAME'])
                ->where('IBLOCK_ID', '=', $iblockId)
                ->whereNull('WF_PARENT_ELEMENT_ID')
                ->fetchAll();

        $res = [];
        foreach ($items as $item)
        {
                $res[$item['ID']] = $item['NAME'];
        }
        return $res;
    }
}