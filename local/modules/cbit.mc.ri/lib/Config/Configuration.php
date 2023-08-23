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
namespace Cbit\Mc\RI\Config;

use Bitrix\Main\Config\Option;
use Bitrix\Main\Localization\Loc;
use Bitrix\Main\UserTable;
use Cbit\Mc\Core\Helper\Main\User;
use Cbit\Mc\Core\Service\Integration\Zup\Fields;
use Cbit\Mc\RI\Entity\Dynamic;
use Cbit\Mc\RI\Internals\Control\ServiceManager;
use Cbit\Mc\RI\Service\Access\Permission;
use Cbit\Mc\RI\Service\Container;
use Cbit\Mc\RI\Service\Integration\Intranet\CustomSectionProvider;

/**
 * Class Configuration
 * @package Cbit\Mc\RI\Config
 */
class Configuration
{
    private static ?Configuration $instance = null;

    private function __construct(){}

    /**
     * @return \Cbit\Mc\RI\Config\Configuration
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
     * @return \string[][]
     */
    public function getCustomPagesMap(): array
    {
        return [
            Constants::CUSTOM_PAGE_LIST => [
                'TITLE'     => Loc::getMessage(ServiceManager::getModuleId() . '_CONFIG_LIST_PAGE_TITLE'),
                'COMPONENT' => CustomSectionProvider::CUSTOM_LIST_COMPONENT,
            ],
            Constants::CUSTOM_PAGE_TEAM => [
                'TITLE'     => Loc::getMessage(ServiceManager::getModuleId() . '_CONFIG_TEAM_PAGE_TITLE'),
                'COMPONENT' => 'cbit:mc.ri.team.profile',
            ],
            Constants::CUSTOM_PAGE_OUTSOURCING => [
                'TITLE'     => Loc::getMessage(ServiceManager::getModuleId() . '_CONFIG_REQUEST_PAGE_TITLE'),
                'COMPONENT' => 'cbit:mc.ri.outsource.list',
            ],
        ];
    }

    /**
     * @return int
     */
    public function getStaffingTypeId(): int
    {
        return (int)Option::get(Constants::STAFFING_MODULE_ID, Constants::OPTION_KEY_STAFFING_TYPE_ID);
    }

    /**
     * @return int|null
     * @throws \Exception
     */
    public function getStaffingEntityTypeId(): ?int
    {
        $typeId = (int)Option::get(
            Constants::STAFFING_MODULE_ID, Constants::OPTION_KEY_STAFFING_TYPE_ID
        );
        if (!empty($typeId))
        {
            /** @var  \Bitrix\Crm\Model\Dynamic\Type|null $typeObject */
            $typeObject = Container::getInstance()->getDynamicTypeDataClass()::getByPrimary($typeId)->fetchObject();
            if (!empty($typeObject))
            {
                return $typeObject->getEntityTypeId();
            }
        }
        return null;
    }

    /**
     * @return array
     * @throws \Exception
     */
    public function getCurrentCoordinatorData(): array
    {
        $coordinatorData = [];
        $currentCoordinatorId = $this->getCurrentCoordinatorId();
        if (!empty($currentCoordinatorId))
        {

            $data = UserTable::query()
                ->where('ID', '=', $currentCoordinatorId)
                ->setSelect([Fields::getFioEnUfCode(), 'NAME', 'LAST_NAME', 'PERSONAL_PHOTO', 'EMAIL', 'PERSONAL_MOBILE'])
                ->fetch();

            if (is_array($data))
            {
                $coordinatorData['ID']             = $currentCoordinatorId;
                $coordinatorData['PERSONAL_PHONE'] = $data['PERSONAL_MOBILE'] ?? '';
                $coordinatorData['EMAIL']          = $data['EMAIL'] ?? '';
                $coordinatorData['NAME']           = \Cbit\Mc\RI\Helper\Main\User::getUserNameByFields($data);
                $coordinatorData['LINK']           = User::getUserProfileLink($currentCoordinatorId);
                $coordinatorData['PHOTO']          = User::getResizedAvatarByFileId((int)$data['PERSONAL_PHOTO']);
            }
        }
        return $coordinatorData;
    }

    /**
     * @return int
     */
    public function getCurrentCoordinatorId(): int
    {
        return (int)Option::get(ServiceManager::getModuleId(), Constants::OPTION_KEY_COORDINATOR_ID);
    }

    /**
     * @return string
     */
    public function getCurrentTeamDescription(): string
    {
        return Option::get(ServiceManager::getModuleId(), Constants::OPTION_KEY_TEAM_DESCRIPTION, '');
    }

    /**
     * @return string
     */
    public function getCurrentTeamWorkTime(): string
    {
        return Option::get(ServiceManager::getModuleId(), Constants::OPTION_KEY_TEAM_WORK_TIME, '');
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