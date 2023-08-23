<?php
namespace Cbit\Mc\RI\Component;

if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die();

use Bitrix\Main\Grid\Options;
use Bitrix\Main\UI\Filter\Type;
use Bitrix\Main\UI\PageNavigation;
use Bitrix\Main\UserTable;
use Cbit\Mc\Core\Component\BaseComponent;
use Cbit\Mc\Core\Config\Configuration as CoreConfig;
use Cbit\Mc\Core\Helper\Iblock\IblockElement;
use Cbit\Mc\Core\Helper\Main\User;
use Cbit\Mc\Core\Service\Integration\Zup\Fields;
use Cbit\Mc\RI\Config\Configuration;
use Cbit\Mc\RI\Internals\Control\ServiceManager;
use Cbit\Mc\RI\Service\Access\Permission;
use Cbit\Mc\RI\Service\Container;

class TeamProfile extends BaseComponent
{
    public string $moduleId;
    public string $gridId = 'cbit_ri_team_profile_grid';
    public string $filterId = 'cbit_ri_team_profile_grid_filter';
    private int   $defaultPageSize = 20;
    private int   $totalCount = 0;
    private PageNavigation $navObject;

    private bool   $userInRiAnalysts;
    private bool   $userInRiManagers;

    /**
     * @param $component
     * @throws \Exception
     */
    public function __construct($component = null)
    {
        $this->moduleId = ServiceManager::getModuleId();

        $perms = Container::getInstance()->getUserPermissions();
        $this->userInRiAnalysts     = $perms->hasUserRiAnalystPermissions();
        $this->userInRiManagers     = $perms->hasUserRiManagerPermissions();

        parent::__construct($component);
    }

    /**
     * @return array
     * @throws \Exception
     */
    public function getResult(): array
    {
        $this->setPageNavigation();

        return [
            'CURRENT_COORDINATOR'     => $this->getCurrentCoordinatorData(),
            'TEAM_DESCRIPTION'        => trim($this->getCurrentTeamDescription()),
            'TEAM_WORK_TIME'          => $this->getCurrentTeamWorkTime(),
            'GRID_ID'                 => $this->gridId,
            'FILTER_ID'               => $this->filterId,
            'FILTER'                  => $this->getFilterSettings(),
            'FILTER_PRESETS'          => [],
            'COLUMNS'                 => $this->getColumns(),
            'ROWS'                    => $this->getRows(),
            'TOTAL_ROWS_COUNT'        => $this->totalCount,
            'NAV'                     => $this->navObject,
            'IS_COORDINATOR_EDITABLE' => $this->userInRiAnalysts || $this->userInRiManagers,
            'IS_WORK_TIME_EDITABLE'   => $this->userInRiManagers,
            'IS_TEAM_DESC_EDITABLE'   => $this->userInRiManagers,
        ];
    }

    /**
     * @return array
     * @throws \Exception
     */
    public function getFilterSettings(): array
    {
        $filterFields = $this->getUserDisplayFields();
        unset($filterFields['PERSONAL_PHOTO']);
        $filterFields['LAST_NAME'] = 'Last name';
        $filterFields[Fields::getFioEnUfCode()] = 'FIO (EN)';

        $filter = [];
        foreach ($filterFields as $fieldCode => $fieldName)
        {
            switch ($fieldCode)
            {
                case 'ID':
                    $filter[] = [
                        'id'      => $fieldCode,
                        'name'    => $fieldName,
                        'type'    => 'integer',
                        'default' => true
                    ];
                    break;
                case 'UF_COVERED_INDUSTRIES':
                    $filter[] = [
                        'id' => $fieldCode,
                        'name' => $fieldName,
                        'type' => 'list',
                        'items' => IblockElement::getElementsListToFilter(CoreConfig::getInstance()->getIndustriesIblockId()),
                        'params' => ['multiple' => 'Y'],
                        'default' => true
                    ];
                    break;
                default:
                    $filter[] = [
                        'id'      => $fieldCode,
                        'name'    => $fieldName,
                        'type'    => 'string',
                        'default' => true
                    ];
                    break;
            }
        }
        return $filter;
    }

    /**
     * @return void
     */
    public function setPageNavigation(): void
    {
        $nav = new PageNavigation($this->gridId);
        $grid_options = new Options($this->gridId);
        $nav_params = $grid_options->GetNavParams();
        $nav->allowAllRecords(false)
            ->setPageSize(!empty($nav_params['nPageSize']) ? $nav_params['nPageSize'] : $this->defaultPageSize)
            ->initFromUri();

        $this->navObject =  $nav;
    }

