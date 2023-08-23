<?php
/**
 * ==================================================
 * Developer: Alexey Nazarov
 * E-mail: jc1988x@gmail.com
 * Copyright (c) 2019 - 2023
 * ==================================================
 * mc-portal - BaseCustomSectionProvider.php
 * 17.03.2023 15:50
 * ==================================================
 */

namespace Cbit\Mc\Core\Service\Integration\Intranet;

use Bitrix\Crm\Integration\IntranetManager;
use Bitrix\Crm\Service\Container;
use Bitrix\Intranet\CustomSection\Provider;
use Bitrix\Intranet\CustomSection\Provider\Component;
use Bitrix\Main\Web\Uri;
use CCrmOwnerType;

/**
 * @class BaseCustomSectionProvider
 * @package Cbit\Mc\Core\Service\Integration\Intranet
 */
abstract class BaseCustomSectionProvider extends Provider
{
    const DEFAULT_LIST_COMPONENT = 'bitrix:crm.item.list';

    /**
     * @param string $pageSettings
     * @param int $userId
     * @return bool
     * @throws \Exception
     */
    public function isAvailable(string $pageSettings, int $userId): bool
    {
        $entityTypeId = $this->getEntityTypeIdByPageSettings($pageSettings);

        if (empty($entityTypeId) || !CCrmOwnerType::IsDefined($entityTypeId))
        {
            return false;
        }

        return $this->checkPermissionsByPageSettings($pageSettings, $userId, $entityTypeId);
    }

    /**
     * @param string $pageSettings
     * @param \Bitrix\Main\Web\Uri $url
     * @return \Bitrix\Intranet\CustomSection\Provider\Component|null
     */
    public function resolveComponent(string $pageSettings, Uri $url): ?Component
    {
        $entityTypeId = $this->getEntityTypeIdByPageSettings($pageSettings);

        if (is_null($entityTypeId) || !CCrmOwnerType::isPossibleDynamicTypeId($entityTypeId))
        {
            return null;
        }

        $customSections = IntranetManager::getCustomSections();
        if (is_null($customSections))
        {
            return null;
        }

        $router = Container::getInstance()->getRouter();
        $componentParameters = [];
        foreach ($customSections as $section)
        {
            foreach ($section->getPages() as $page)
            {
                $entityTypeId = $this->getEntityTypeIdByPageSettings($page->getSettings());

                if (($entityTypeId > 0) && ($page->getSettings() === $pageSettings))
                {
                    $url = IntranetManager::getUrlForCustomSectionPage($section->getCode(), $page->getCode());
                    $componentParameters = [
                        'root' => !is_null($url) ? $url->getPath() : null,
                    ];

                    $router->setDefaultComponent($this->getComponentByPageSettings($pageSettings));
                    $router->setDefaultComponentParameters([
                        'entityTypeId' => $entityTypeId,
                    ]);
                }
            }
        }

        return (new Component())
            ->setComponentTemplate('')
            ->setComponentName('bitrix:crm.router')
            ->setComponentParams($componentParameters);
    }

    /**
     * @param string $pageSettings
     * @return int|null
     */
    public function getEntityTypeIdByPageSettings(string $pageSettings): ?int
    {
        $set = explode("_", $pageSettings);

        if (is_numeric($set[0]))
        {
            $entityTypeId = (int)$set[0];
        }
        else
        {
            $entityTypeId = IntranetManager::getEntityTypeIdByPageSettings($pageSettings);
        }

        return $entityTypeId;
    }

    abstract public function getComponentByPageSettings(string $pageSettings): string;
    abstract public function checkPermissionsByPageSettings(string $pageSettings, int $userId, int $entityTypeId): bool;
}