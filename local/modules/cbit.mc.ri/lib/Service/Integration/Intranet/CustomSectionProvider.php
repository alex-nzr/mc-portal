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
namespace Cbit\Mc\RI\Service\Integration\Intranet;

use Bitrix\Crm\Integration\Intranet\CustomSectionProvider as IntranetCustomSectionProvider;
use Bitrix\Crm\Integration\IntranetManager;
use Bitrix\Intranet\CustomSection\Provider\Component;
use Bitrix\Main\Web\Uri;
use Cbit\Mc\Core\Service\Integration\Intranet\BaseCustomSectionProvider;
use Cbit\Mc\RI\Config\Configuration;
use Cbit\Mc\RI\Config\Constants;
use Cbit\Mc\RI\Service\Container;
use CCrmOwnerType;

/**
 * Class CustomSectionProvider
 * @package Cbit\Mc\RI\Service\Integration\Intranet
 */
class CustomSectionProvider extends BaseCustomSectionProvider
{
    const DEFAULT_LIST_COMPONENT = 'bitrix:crm.item.list';
    const CUSTOM_LIST_COMPONENT  = 'cbit:mc.ri.item.list';

    /**
     * @param string $pageSettings
     * @return string
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
        $set = explode("_", $pageSettings);
        if (is_array($set) && $set[1] === Constants::CUSTOM_PAGE_OUTSOURCING)
        {
            return false;
        }

        return Container::getInstance()->getUserPermissions($userId)->checkReadPermissions($entityTypeId);
    }
}
