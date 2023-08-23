<?php
/**
 * ==================================================
 * Developer: Alexey Nazarov
 * E-mail: alsnazarov@1cbit.ru
 * Copyright (c) 2019 - 2022
 * ==================================================
 * mc-portal - CustomSectionInstaller.php
 * 17.01.2023 20:23
 * ==================================================
 */
namespace Cbit\Mc\Subscription\Internals\Installation;

use Bitrix\Crm\Integration\IntranetManager;
use Bitrix\Intranet\CustomSection\Entity\CustomSectionPageTable;
use Bitrix\Intranet\CustomSection\Entity\CustomSectionTable;
use Bitrix\Main\Orm\Data\AddResult;
use Bitrix\Main\Orm\Data\UpdateResult;
use Cbit\Mc\Subscription\Config\Configuration;
use Cbit\Mc\Subscription\Config\Constants;
use Cbit\Mc\Subscription\Service\Container;
use Exception;

/**
 * Class CustomSectionInstaller
 * @package Cbit\Mc\Subscription\Internals\Installation
 */
class CustomSectionInstaller
{
    private static array $pagesMap = [];

    /**
     * @return \Bitrix\Main\Orm\Data\UpdateResult | \Bitrix\Main\Orm\Data\AddResult
     * @throws \Exception
     */
    public static function installCustomSection(): UpdateResult|AddResult
    {
        if (!IntranetManager::isCustomSectionsAvailable())
        {
            throw new Exception('Intranet custom sections is unavailable');
        }

        $title           = Constants::DYNAMIC_TYPE_CUSTOM_SECTION_TITLE;
        $code            = Constants::DYNAMIC_TYPE_CUSTOM_SECTION_CODE;
        $existsSectionId = static::getCustomSectionId($code);

        if ((int)$existsSectionId > 0)
        {
            return CustomSectionTable::update($existsSectionId, [
                'TITLE' => $title
            ]);
        }
        else
        {
            return CustomSectionTable::add([
                'TITLE'     => $title,
                'CODE'      => $code,
                'MODULE_ID' => 'crm',
            ]);
        }
    }

    /**
     * @param string|null $code
     * @return int|null
     * @throws \Exception
     */
    protected static function getCustomSectionId(?string $code): ?int
    {
        if (!empty($code))
        {
            $existsSection = CustomSectionTable::query()
                ->setFilter([
                    'CODE' => $code,
                    'MODULE_ID' => 'crm'
                ])
                ->setSelect(['ID'])
                ->fetch();

            if (!empty($existsSection))
            {
                return (int)$existsSection['ID'];
            }
        }
        return null;
    }

    /**
     * @param int $entityTypeId
     * @param int $customSectionId
     * @throws \Exception
     */
    public static function installCustomPages(int $entityTypeId, int $customSectionId): void
    {
        static::$pagesMap = Configuration::getInstance()->getCustomPagesMap();

        $pagesSettings = [];
        foreach (static::$pagesMap as $pageCode => $pageData)
        {
            $pagesSettings[$pageCode] = $entityTypeId . '_' . $pageCode;
        }

        $pages = CustomSectionPageTable::query()
            ->setSelect(['ID', 'SETTINGS'])
            ->setFilter([
                '=SETTINGS'          => $pagesSettings,
                '=CUSTOM_SECTION_ID' => $customSectionId,
            ])
            ->fetchAll();

        $pagesToUpdate = [];

        if (!empty($pages))
        {
            foreach ($pages as $page)
            {
                $code = array_search($page['SETTINGS'], $pagesSettings);
                if (is_string($code) && !empty($code))
                {
                    $pagesToUpdate[$page['ID']] = $code;
                    unset($pagesSettings[$code]);
                }
            }
        }

        $pagesToAdd = $pagesSettings;

        static::addCustomPages($pagesToAdd, $customSectionId);
        static::updateCustomPages($pagesToUpdate);

        Container::getInstance()->getRouter()->reInit();
    }

    /**
     * @param array $pages
     * @param int $customSectionId
     * @throws \Exception
     */
    protected static function addCustomPages(array $pages, int $customSectionId): void
    {
        $sort = 0;
        foreach ($pages as $pageCode => $pageSettings)
        {
            $sort += 100;
            CustomSectionPageTable::add([
                'TITLE' => static::$pagesMap[$pageCode]['TITLE'],
                'MODULE_ID' => 'crm',
                'CUSTOM_SECTION_ID' => $customSectionId,
                'SETTINGS' => $pageSettings,
                'SORT' => $sort,
                'CODE' => ''
            ]);
        }
    }

    /**
     * @param array $pages
     * @throws \Exception
     */
    protected static function updateCustomPages(array $pages): void
    {
        foreach ($pages as $pageId => $pageCode)
        {
            CustomSectionPageTable::update($pageId, [
                'TITLE' => static::$pagesMap[$pageCode]['TITLE'],
            ]);
        }
    }
}