<?php
namespace Cbit\Mc\Staffing\Component;

if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die();

use Bitrix\Main\Error;
use Bitrix\Main\Localization\Loc;
use Bitrix\Main\Result;
use Bitrix\Main\Type\Date;
use Cbit\Mc\Core\Component\BaseComponent;
use Cbit\Mc\Core\Helper\Main\User;
use Cbit\Mc\Core\Service\Integration\Zup\Fields;
use Cbit\Mc\Staffing\Config\Configuration;
use Cbit\Mc\Staffing\Entity\Dynamic;
use Cbit\Mc\Staffing\Helper\Employment;
use Cbit\Mc\Staffing\Internals\Control\ServiceManager;
use Cbit\Mc\Staffing\Internals\Model\EmploymentNeedTable;
use Cbit\Mc\Staffing\Internals\Model\UserProjectTable;
use Cbit\Mc\Staffing\Service\Container;
use Cbit\Mc\Staffing\Service\Operation\Recruitment;
use Exception;
use Throwable;

/**
 * Class ProjectTeam
 * @package Cbit\Mc\Staffing\Component
 */
class ProjectTeam extends BaseComponent
{
    public string $moduleId;
    private int $projectId = 0;

    /**
     * ProjectTeam constructor.
     * @param null $component
     */
    public function __construct($component = null)
    {
        $this->moduleId = ServiceManager::getModuleId();
        parent::__construct($component);
    }

    /**
     * @param $arParams
     * @return array
     */
    public function onPrepareComponentParams($arParams): array
    {
        if (!empty($arParams['PROJECT_ID']))
        {
            $this->projectId = (int)$arParams['PROJECT_ID'];
        }

        return array_merge(parent::onPrepareComponentParams($arParams), $arParams);
    }

    /**
     * @return array
     * @throws \Exception
     */
    public function getResult(): array
    {
        return [
            'TEAM'                => $this->getProjectTeam(),
            'NEEDLE'              => $this->getNeedleEmployees(),
            'STAFFING_USER_ROLES' => $this->getStaffingUserRoles(),
            'PROJECT_DATA'        => $this->getProjectData()
        ];
    }

    /**
     * @param int|null $projectId
     * @return array
     * @throws \Exception
     */
    public function getProjectData(int $projectId = null): array
    {
        if (empty($projectId))
        {
            $projectId = $this->projectId;
        }

        $typeId = Dynamic::getInstance()->getTypeId();
        $project = Dynamic::getInstance()->getItemFactory()->getDataClass()::query()
            ->where('ID', '=', $projectId)
            ->setSelect([
                'UF_CRM_'.$typeId.'_START_DATE', 'UF_CRM_'.$typeId.'_END_DATE'
            ])
            ->fetch();

        if (is_array($project))
        {
            $start = $project['UF_CRM_'.$typeId.'_START_DATE'];
            $end   = $project['UF_CRM_'.$typeId.'_END_DATE'];
            return [
                'START_DATE' => ($start instanceof Date) ? $start->toString() : '',
                'END_DATE'   => ($end instanceof Date) ? $end->toString() : '',
            ];
        }

        return [];
    }

    /**
     * @param int|null $projectId
     * @return array
     * @throws \Exception
     */
    public function getProjectTeam(int $projectId = null): array
    {
        if (empty($projectId))
        {
            $projectId = $this->projectId;
        }

        $elements = UserProjectTable::query()
            ->where('PROJECT_ID', '=', $projectId)
            ->whereNot('DELETION_MARK', '=', 'Y')
            ->setSelect([
                'ID', 'USER_ID', 'PROJECT_ID', 'USER_ROLE', 'USER_EMPLOYMENT_PERCENT',
                'USER_EMPLOYMENT_TYPE', 'STAFFING_DATE_FROM', 'STAFFING_DATE_TO', 'USER_PER_DIEM',
                'USER_PHOTO_ID' => 'USER.PERSONAL_PHOTO',
                'NAME' => 'USER.NAME',
                'LAST_NAME' => 'USER.LAST_NAME',
                Fields::getFioEnUfCode() => 'USER.'.Fields::getFioEnUfCode(),
            ])
            ->setOrder(['ID' => 'ASC'])
            ->fetchAll();

        $result = [];
        foreach($elements as $key => $elem)
        {
            $elem['NUMBER'] = $key+1;
            $elem['USER_LINK'] = User::getProfileViewLink($elem['USER_ID'], $elem);
            $elem['USER_PHOTO'] = User::getResizedAvatarByFileId((int)$elem["USER_PHOTO_ID"]);
            $elem['PROJECT_LINK'] = Container::getInstance()->getRouter()->getItemDetailUrl(
                Dynamic::getInstance()->getEntityTypeId(), $elem['PROJECT_ID']
            );
            $elem['STAFFING_DATE_FROM'] = $elem['STAFFING_DATE_FROM']->format('d.m.Y');
            $elem['STAFFING_DATE_TO'] = $elem['STAFFING_DATE_TO']->format('d.m.Y');

            $result[$elem['ID']] = $elem;
        }
        return $result;
    }

