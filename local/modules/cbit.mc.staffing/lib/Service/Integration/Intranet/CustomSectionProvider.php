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
namespace Cbit\Mc\Staffing\Service\Integration\Intranet;

use Cbit\Mc\Core\Service\Integration\Intranet\BaseCustomSectionProvider;
use Cbit\Mc\Staffing\Config\Configuration;
use Cbit\Mc\Staffing\Service\Container;

/**
 * Class CustomSectionProvider
 * @package Cbit\Mc\Staffing\Service\Integration\Intranet
 */
class CustomSectionProvider extends BaseCustomSectionProvider
{
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
            return 'bitrix:crm.item.list';
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
        $perms = Container::getInstance()->getUserPermissions($userId);

        return $perms->hasPdStaffingPermissions();
    }
}
