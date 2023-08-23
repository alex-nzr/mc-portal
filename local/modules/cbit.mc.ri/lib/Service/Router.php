<?php
/**
 * ==================================================
 * Developer: Alexey Nazarov
 * E-mail: alsnazarov@1cbit.ru
 * Copyright (c) 2019 - 2022
 * ==================================================
 * mc-portal - Router.php
 * 17.01.2023 14:46
 * ==================================================
 */
namespace Cbit\Mc\RI\Service;

use Bitrix\Crm\Service\Router\ParseResult;
use Bitrix\Intranet\CustomSection\Entity\CustomSectionPageTable;
use Bitrix\Intranet\CustomSection\Entity\CustomSectionTable;
use Bitrix\Main\Context;
use Bitrix\Main\HttpRequest;
use Bitrix\Main\SiteTable;
use Cbit\Mc\RI\Config\Constants;
use Cbit\Mc\RI\Entity;
use Cbit\Mc\RI\Internals\Control\ServiceManager;
use Cbit\Mc\RI\Service\Integration\Intranet\CustomSectionProvider;
use Exception;

/**
 * Class Router
 * @package Cbit\Mc\RI\Service
 */
class Router extends \Bitrix\Crm\Service\Router
{
    private ?bool $isDetailPage = null;
    private ?bool $isListPage = null;
    private int $entityTypeId;
    private bool $isDetailPageInCreationMode;

    /**
     * @throws \Exception
     */
    public function __construct()
    {
        $this->entityTypeId = Entity\Dynamic::getInstance()->getEntityTypeId();
        parent::__construct();
    }

    /**
     * @param \Bitrix\Main\HttpRequest|null $httpRequest
     * @return \Bitrix\Crm\Service\Router\ParseResult
     * @throws \Exception
     */
    public function parseRequest(HttpRequest $httpRequest = null): ParseResult
    {
        $result       = parent::parseRequest($httpRequest);
        $component    = $result->getComponentName();
        $parameters   = $result->getComponentParameters();
        $entityTypeId = $parameters['ENTITY_TYPE_ID'] ?? $parameters['entityTypeId'] ?? null;

        if ((int)$entityTypeId === $this->entityTypeId)
        {
            $newComponent = $component;
            switch ($component)
            {
                case 'bitrix:crm.item.list':
                    if ($this->isListPage())
                    {
                        $newComponent       = CustomSectionProvider::CUSTOM_LIST_COMPONENT;
                        $this->isListPage   = true;
                        $this->isDetailPage = false;
                    }
                    break;

                case 'bitrix:crm.item.details':
                    if ($this->isDetailPage())
                    {
                        //TODO add to the detail component after replacement
                        ServiceManager::getInstance()->addDetailPageUI();
                        //$newComponent = 'myNewDetailComponent';

                        $this->isListPage = false;
                        $this->isDetailPage = true;
                    }
                    break;
            }

            $result = new ParseResult( $newComponent, $parameters, $result->getTemplateName() );
        }

        return $result;
    }

    /**
     * @return string
     * @throws \Exception
     */
    public function getCustomSectionRoot(): string
    {
        return Constants::DYNAMIC_TYPE_CUSTOM_SECTION_CODE . "/";
    }

    /**
     * @return string
     * @throws \Exception
     */
    public function getItemListUrlInCustomSection(): string
    {
        return '/page/' . $this->getCustomSectionRoot() . $this->getCustomPageCode(Constants::CUSTOM_PAGE_LIST);
    }

    /**
     * @return string
     */
    public function getItemListUrlInCrmSection(): string
    {
        return '/crm/type/'.$this->entityTypeId.'/list';
    }

    /**
     * @return bool
     * @throws \Exception
     */
    public function isInDynamicTypeSection(): bool
    {
        $page    = $this->getCurPage();
        $needle1 = '/crm/type/' . $this->entityTypeId . '/';
        $needle2 = "/page/" . Constants::DYNAMIC_TYPE_CUSTOM_SECTION_CODE . "/";

        return ( (str_starts_with($page, $needle1)) || (str_starts_with($page, $needle2)) );
    }

    /**
     * @return bool
     * @throws \Exception
     */
    public function isDetailPage(): bool
    {
        if ($this->isDetailPage === null)
        {
            $page               = $this->getCurPage();
            $needle             = '/type/' . $this->entityTypeId . '/details/';
            $this->isDetailPage = (str_contains($page, $needle));
        }
        return $this->isDetailPage;
    }

