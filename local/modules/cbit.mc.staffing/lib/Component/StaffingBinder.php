<?php
/**
 * ==================================================
 * Developer: Alexey Nazarov
 * E-mail: alsnazarov@1cbit.ru
 * Copyright (c) 2019 - 2023
 * ==================================================
 * mc-portal - StaffingBinder.php
 * 01.02.2023 16:43
 * ==================================================
 */

namespace Cbit\Mc\Staffing\Component;

use Bitrix\Main\Localization\Loc;
use Bitrix\Main\UI\Filter\Options;
use Bitrix\Main\UserTable;
use Cbit\Mc\Core\Component\BaseComponent;
use Cbit\Mc\Core\Config\Constants as CoreConstants;
use Cbit\Mc\Core\Helper\Iblock\IblockElement;
use Cbit\Mc\Core\Helper\Main\User;
use Cbit\Mc\Core\Helper\Main\UserField;
use Cbit\Mc\Core\Service\Integration\Zup\Fields;
use Cbit\Mc\Staffing\Config\Configuration;
use Cbit\Mc\Staffing\Config\Constants;
use Cbit\Mc\Staffing\Entity\Dynamic;
use Cbit\Mc\Staffing\Internals\Control\ServiceManager;
use Cbit\Mc\Staffing\Service\Container;
use Exception;

class StaffingBinder extends BaseComponent
{
    public string $moduleId;
    public int    $pageLimit = 10;
    public int    $totalUsers = 0;
    public int    $totalProjects    = 0;
    public string $usersFilterId    = 'staffing-binder-users-table-body';
    public string $projectsFilterId = 'staffing-binder-projects-table-body';

    const PROJECT_FIELD_ID         = 'ID';
    const PROJECT_FIELD_CC         = 'CC';
    const PROJECT_FIELD_EMP_TYPE   = 'Type';
    const PROJECT_FIELD_TITLE_LINK = 'Title';
    const PROJECT_FIELD_TITLE_TEXT = 'TitleText';
    const PROJECT_FIELD_ED         = 'ED';
    const PROJECT_FIELD_INDUSTRY   = 'Industry';
    const PROJECT_FIELD_STATUS     = 'Status';
    const PROJECT_FIELD_CLIENT     = 'Client';
    const PROJECT_FIELD_TOPIC      = 'Topic';
    const PROJECT_FIELD_START_DATE = 'Start date';
    const PROJECT_FIELD_END_DATE   = 'End date';
    const PROJECT_FIELD_DURATION   = 'Duration';
    const PROJECT_FIELD_TEAM       = 'Team';
    const PROJECT_FIELD_LOCATION   = 'Location';

    const USER_FIELD_ID            = 'ID';
    const USER_FIELD_NAME_LINK     = 'Name';
    const USER_FIELD_NAME_TEXT     = 'NameText';
    const USER_FIELD_FMNO          = 'FMNO';
    const USER_FIELD_AVAILABLE     = 'Availability';
    const USER_FIELD_STAFFING_MGR  = 'Staffing manager';
    const USER_FIELD_AVL_FOR_BEACH = 'Avl for beach';

    public function __construct($component = null)
    {
        $this->moduleId = ServiceManager::getModuleId();
        parent::__construct($component);
    }

    /**
     * @return array
     * @throws \Exception
     */
    public function getResult(): array
    {
        if ($this->excelMode)
        {
            $this->setTemplateName('excel');
            if ($this->request->get('EXPORT_USERS') === 'Y')
            {
                return [
                    'FILE_NAME' => 'users',
                    'ITEMS'     => $this->getUsersForExcel(),
                    'HEADERS'   => $this->getUserDisplayFields(),
                ];
            }
            elseif ($this->request->get('EXPORT_PROJECTS') === 'Y')
            {
                return [
                    'FILE_NAME' => 'projects',
                    'ITEMS'     => $this->getProjectsForExcel(),
                    'HEADERS'   => $this->getProjectDisplayFields(),
                ];
            }
        }

        return [
            'USERS'                     => [],
            'USERS_FILTER'              => $this->getUsersFilter(),
            'USERS_FILTER_ID'           => $this->usersFilterId,
            'USER_DISPLAY_FIELDS'       => $this->getUserDisplayFields(),
            'PROJECTS'                  => [],
            'PROJECTS_FILTER'           => $this->getProjectsFilter(),
            'PROJECTS_FILTER_ID'        => $this->projectsFilterId,
            'PROJECT_DISPLAY_FIELDS'    => $this->getProjectDisplayFields(),
            'STAFFING_EMPLOYMENT_TYPES' => $this->getStaffingEmploymentTypes(),
            'STAFFING_USER_ROLES'       => $this->getStaffingUserRoles(),
            'PER_DIEM_EDIT_REASONS'     => $this->getPerDiemEditReasons(),
        ];
    }

