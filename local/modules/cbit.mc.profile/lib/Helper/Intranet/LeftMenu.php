<?php
/**
 * ==================================================
 * Developer: Alexey Nazarov
 * E-mail: alsnazarov@1cbit.ru
 * Copyright (c) 2019 - 2022
 * ==================================================
 * mc-portal - LeftMenu.php
 * 01.11.2022 17:48
 * ==================================================
 */


namespace Cbit\Mc\Profile\Helper\Intranet;

use Cbit\Mc\Profile\Internals\Debug\Logger;
use CUserOptions;

/**
 * Class LeftMenu
 * @package Cbit\Mc\Profile\Helper\Intranet
 */
class LeftMenu
{
    /**
     * @param array $items
     * @return array[]
     */
    public static function convertItemsSortFromJSToDB(array $items): array
    {
        $userOption = ['show' => [], 'hide' => []];
        foreach ($userOption as $key => $val)
        {
            if (isset($items[$key]) && is_array($items[$key]))
            {
                $userOption[$key] = $items[$key];
            }
        }
        return $userOption;
    }

    /**
     * @param $siteId
     * @return string
     */
    public static function getItemsSortOptionName($siteId):string
    {
        return 'left_menu_sorted_items_' . $siteId;
    }

    /**
     * @param $siteId
     * @return string
     */
    public static function getFirstPageOptionName($siteId):string
    {
        return 'left_menu_first_page_' . $siteId;
    }

    /**
     * @return void
     */
    public static function setPortalFirstPage(): void
    {
        CUserOptions::SetOption(
            'intranet',
            static::getFirstPageOptionName('s1'),
            '/profile/',
            true
        );
    }

    /**
     * @return void
     */
    public static function setMenuItemsSort(): void
    {
        $items = [
            'show' => ['menu_profile_sect'],
            'hide' => [
                'menu_configs_sect',
                [
                    'group_id' => 'menu_marketplace_group',
                    'items' => ['menu_devops_sect']
                ],
                'menu_devops_sect'
            ]
        ];

        CUserOptions::SetOption(
            'intranet',
            static::getItemsSortOptionName('s1'),
            static::convertItemsSortFromJSToDB($items),
            true
        );
    }

    /**
     * @return void
     */
    public static function setMenuForAllUsers(): void
    {
        static::setPortalFirstPage();
        static::setMenuItemsSort();
    }
}