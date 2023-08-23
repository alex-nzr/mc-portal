<?php
/**
 * ==================================================
 * Developer: Alexey Nazarov
 * E-mail: alsnazarov@1cbit.ru
 * Copyright (c) 2019 - 2022
 * ==================================================
 * mc-portal - Base.php
 * 07.12.2022 12:11
 * ==================================================
 */


namespace Cbit\Mc\Staffing\Service\Operation\Recruitment;

use Bitrix\Main\Localization\Loc;
use Bitrix\Main\Result;
use Bitrix\Main\Type\Date;
use Cbit\Mc\Staffing\Internals\Control\ServiceManager;
use Cbit\Mc\Staffing\Service\Container;
use Exception;

/**
 * Class Base
 * @package Cbit\Mc\Staffing\Service\Operation\Recruitment
 */
abstract class Base
{
    protected int    $recordId;
    protected int    $userId;
    protected int    $projectId;
    protected int    $employmentPercent;
    protected string $employmentType;
    protected string $userRole;
    protected Date   $from;
    protected Date   $to;
    protected int    $perDiemValue;
    protected array  $customPerDiemData;

    public function __construct()
    {
        Loc::loadMessages(__FILE__);
    }

    /**
     * @param array $params
     * @param array $customPerDiemData
     * @return $this
     * @throws \Exception
     */
    public function configureParams(array $params, array $customPerDiemData = []): Base
    {
        $moduleId = ServiceManager::getModuleId();

        if (!Container::getInstance()->getUserPermissions()->hasPdStaffingPermissions())
        {
            throw new Exception(Loc::getMessage($moduleId."_OPERATION_PERM_ERROR"));
        }

        foreach ($params as $key => $value)
        {
            if (empty($value))
            {
                throw new Exception(Loc::getMessage($moduleId."_OPERATION_REQUIRED_PARAM_ERROR", [
                    '#PARAM#' => $key
                ]));
            }

        }
        $this->recordId          = (int)$params['RECORD_ID'];
        $this->userId            = (int)$params['USER_ID'];
        $this->projectId         = (int)$params['PROJECT_ID'];
        $this->employmentPercent = (int)$params['USER_EMPLOYMENT_PERCENT'];
        $this->employmentType    = (string)$params['USER_EMPLOYMENT_TYPE'];
        $this->userRole          = (string)$params['USER_ROLE'];
        $this->from              = new Date($params['STAFFING_DATE_FROM']);
        $this->to                = new Date($params['STAFFING_DATE_TO']);
        $this->perDiemValue      = (int)$params['USER_PER_DIEM'];
        $this->customPerDiemData = $customPerDiemData;

        if ($this->to < $this->from)
        {
            throw new Exception(Loc::getMessage($moduleId."_OPERATION_DATE_ERROR"));
        }

        return $this;
    }

    abstract public function launch(): Result;
}