    /**
     * @param int $limit
     * @param array $filter
     * @return array
     * @throws \Bitrix\Main\ArgumentException
     * @throws \Bitrix\Main\ObjectPropertyException
     * @throws \Bitrix\Main\SystemException
     */
    public function getUsersData(int $limit = 0, array $filter = []): array
    {
        $result = [];
        $query = UserTable::query()
            ->setSelect([
                'ID',
                Fields::getFmnoUfCode(),
                Fields::getFioEnUfCode(),
                'UF_STAFFING_MANAGER',
                'UF_USER_AVAILABLE',
                'NAME', 'LAST_NAME'
            ])
            ->setFilter($filter)
            ->setOrder(['ID' => 'ASC'])
            ->setLimit(($limit > 0) ? $limit : null)
            ->countTotal(true);

        if (!empty(Fields::getCspOspUfCode()))
        {
            $query->where(Fields::getCspOspUfCode(), CoreConstants::USER_EMPLOYMENT_TYPE_CSP);
        }

        $queryResult = $query->exec();

        $this->totalUsers = $queryResult->getCount();

        while($elem = $queryResult->fetch())
        {
            $availability = UserField::getUfListValueById($elem['UF_USER_AVAILABLE']);
            $user = [
                static::USER_FIELD_ID        => $elem['ID'],
                static::USER_FIELD_NAME_LINK => User::getProfileViewLink($elem['ID'], $elem),
                static::USER_FIELD_NAME_TEXT => User::getUserNameByFields($elem),
                static::USER_FIELD_FMNO      => $elem[Fields::getFmnoUfCode()],
                static::USER_FIELD_AVAILABLE => $availability,
            ];

            $user[static::USER_FIELD_STAFFING_MGR] = !empty($elem['UF_STAFFING_MANAGER'])
                ? User::getProfileViewLink($elem['UF_STAFFING_MANAGER']) : '';

            $user[static::USER_FIELD_AVL_FOR_BEACH] = (
                $availability === CoreConstants::USER_AVAILABILITY_STATUS_FREE
                || $availability === CoreConstants::USER_AVAILABILITY_STATUS_BEACH
            ) ? 'Y' : 'N';

            $result[] = $user;
        }
        return $result;
    }