    /**
     * @param int|null $projectId
     * @return array
     * @throws \Exception
     */
    public function getNeedleEmployees(int $projectId = null): array
    {
        if (empty($projectId))
        {
            $projectId = $this->projectId;
        }

        $data = EmploymentNeedTable::query()
            ->setSelect(['*'])
            ->setFilter([
                'PROJECT_ID' => $projectId,
                'ACTIVE'     => 'Y'
            ])
            ->setOrder(['ID' => 'ASC'])
            ->fetchAll();

        $result = [];
        foreach($data as $key => $item)
        {
            $result[$item['ID']] = [
                'NUMBER'                  => $key + 1,
                'USER_ROLE'               => $item['USER_ROLE'],
                'USER_EMPLOYMENT_PERCENT' => $item['USER_EMPLOYMENT_PERCENT'],
                'NEEDLE_DATE_FROM'        => ($item['NEEDLE_DATE_FROM'] instanceof Date)
                                                ? $item['NEEDLE_DATE_FROM']->format('d.m.Y') : '',
                'NEEDLE_DATE_TO'          => ($item['NEEDLE_DATE_TO'] instanceof Date)
                                                ? $item['NEEDLE_DATE_TO']->format('d.m.Y') : '',
            ];
        }

        return $result;
    }

    /**
     * @param array $data
     * @return \Bitrix\Main\Result
     * @throws \Exception
     */
    public function addNeedleEmployee(array $data): Result
    {
        return Employment::addNeed($data);
    }

    /**
     * @param array $data
     * @return \Bitrix\Main\Result
     * @throws \Exception
     */
    public function updateNeedleEmployee(array $data): Result
    {
        if (empty($data['ID']))
        {
            throw new Exception("ID of updating 'Need' is empty.");
        }

        return Employment::updateNeed((int)$data['ID'], $data);
    }

    /**
     * @param int $id
     * @return \Bitrix\Main\Result
     * @throws \Exception
     */
    public function deleteNeedleEmployee(int $id): Result
    {
        return Employment::deleteNeed($id);
    }

    /**
     * @param int $recordId
     * @param int $userId
     * @param int $projectId
     * @return \Bitrix\Main\Result
     * @throws \Exception
     */
    public function deleteEmployeeFromProjectTeam(int $recordId, int $userId, int $projectId): Result
    {
        try
        {
            $operation = (new Recruitment\Delete())->configureParams([
                'RECORD_ID'  => $recordId,
                'USER_ID'    => $userId,
                'PROJECT_ID' => $projectId
            ]);
            return $operation->launch();
        }
        catch(Throwable $e)
        {
            return (new Result)->addError(new Error($e->getMessage()));
        }
    }

    /**
     * @param array $data
     * @return \Bitrix\Main\Result
     */
    public function updateStaffingPeriodOfUser(array $data): Result
    {
        try
        {
            $operation = (new Recruitment\Update())->configureParams([
                'RECORD_ID'          => $data['ID'],
                'USER_ID'            => $data['USER_ID'],
                'PROJECT_ID'         => $data['PROJECT_ID'],
                'USER_ROLE'          => $data['USER_ROLE'],
                'USER_PER_DIEM'      => $data['USER_PER_DIEM'],
                'STAFFING_DATE_FROM' => $data['STAFFING_DATE_FROM'],
                'STAFFING_DATE_TO'   => $data['STAFFING_DATE_TO'],
            ]);
            return $operation->launch();
        }
        catch(Throwable $e)
        {
            return (new Result)->addError(new Error($e->getMessage()));
        }
    }

    /**
     * @return array
     */
    public function getStaffingUserRoles(): array
    {
        return Configuration::getInstance()->getStaffingUserRoles();
    }

    /**
     * @return string[]
     */
    protected function listKeysSignedParameters(): array
    {
        return ['PROJECT_ID'];
    }

    /**
     * @return bool
     * @throws \Exception
     */
    public function checkRequirements(): bool
    {
        if (!($this->projectId > 0))
        {
            throw new Exception(Loc::getMessage($this->moduleId."_COMPONENT_ERROR_PROJECT_ID"));
        }
        return true;
    }
}