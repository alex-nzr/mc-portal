<?php
namespace Cbit\Mc\Staffing\Component;

if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die();

use Bitrix\Main\Localization\Loc;
use Bitrix\Main\Type\Date;
use Cbit\Mc\Core\Component\BaseComponent;
use Cbit\Mc\Staffing\Helper\Employment;
use Cbit\Mc\Staffing\Internals\Control\ServiceManager;
use Cbit\Mc\Staffing\Service\Access\UserPermissions;
use Cbit\Mc\Staffing\Service\Container;
use CIntranetUtils;
use CSite;
use Exception;

/**
 * Class UserEmployment
 * @package Cbit\Mc\Staffing\Component
 */
class UserReport extends BaseComponent
{
    public   string          $moduleId;
    private  int             $userId    = 0;
    private  int             $pageLimit = 10;
    private  bool            $isOwnProfile = false;
    private  UserPermissions $perms;

    /**
     * @param $component
     * @throws \Exception
     */
    public function __construct($component = null)
    {
        $this->moduleId = ServiceManager::getModuleId();
        $this->perms    = Container::getInstance()->getUserPermissions();
        parent::__construct($component);
    }

    /**
     * @param $arParams
     * @return array
     * @throws \Exception
     */
    public function onPrepareComponentParams($arParams): array
    {
        if (!empty($arParams['USER_ID']))
        {
            $this->userId = (int)$arParams['USER_ID'];
            $this->isOwnProfile = ($this->userId === Container::getInstance()->getContext()->getUserId());
        }

        return parent::onPrepareComponentParams($arParams);
    }

    /**
     * @param int|null $userId
     * @param int $lastId
     * @return array
     * @throws \Exception
     */
    public function getResult(?int $userId = null, int $lastId = 0): array
    {
        $result = [
            'HAS_STAFFING_PERMS' => $this->perms->hasPdStaffingPermissions()
        ];

        if ($this->isOwnProfile || $this->perms->hasPdStaffingPermissions())
        {
            $result["INDUSTRIES_DIAGRAM"] = $this->calculateIndustriesDiagramData($userId);
            $result["GENERAL_NETWORK"]    = $this->getGeneralNetwork($result["INDUSTRIES_DIAGRAM"]);
            $result["PROJECTS"] = $this->getUserProjectsData($userId, $lastId);
        }

        if ($this->perms->hasPdStaffingPermissions())
        {
            $result["ABSENCES"] = $this->getUserAbsencesData();
            $result["FUNCTIONS_DIAGRAM"]  = $this->calculateFunctionsDiagramData($userId);
        }

        return $result;
    }

    /**
     * @param int|null $userId
     * @param int $lastId
     * @return array
     * @throws \Exception
     */
    public function getUserProjectsData(?int $userId = null, int $lastId = 0): array
    {
        $projects = Employment::getUserProjectsData(
            !empty($userId) ? $userId : $this->userId,
            [ '>ID' => $lastId ],
            $this->getPageLimit()
        );

        if (!$this->perms->hasPdStaffingPermissions())
        {
            foreach ($projects as $key => $project)
            {
                $projects[$key]['PROJECT_CLIENT'] = '';
                $projects[$key]['PROJECT_DESCRIPTION'] = '';
            }
        }

        return $projects;
    }

    /**
     * Метод сделан как временное решение по выводу отсутствий. В дальнейшем планируется переделать на вывод в grid.
     * Нужно будет переписать метод CIntranetUtils::GetAbsenceData, добавив возможность устанавливать лимит, пагинацию и т.п.
     * @param int|null $userId
     * @param string|null $startDate
     * @return array
     * @throws \Bitrix\Main\ObjectException
     */
    public function getUserAbsencesData(?int $userId = null, string $startDate = null): array
    {
        if(empty($userId)){
            $userId = $this->userId;
        }

        if (empty($startDate))
        {
            $startDate = date(Date::convertFormatToPhp(CSite::GetDateFormat()), strtotime(date('2022-01-01')));
        }

        $params = [
            'DATE_START' => $startDate,
            'USERS'      => [$userId],
            'PER_USER'   => false
        ];

        $absences = (array)CIntranetUtils::GetAbsenceData($params);
        foreach ($absences as $key => $absence)
        {
            $from = new Date($absence['DATE_FROM']);
            $to   = new Date($absence['DATE_TO']);

            //из 1с иногда приходят непонятные отсутствия за 1899 год, поэтому проверка
            if ((int)$from->format('Y') === 1899)
            {
                unset($absences[$key]);
            }
            else
            {
                $absences[$key]['DATE_FROM'] = $from->format('d.m.Y');
                $absences[$key]['DATE_TO']   = $to->format('d.m.Y');
            }
        }

        return array_values($absences);
    }

    /**
     * @param int|null $userId
     * @return array
     * @throws \Exception
     */
    public function calculateIndustriesDiagramData(?int $userId):array
    {
        return Employment::calculateIndustriesPartData(!empty($userId) ? $userId : $this->userId);
    }

    /**
     * @param int|null $userId
     * @return array
     * @throws \Exception
     */
    public function calculateFunctionsDiagramData(?int $userId):array
    {
        return Employment::calculateFunctionsPartData(!empty($userId) ? $userId : $this->userId);
    }

    /**
     * @param array $projectPartData
     * @return string
     * @throws \Exception
     */
    public function getGeneralNetwork(array $projectPartData): string
    {
        return Employment::calculateGeneralUserNetwork($this->userId, $projectPartData);
    }

    /**
     * @return bool
     * @throws \Exception
     */
    public function checkRequirements(): bool
    {
        if (!($this->userId > 0))
        {
            throw new Exception(Loc::getMessage($this->moduleId."_COMPONENT_ERROR_USER_ID"));
        }
        return true;
    }

    /**
     * @return int
     */
    public function getPageLimit(): int
    {
        return $this->pageLimit;
    }

    /**
     * @return string[]
     */
    protected function listKeysSignedParameters(): array
    {
        return [
            'USER_ID'
        ];
    }
}