    /**
     * @param int $limit
     * @param array $filter
     * @return array
     * @throws \Exception
     */
    public function getProjectsData(int $limit = 0, array $filter = []): array
    {
        $dataClass    = Dynamic::getInstance()->getDataClass();
        $typeId       = Dynamic::getInstance()->getTypeId();
        $entityTypeId = Dynamic::getInstance()->getEntityTypeId();

        $query = $dataClass::query()
            ->setSelect(['ID', 'TITLE', 'UF_*'])
            ->setFilter($filter)
            ->where("UF_CRM_" . $typeId . "_ALLOW_STAFFING", true)
            ->setOrder(['ID' => 'ASC'])
            ->setLimit(($limit > 0) ? $limit : null)
            ->countTotal(true)
            ->exec();

        $this->totalProjects = $query->getCount();

        $result = [];
        while($item = $query->fetch())
        {
            $detailUrl = Container::getInstance()->getRouter()->getItemDetailUrl($entityTypeId, $item['ID']);

            $result[] = [
                static::PROJECT_FIELD_ID         => $item['ID'],
                static::PROJECT_FIELD_TITLE_TEXT => $item['TITLE'],
                static::PROJECT_FIELD_CC         => '<a href="'.$detailUrl.'">'.$item["UF_CRM_".$typeId."_CHARGE_CODE"].'</a>',
                static::PROJECT_FIELD_EMP_TYPE   => UserField::getUfListValueById($item["UF_CRM_".$typeId."_EMPLOYMENT_TYPE"]),
                static::PROJECT_FIELD_TITLE_LINK => '<a href="'.$detailUrl.'">'.$item['TITLE'].'</a>',
                static::PROJECT_FIELD_ED         => !empty($item["UF_CRM_".$typeId."_ED"])
                    ? User::getProfileViewLink($item["UF_CRM_".$typeId."_ED"]): '',
                static::PROJECT_FIELD_INDUSTRY   => IblockElement::getElementById($item["UF_CRM_".$typeId."_INDUSTRY"])['NAME'],
                static::PROJECT_FIELD_STATUS     => IblockElement::getElementById($item["UF_CRM_".$typeId."_STATE"])['NAME'],
                static::PROJECT_FIELD_CLIENT     => $item["UF_CRM_".$typeId."_CLIENT"],
                static::PROJECT_FIELD_TOPIC      => $item["UF_CRM_".$typeId."_DESCRIPTION"],
                static::PROJECT_FIELD_START_DATE => !empty($item["UF_CRM_".$typeId."_START_DATE"])
                    ? $item["UF_CRM_".$typeId."_START_DATE"]->format('d.m.Y') : '',
                static::PROJECT_FIELD_END_DATE  => !empty($item["UF_CRM_".$typeId."_END_DATE"])
                    ? $item["UF_CRM_".$typeId."_END_DATE"]->format('d.m.Y') : '',
                static::PROJECT_FIELD_DURATION   => $item["UF_CRM_".$typeId."_DURATION"],
                static::PROJECT_FIELD_TEAM       => $item["UF_CRM_".$typeId."_TEAM"],
                static::PROJECT_FIELD_LOCATION   => $item["UF_CRM_".$typeId."_LOCATION"],
            ];
        }
        return $result;
    }

    /**
     * @return array
     */
    public function getUsersFilter(): array
    {
        return [
            ['id' => 'NAME', 'name' => 'Name', 'type' => 'text', 'default' => true],
            ['id' => 'LAST_NAME', 'name' => 'Last name', 'type' => 'text', 'default' => true],
            ['id' => Fields::getFmnoUfCode(), 'name' => 'FMNO', 'type' => 'text', 'default' => true],
            ['id' => Fields::getFioEnUfCode(), 'name' => 'Name EN', 'type' => 'text', 'default' => true],
            [
                'id' => 'UF_STAFFING_MANAGER',
                'name' => 'Staffing manager',
                'type' => 'dest_selector',
                'params' => [
                    'multiple' => 'Y',
                    'context' => 'USER',
                    'contextCode' => 'U',
                    'enableAll' => 'N',
                    'enableUsers' => "Y",
                    //'enableUserManager' => "Y",
                    'userSearchArea' => 'I',
                    "departmentSelectDisable" => "Y",
                    'enableDepartments' => 'N',
                    'departmentFlatEnable' => 'N',
                    'default' => true
                ],
            ],
            [
                'id' => 'UF_USER_AVAILABLE',
                'name' => 'Available',
                'type' => 'list',
                'items' => UserField::getUfListValuesByCode('UF_USER_AVAILABLE'),
                'params' => ['multiple' => 'Y'],
                'default' => true
            ],
        ];
    }

