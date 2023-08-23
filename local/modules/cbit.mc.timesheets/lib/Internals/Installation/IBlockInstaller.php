<?php
/**
 * ==================================================
 * Developer: Alexey Nazarov
 * E-mail: alsnazarov@1cbit.ru
 * Copyright (c) 2019 - 2022
 * ==================================================
 * mc-portal - IBlockInstaller.php
 * 13.12.2022 15:13
 * ==================================================
 */
namespace Cbit\Mc\Timesheets\Internals\Installation;

use Bitrix\Main\Result;
use Cbit\Mc\Core\Config\Constants as CoreConstants;
use Cbit\Mc\Core\Helper\Iblock\Iblock;

/**
 * Class IBlockInstaller
 * @package Cbit\Mc\Timesheets\Internals\Installation
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
            case CoreConstants::ACTIVITIES_IBLOCK_CODE:
                $props = [
                    [
                        "code"      => "ALLOW_STAFFING",
                        "name"      => "Allow staffing",
                        "type"      => "L",
                        "list_type" => 'C',
                        'list_items'=> ['Y', 'N']
                    ],
                    [
                        "code"      => "ALLOW_RESTAFFING",
                        "name"      => "Allow restaffing",
                        "type"      => "L",
                        "list_type" => 'C',
                        'list_items'=> ['Y', 'N']
                    ],
                ];
                break;
            case CoreConstants::INDUSTRIES_IBLOCK_CODE:
                $props = [
                    [
                        "code" => "INDUSTRY_COLOR",
                        "name" => "Color",
                        "type" => "S"
                    ],
                ];
                break;
            case CoreConstants::FUNCTIONS_IBLOCK_CODE:
                $props = [
                    [
                        "code" => "FUNCTION_COLOR",
                        "name" => "Color",
                        "type" => "S"
                    ],
                ];
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
                'NAME' => 'Activities registry',
                'CODE' => CoreConstants::ACTIVITIES_IBLOCK_CODE,
                'DESC' => ''
            ],
            [
                'NAME' => 'Industries registry',
                'CODE' => CoreConstants::INDUSTRIES_IBLOCK_CODE,
                'DESC' => ''
            ],
            [
                'NAME' => 'Functions registry',
                'CODE' => CoreConstants::FUNCTIONS_IBLOCK_CODE,
                'DESC' => ''
            ],
            [
                'NAME' => 'Team compositions registry',
                'CODE' => CoreConstants::TEAM_COMP_IBLOCK_CODE,
                'DESC' => ''
            ],
            [
                'NAME' => 'Project phases registry',
                'CODE' => CoreConstants::PROJECT_PHASES_IBLOCK_CODE,
                'DESC' => ''
            ],
            [
                'NAME' => 'Project states registry',
                'CODE' => CoreConstants::PROJECT_STATES_IBLOCK_CODE,
                'DESC' => ''
            ],
        ];
    }
}