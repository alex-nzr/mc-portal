<?php
/**
 * ==================================================
 * Developer: Alexey Nazarov
 * E-mail: alsnazarov@1cbit.ru
 * Copyright (c) 2019 - 2022
 * ==================================================
 * mc-portal - Iblock.php
 * 13.12.2022 18:30
 * ==================================================
 */
namespace Cbit\Mc\Core\Helper\Iblock;

use Bitrix\Iblock\IblockTable;
use Bitrix\Iblock\PropertyTable;
use Bitrix\Main\Error;
use Bitrix\Main\ORM\Query\Filter\ConditionTree;
use Bitrix\Main\ORM\Query\Query;
use Bitrix\Main\Result;
use CIBlock;
use CIBlockProperty;
use CIBlockPropertyEnum;
use Exception;

/**
 * Class Iblock
 * @package Cbit\Mc\Core\Helper\Iblock
 */
class Iblock
{
    /**
     * @param string $name
     * @param string $code
     * @param string $description
     * @param array $propsList
     * @return \Bitrix\Main\Result
     */
    public static function createIblock(string $name, string $code, string $description, array $propsList): Result
    {
        $result = new Result();
        $ib = new CIBlock;
        $arFields = [
            "ACTIVE"           => "Y",
            "NAME"             => $name,
            "CODE"             => $code,
            "API_CODE"         => $code,
            "XML_ID"           => $code,
            "IBLOCK_TYPE_ID"   => 'lists',
            "LID"              => "s1",
            "SORT"             => 100,
            "DESCRIPTION"      => $description,
            "DESCRIPTION_TYPE" => 'text',
            "VERSION"          => 2,
            "GROUP_ID"         => ["1"=>"X"],
        ];

        $iblockId = (int)$ib->Add($arFields);
        if ($iblockId > 0)
        {
            $propRes = self::setIBlockProps($iblockId, $propsList);
            if (!$propRes->isSuccess())
            {
                $result->addErrors($propRes->getErrors());
            }
        }
        else
        {
            $result->addError(new Error("Error on iblock '$code' creation - " . $ib->LAST_ERROR));
        }

        return $result;
    }

    /**
     * @param int $iblockId
     * @param array $propsList
     * @return \Bitrix\Main\Result
     */
    public static function setIBlockProps(int $iblockId, array $propsList): Result
    {
        $result = new Result();

        foreach ($propsList as $index => $prop) {
            $arFields = [
                'NAME'          => $prop['name'],
                'ACTIVE'        => 'Y',
                'SORT'          => ($index + 1) * 100,
                'CODE'          => $prop['code'],
                'PROPERTY_TYPE' => $prop['type'],
                "LIST_TYPE"     => $prop['list_type'],
                'IBLOCK_ID'     => $iblockId,
                'XML_ID'        => "iBlock".$iblockId."_".$prop['code'],
            ];
            $ibp = new CIBlockProperty;
            $propId = $ibp->Add($arFields);
            if ((int)$propId <= 0){
                $result->addError(new Error("Error on creating iblock $iblockId props - ". $ibp->LAST_ERROR));
            }
            else
            {
                if (!empty($prop['list_items']))
                {
                    foreach ($prop['list_items'] as $key => $listItem) {
                        $obEnum = new CIBlockPropertyEnum;
                        $obEnum->Add([
                            'PROPERTY_ID' => $propId,
                            'VALUE'       => $listItem,
                            'XML_ID'      => "iblock". $iblockId . "_prop" . $propId . "_value" .  $key
                        ]);
                    }
                }
            }
        }
        return $result;
    }

    /**
     * @param string $code
     * @return int
     * @throws \Exception
     */
    public static function getIblockIdByCode(string $code): int
    {
        $iblock = IblockTable::query()
            ->setSelect(['ID'])
            ->where(Query::filter()
                ->logic(ConditionTree::LOGIC_OR)
                ->where('CODE',     '=', $code)
                ->where('API_CODE', '=', $code)
            )
            ->fetch();

        if (!empty($iblock))
        {
            return (int)$iblock['ID'];
        }
        return 0;
    }

    /**
     * @param int $iblockId
     * @return array
     * @throws \Exception
     */
    public static function getIblockPropertyCodes(int $iblockId): array
    {
        $props = PropertyTable::query()
            ->setSelect(['ID', 'CODE'])
            ->where('IBLOCK_ID', '=', $iblockId)
            ->fetchAll();

        $res = [];
        foreach ($props as $prop)
        {
            $res[$prop['ID']] = $prop['CODE'];
        }
        return $res;
    }

    /**
     * @param int $iblockId
     * @param string $code
     * @return int
     * @throws \Exception
     */
    public static function getIblockPropertyIdByCode(int $iblockId, string $code): int
    {
        $prop = PropertyTable::query()
            ->setSelect(['ID'])
            ->setFilter([
                'IBLOCK_ID' => $iblockId,
                'CODE'      => $code
            ])
            ->fetch();
        return is_array($prop) ? (int)$prop['ID'] : 0;
    }
}