    /**
     * @return array
     * @throws \Exception
     */
    public function getRows(): array
    {
        $riUserIds = array_unique(
            array_merge(
                Permission::getRiManagersIds(), Permission::getRiAnalystsIds()
            )
        );

        $select     = array_merge(['LAST_NAME', Fields::getFioEnUfCode()], array_keys($this->getUserDisplayFields()));
        $filter     = array_merge(['=ID' => $riUserIds], $this->getFilterValues());
        $usersQuery = UserTable::query()
                        ->setOrder($this->getGridSort())
                        ->setSelect($select)
                        ->setFilter($filter)
                        ->setOffset($this->navObject->getOffset())
                        ->setLimit($this->navObject->getLimit())
                        ->countTotal(true)
                        ->exec();

        $this->navObject->setRecordCount($usersQuery->getCount());
        $this->totalCount = $usersQuery->getCount();

        $users = $usersQuery->fetchAll();

        $rows = [];
        foreach ($users as $user)
        {
            $data = [];
            foreach ($user as $key => $value)
            {
                switch ($key)
                {
                    case 'NAME':
                        $data[$key] = User::getProfileViewLink((int)$user['ID'], $user);
                        break;
                    case 'PERSONAL_PHOTO':
                        $data[$key] = !empty($value) ? "<img class='avatar' src='".User::getResizedAvatarByFileId((int)$value)."'>" : '<i class="empty"></i>';
                        break;
                    case  'UF_COVERED_INDUSTRIES':
                        $data[$key] = is_array($value) ? implode('<br>', array_map(function($id){
                            return IblockElement::getElementById($id)['NAME'];
                        }, $value)) : $value;
                        break;
                    case 'LAST_NAME':
                    case Fields::getFioEnUfCode():
                        break;
                    default:
                        $data[$key] = $value;
                        break;
                }
            }

            $rows[] = [
                'id'   => $user['ID'],
                'data' => $data
            ];
        }
        return $rows;
    }

    /**
     * @return array
     * @throws \Exception
     */
    public function getFilterValues():array
    {
        $filterOption   = new \Bitrix\Main\UI\Filter\Options($this->filterId);
        $filterData     = $filterOption->getFilter();
        $filterSettings = $this->getFilterSettings();
        $preparedFilter = Type::getLogicFilter($filterData, $filterSettings);
        $filter = [];
        foreach ($filterSettings as $item)
        {
            if (!empty($preparedFilter[$item['id']]))
            {
                switch ($item['type'])
                {
                    case 'integer':
                        $filter['='.$item['id']] = $preparedFilter[$item['id']];
                        break;
                    case 'text':
                    case 'string':
                        $filter['%'.$item['id']] = $preparedFilter[$item['id']];
                        break;
                    default:
                        $filter[$item['id']] = $preparedFilter[$item['id']];
                        break;
                }
            }
        }
        return $filter;
    }

    /**
     * @return array
     */
    public function getGridSort():array
    {
        $gridOptions = new Options($this->gridId);
        $gridSort = $gridOptions->GetSorting(['sort' => ['ID' => 'DESC']]);
        return (array)$gridSort['sort'];
    }

    /**
     * @return array
     */
    public function getColumns(): array
    {
        $res = [];
        foreach ($this->getUserDisplayFields() as $code => $name)
        {
            $res[$code] = [
                'id'      => $code,
                'name'    => $name,
                'default' => true,
                'sort'    => $code,
            ];
        }
        return $res;
    }

    /**
     * @return array
     */
    public function getUserDisplayFields(): array
    {
        $fields = [
            'ID'                        => 'ID',
            //Fields::getFmnoUfCode()     => 'FMNO',
            'PERSONAL_PHOTO'            => 'Photo',
            'NAME'                      => 'Name',
            'WORK_POSITION'             => 'Position',
            Fields::getZupStatusUfCode()=> 'Status',
            'EMAIL'                     => 'Email',
            'UF_EMAIL'                  => 'Additional email',
            'PERSONAL_PHONE'            => 'Phone',
            'PERSONAL_MOBILE'           => 'Mobile phone',
            'UF_TELEGRAM'               => 'Telegram',
            'UF_COVERED_INDUSTRIES'     => 'Covered industries',
        ];

        if ($this->userInRiManagers)
        {
            $fields[Fields::getBasePerDiemUfCode()] = 'Per diem';
        }

        return $fields;
    }

    /**
     * @return array
     * @throws \Exception
     */
    public function getCurrentCoordinatorData(): array
    {
        return Configuration::getInstance()->getCurrentCoordinatorData();
    }

    /**
     * @return string
     */
    public function getCurrentTeamDescription(): string
    {
        return Configuration::getInstance()->getCurrentTeamDescription();
    }

    /**
     * @return array
     */
    public function getCurrentTeamWorkTime(): array
    {
        $res = Configuration::getInstance()->getCurrentTeamWorkTime();
        if (!empty($res))
        {
            $arRes = explode("-", $res);
            return [
                'FROM' => trim($arRes[0]),
                'TO' => trim($arRes[1]),
            ];
        }
        return [];
    }

    /**
     * @return bool
     * @throws \Exception
     */
    public function checkRequirements(): bool
    {
        /*if (!Permission::isUserInRiAnalystsGroup() || !Permission::isUserInRiManagersGroup())
        {
            throw new Exception('No permissions for this component');
        }*/
        return true;
    }
}