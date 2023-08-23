<?php
/**
 * ==================================================
 * Developer: Alexey Nazarov
 * E-mail: alsnazarov@1cbit.ru
 * Copyright (c) 2019 - 2023
 * ==================================================
 * mc-portal - IBlockInstaller.php
 * 20.01.2023 14:43
 * ==================================================
 */
namespace Cbit\Mc\Expense\Internals\Installation;

use Bitrix\Main\Result;
use Cbit\Mc\Core\Config\Constants as CoreConstants;
use Cbit\Mc\Core\Helper\Iblock\Iblock;

/**
 * @class IBlockInstaller
 * @package Cbit\Mc\Expense\Internals\Installation
 */
class IBlockInstaller
{
    /**
     * @return \Bitrix\Main\Result
     * @throws \Exception
     */
    public static function install(): Result
    {
        $res = new Result();

        $iblockData = static::getIblockData();

        foreach ($iblockData as $iblockItem)
        {
            $iblockId = Iblock::getIblockIdByCode($iblockItem['CODE']);
            if ($iblockId <= 0)
            {
                $createRes = Iblock::createIblock(
                    $iblockItem['NAME'], $iblockItem['CODE'], $iblockItem['DESC'],
                    static::getIblockPropsByCode($iblockItem['CODE'])
                );
                if (!$createRes->isSuccess())
                {
                    $res->addErrors($createRes->getErrors());
                }
            }
        }
        return $res;
    }

    /**
     * @param string $code
     * @return array
     */
    private static function getIblockPropsByCode(string $code): array
    {
        $props = [];
        switch ($code)
        {
            case CoreConstants::EXPENSE_LOCATIONS_IBLOCK_CODE:
                $props = [
                    [
                        "code"      => "CATEGORY",
                        "name"      => "Category",
                        "type"      => "L",
                        "list_type" => 'C',
                        'list_items'=> ['A', 'B', 'C']
                    ],
                    [
                        "code"      => "AUTO_CATEGORY",
                        "name"      => "Auto category",
                        "type"      => "L",
                        "list_type" => 'C',
                        'list_items'=> ['A', 'B', 'C']
                    ],
                ];
                break;
            default:
                break;
        }
        return $props;
    }

    /**
     * @return array[]
     */
    private static function getIblockData(): array
    {
        return [
            [
                'NAME' => 'Expense locations',
                'CODE' => CoreConstants::EXPENSE_LOCATIONS_IBLOCK_CODE,
                'DESC' => ''
            ],
        ];
    }
}