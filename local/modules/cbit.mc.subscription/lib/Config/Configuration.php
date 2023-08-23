<?php
/**
 * ==================================================
 * Developer: Alexey Nazarov
 * E-mail: alsnazarov@1cbit.ru
 * Copyright (c) 2019 - 2022
 * ==================================================
 * mc-portal - Configuration.php
 * 17.01.2023 12:00
 * ==================================================
 */
namespace Cbit\Mc\Subscription\Config;

use Bitrix\Main\Config\Option;
use Bitrix\Main\Loader;
use Bitrix\Main\Localization\Loc;
use Cbit\Mc\Subscription\Internals\Control\ServiceManager;
use Cbit\Mc\Subscription\Service\Integration\Intranet\CustomSectionProvider;

/**
 * Class Configuration
 * @package Cbit\Mc\Subscription\Config
 */
class Configuration
{
    private static ?Configuration $instance = null;

    private function __construct(){}

    /**
     * @return \Cbit\Mc\Subscription\Config\Configuration
     */
    public static function getInstance(): Configuration
    {
        if (static::$instance === null)
        {
            static::$instance = new static();
        }
        return static::$instance;
    }

    /**
     * @return string
     */
    public function getLogFilePath(): string
    {
        return '/local/logs/'.ServiceManager::getModuleId().'-log.txt';
    }

    /**
     * @return array[]
     * @throws \Exception
     */
    public function getCustomPagesMap(): array
    {
        $map = [
            Constants::CUSTOM_PAGE_LIST => [
                'TITLE'     => 'R&I Subscriptions',
                'COMPONENT' => CustomSectionProvider::DEFAULT_LIST_COMPONENT,
            ],
        ];

        if (!ServiceManager::isModuleInstallingNow() && Loader::includeModule(Constants::RI_MODULE_ID))
        {
            $map[\Cbit\Mc\RI\Config\Constants::CUSTOM_PAGE_TEAM] = [
                'TITLE'     => Loc::getMessage(Constants::RI_MODULE_ID.'_CONFIG_TEAM_PAGE_TITLE'),
                'COMPONENT' => 'cbit:mc.ri.team.profile',
            ];
        }
        return $map;
    }

    /**
     * @return int
     */
    public function getTypeIdFromOption(): int
    {
        return (int)Option::get(
            ServiceManager::getModuleId(), Constants::OPTION_KEY_DYNAMIC_TYPE_ID
        );
    }

    private function __clone(){}
    public function __wakeup(){}
}