    /**
     * @return array[]
     * @throws \Exception
     */
    public function getProjectsFilter(): array
    {
        $typeId = Dynamic::getInstance()->getTypeId();
        return [
            [
                'id' => 'TITLE',
                'name' => static::PROJECT_FIELD_TITLE_LINK,
                'type' => 'text',
                'default' => true
            ],
            [
                'id' => 'UF_CRM_'.$typeId.'_CHARGE_CODE',
                'name' => static::PROJECT_FIELD_CC,
                'type' => 'text',
                'default' => true
            ],
            [
                'id' => 'UF_CRM_'.$typeId.'_CLIENT',
                'name' => static::PROJECT_FIELD_CLIENT,
                'type' => 'text',
                'default' => true
            ],
            [
                'id' => 'UF_CRM_'.$typeId.'_DESCRIPTION',
                'name' => static::PROJECT_FIELD_TOPIC,
                'type' => 'text',
                'default' => true
            ],

            [
                'id' => 'UF_CRM_'.$typeId.'_EMPLOYMENT_TYPE',
                'name' => static::PROJECT_FIELD_EMP_TYPE,
                'type' => 'list',
                'items' => UserField::getUfListValuesByCode('UF_CRM_'.$typeId.'_EMPLOYMENT_TYPE'),
                'params' => ['multiple' => 'Y'],
                'default' => true
            ],

            [
                'id' => 'UF_CRM_'.$typeId.'_ED',
                'name' => static::PROJECT_FIELD_ED,
                'type' => 'dest_selector',
                'params' => [
                    'multiple' => 'Y',
                    'context' => 'USER',
                    'contextCode' => 'U',
                    'enableAll' => 'N',
                    'enableUsers' => "Y",
                    //'enableUserManager' => "Y",
                    'userSearchArea' => 'I',
                    "departmentSelectDisable" => "Y",
                    'enableDepartments' => 'N',
                    'departmentFlatEnable' => 'N',
                    'default' => true
                ],
            ],

            [
                'id' => 'UF_CRM_'.$typeId.'_STATE',
                'name' => static::PROJECT_FIELD_STATUS,
                'type' => 'list',
                'items' => IblockElement::getElementsListToFilter(Configuration::getInstance()->getProjectStatesIBlockId()),
                'params' => ['multiple' => 'Y'],
                'default' => true
            ],

            [
                'id' => 'UF_CRM_'.$typeId.'_INDUSTRY',
                'name' => static::PROJECT_FIELD_INDUSTRY,
                'type' => 'list',
                'items' => IblockElement::getElementsListToFilter(Configuration::getInstance()->getProjectIndustriesIblockId()),
                'params' => ['multiple' => 'Y'],
                'default' => true
            ],

            ['id' => 'UF_CRM_'.$typeId.'_START_DATE', 'name' => static::PROJECT_FIELD_START_DATE, 'type' => 'date', 'default' => true],
            ['id' => 'UF_CRM_'.$typeId.'_END_DATE', 'name' => static::PROJECT_FIELD_END_DATE, 'type' => 'date', 'default' => true],
            ['id' => 'UF_CRM_'.$typeId.'_DURATION', 'name' => static::PROJECT_FIELD_DURATION, 'type' => 'text', 'default' => true],
            ['id' => 'UF_CRM_'.$typeId.'_TEAM', 'name' => static::PROJECT_FIELD_TEAM, 'type' => 'text', 'default' => true],
            ['id' => 'UF_CRM_'.$typeId.'_LOCATION', 'name' => static::PROJECT_FIELD_LOCATION, 'type' => 'text', 'default' => true],
        ];
    }

    /**
     * @return int
     */
    public function getTotalUsers(): int
    {
        return $this->totalUsers;
    }

    /**
     * @return int
     */
    public function getTotalProjects(): int
    {
        return $this->totalProjects;
    }

    /**
     * @return array
     * @throws \Exception
     */
    public function getProjectFilterValues(): array
    {
        $filterOption = new Options($this->projectsFilterId);
        $filterData   = $filterOption->getFilter();

        $typeId = Dynamic::getInstance()->getTypeId();
        $preparedFilter = [];
        foreach ($filterData as $key => $value)
        {
            if (!empty($value))
            {
                switch ($key)
                {
                    case 'TITLE':
                    case 'UF_CRM_'.$typeId.'_DURATION':
                    case 'UF_CRM_'.$typeId.'_LOCATION':
                    case 'UF_CRM_'.$typeId.'_CHARGE_CODE':
                    case 'UF_CRM_'.$typeId.'_CLIENT':
                        $preparedFilter["%".$key] = $value;
                        break;
                    case 'UF_CRM_'.$typeId.'_ED':
                        $preparedFilter[$key] = [];
                        if (is_array($value))
                        {
                            foreach ($value as $item)
                            {
                                if (is_string($item))
                                {
                                    $preparedFilter[$key][] = str_replace('U', '', $item);
                                }
                            }
                        }
                        break;
                    case 'UF_CRM_'.$typeId.'_STATE':
                    case 'UF_CRM_'.$typeId.'_INDUSTRY':
                    case 'UF_CRM_'.$typeId.'_TEAM_COMPOSITION':
                    case 'UF_CRM_'.$typeId.'_EMPLOYMENT_TYPE':
                        $preparedFilter[$key] = $value;
                        break;
                    case 'UF_CRM_'.$typeId.'_END_DATE_from':
                    case 'UF_CRM_'.$typeId.'_START_DATE_from':
                        $preparedFilter['>='.str_replace('_from', '', $key)] = $value;
                        break;
                    case 'UF_CRM_'.$typeId.'_END_DATE_to':
                    case 'UF_CRM_'.$typeId.'_START_DATE_to':
                        $preparedFilter['<='.str_replace('_to', '', $key)] = $value;
                        break;
                    default:
                        break;
                }
            }
        }

        return $preparedFilter;
    }

