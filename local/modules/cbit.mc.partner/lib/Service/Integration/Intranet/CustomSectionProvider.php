<?php
/**
 * ==================================================
 * Developer: Alexey Nazarov
 * E-mail: alsnazarov@1cbit.ru
 * Copyright (c) 2019 - 2022
 * ==================================================
 * mc-portal - CustomSectionProvider.php
 * 17.01.2023 14:46
 * ==================================================
 */
namespace Cbit\Mc\Partner\Service\Integration\Intranet;

use Bitrix\Main\Loader;
use Cbit\Mc\Core\Service\Integration\Intranet\BaseCustomSectionProvider;
use Cbit\Mc\Partner\Config\Configuration;
use Cbit\Mc\Partner\Config\Constants;
use Cbit\Mc\Partner\Service\Container;
use Cbit\Mc\RI\Config\Constants as RiConstants;

/**
 * Class CustomSectionProvider
 * @package Cbit\Mc\Partner\Service\Integration\Intranet
 */
class CustomSectionProvider extends BaseCustomSectionProvider
{
    /**
     * @param string $pageSettings
     * @return string
     * @throws \Exception
     */
    public function getComponentByPageSettings(string $pageSettings): string
    {
        $customPagesMap = Configuration::getInstance()->getCustomPagesMap();
        $set = explode("_", $pageSettings);

        if (array_key_exists($set[1], $customPagesMap))
        {
            return $customPagesMap[$set[1]]['COMPONENT'];
        }
        else
        {
            return static::DEFAULT_LIST_COMPONENT;
        }
    }

    /**
     * @param string $pageSettings
     * @param int $userId
     * @param int $entityTypeId
     * @return bool
     * @throws \Exception
     */
    public function checkPermissionsByPageSettings(string $pageSettings, int $userId, int $entityTypeId): bool
    {
        if (Loader::includeModule(Constants::RI_MODULE_ID))
        {
            $set = explode("_", $pageSettings);
            if (is_array($set) && $set[1] === RiConstants::CUSTOM_PAGE_OUTSOURCING)
            {
                return false;
            }
        }
        return Container::getInstance()->getUserPermissions($userId)->checkReadPermissions($entityTypeId);
    }
}