    /**
     * @return bool
     * @throws \Exception
     */
    public function isDetailPageInCreationMode(): bool
    {
        if ($this->isDetailPage())
        {
            $page               = $this->getCurPage();
            $needle             = '/type/' . $this->entityTypeId . '/details/0/';
            $this->isDetailPageInCreationMode = (str_contains($page, $needle));
        }
        else
        {
            $this->isDetailPageInCreationMode = false;
        }

        return $this->isDetailPageInCreationMode;
    }

    /**
     * @return bool
     * @throws \Exception
     */
    public function isListPage(): bool
    {
        if ($this->isListPage === null)
        {
            $page    = $this->getCurPage();
            $needle1 = $this->getItemListUrlInCustomSection();
            $needle2 = $this->getItemListUrlInCrmSection();
            $this->isListPage = ( (str_starts_with($page, $needle1)) || (str_starts_with($page, $needle2)) );
        }
        return $this->isListPage;
    }

    /**
     * @return bool
     * @throws \Exception
     */
    public function isTeamProfilePage(): bool
    {
        $page   = $this->getCurPage();
        $needle = $this->getTeamProfilePageUrl();
        return str_starts_with($page, $needle);
    }

    /**
     * @return string
     */
    public function getCurPage(): string
    {
        return (string)Context::getCurrent()->getRequest()->getRequestedPage();
    }

    /**
     * @param int $id
     * @return string
     * @throws \Exception
     */
    public function getItemDetailUrlById(int $id): string
    {
        return $this->getItemDetailUrl( $this->entityTypeId, $id);
    }

    /**
     * @return string
     * @throws \Exception
     */
    public function getTeamProfilePageUrl():string
    {
        return '/page/' . $this->getCustomSectionRoot() . $this->getCustomPageCode(Constants::CUSTOM_PAGE_TEAM);
    }

    /**
     * @param string $settingsKey
     * @return string
     * @throws \Exception
     */
    public function getCustomPageCode(string $settingsKey): string
    {
        $existsPages = $this->getCustomSectionPages(Constants::DYNAMIC_TYPE_CUSTOM_SECTION_CODE);

        $pageCode = '';

        foreach ($existsPages as $existsPage)
        {
            if ($existsPage['SETTINGS'] === $this->entityTypeId . '_' . $settingsKey)
            {
                $pageCode = $existsPage['CODE'];
            }
        }

        return $pageCode;
    }

    /**
     * @param $sectionCode
     * @return array
     * @throws \Exception
     */
    public function getCustomSectionPages($sectionCode): array
    {
        $existsSection = CustomSectionTable::query()
            ->setFilter([
                'CODE'      => $sectionCode,
                'MODULE_ID' => 'crm'
            ])
            ->setSelect(['ID', 'TITLE'])
            ->fetch();

        if (!empty($existsSection))
        {
            return CustomSectionPageTable::query()
                ->setSelect(['ID', 'CODE', 'SETTINGS'])
                ->setFilter(['CUSTOM_SECTION_ID' => $existsSection['ID']])
                ->fetchAll();
        }
        return [];
    }

    /**
     * @param string $requestedPage
     * @return int|null
     * @throws \Exception
     */
    public function getEntityIdFromDetailUrl(string $requestedPage): ?int
    {
        $parts = explode( '/type/'.$this->entityTypeId.'/details/',$requestedPage);
        if (!empty($parts[1]))
        {
            $id = current(explode('/', $parts[1]));
            return is_numeric($id) ? (int)$id : null;
        }
        return null;
    }

    /**
     * @param int $itemId
     * @return string
     */
    public function getItemFullPath(int $itemId): string
    {
        try
        {
            $site = SiteTable::getByPrimary('s1')->fetch();
            $siteUrl = $site['SERVER_NAME'];
            if (empty($siteUrl) && is_array($_SERVER))
            {
                $siteUrl = ((!empty($_SERVER['HTTPS'])) ? 'https' : 'http') . '://' . $_SERVER['HTTP_HOST'];
            }
            return $siteUrl . $this->getItemDetailUrlById($itemId);
        }
        catch (Exception $e)
        {
            //log error
            return '';
        }
    }
}