    /**
     * @return array
     */
    public function getUserFilterValues(): array
    {
        $filterOption = new Options($this->usersFilterId);
        $filterData   = $filterOption->getFilter();

        $preparedFilter = [];
        foreach ($filterData as $key => $value)
        {
            if (!empty($value))
            {
                switch ($key)
                {
                    case "LAST_NAME":
                    case "NAME":
                    case Fields::getFmnoUfCode():
                    case Fields::getFioEnUfCode():
                        $preparedFilter["%".$key] = $value;
                        break;
                    case 'UF_STAFFING_MANAGER':
                        $preparedFilter[$key] = [];
                        if (is_array($value))
                        {
                            foreach ($value as $item)
                            {
                                if (is_string($item))
                                {
                                    $preparedFilter[$key][] = str_replace('U', '', $item);
                                }
                            }
                        }
                        break;
                    case 'UF_USER_AVAILABLE':
                        $preparedFilter[$key] = $value;
                        break;
                    default:
                        break;
                }
            }
        }

        return $preparedFilter;
    }

    /**
     * @return string[]
     */
    public function getProjectDisplayFields(): array
    {
        return [
            static::PROJECT_FIELD_CC,
            static::PROJECT_FIELD_EMP_TYPE,
            static::PROJECT_FIELD_ED,
            static::PROJECT_FIELD_INDUSTRY,
            static::PROJECT_FIELD_STATUS,
            static::PROJECT_FIELD_CLIENT,
            static::PROJECT_FIELD_TOPIC,
            static::PROJECT_FIELD_START_DATE,
            static::PROJECT_FIELD_END_DATE,
            //static::PROJECT_FIELD_DURATION,
            //static::PROJECT_FIELD_TEAM,
            //static::PROJECT_FIELD_LOCATION,
            //static::PROJECT_FIELD_TITLE_LINK,
        ];
    }

    /**
     * @return string[]
     */
    public function getUserDisplayFields(): array
    {
        return [
            static::USER_FIELD_NAME_LINK,
            static::USER_FIELD_FMNO,
            static::USER_FIELD_AVAILABLE,
            static::USER_FIELD_STAFFING_MGR,
            //static::USER_FIELD_AVL_FOR_BEACH,
        ];
    }

    /**
     * @return array
     */
    public function getStaffingEmploymentTypes(): array
    {
        return Configuration::getInstance()->getStaffingEmploymentTypes();
    }

    /**
     * @return array
     */
    public function getStaffingUserRoles(): array
    {
        return Configuration::getInstance()->getStaffingUserRoles();
    }

    /**
     * @return bool
     * @throws \Exception
     */
    public function checkRequirements(): bool
    {
        if (!Container::getInstance()->getUserPermissions()->hasPdStaffingPermissions())
        {
            throw new Exception(Loc::getMessage($this->moduleId."_COMPONENT_ERROR_PERMISSIONS"));
        }

        return true;
    }

    /**
     * @return array
     * @throws \Exception
     */
    protected function getUsersForExcel(): array
    {
        $limitCount = $this->request->get('USER_PAGES_COUNT') ?? 1;
        $filter     = $this->getUserFilterValues();
        $limit      = (int)$limitCount * $this->pageLimit;

        return $this->getUsersData($limit, $filter);
    }

    /**
     * @return array
     * @throws \Exception
     */
    protected function getProjectsForExcel(): array
    {
        $limitCount = $this->request->get('PROJECT_PAGES_COUNT') ?? 1;
        $filter     = $this->getProjectFilterValues();
        $limit      = (int)$limitCount * $this->pageLimit;

        return $this->getProjectsData($limit, $filter);
    }

    /**
     * @return array
     * @throws \Exception
     */
    public function getPerDiemEditReasons(): array
    {
        return Configuration::getInstance()->getPerDiemEditReasonsList();
    }
}