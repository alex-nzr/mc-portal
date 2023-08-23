<?php
/**
 * ==================================================
 * Developer: Alexey Nazarov
 * E-mail: alsnazarov@1cbit.ru
 * Copyright (c) 2019 - 2022
 * ==================================================
 * mc-portal - Bind.php
 * 30.11.2022 19:45
 * ==================================================
 */
namespace Cbit\Mc\Staffing\Service\Operation\Recruitment;

use Bitrix\Main\Error;
use Bitrix\Main\Localization\Loc;
use Bitrix\Main\ORM\Data\AddResult;
use Bitrix\Main\Result;
use Bitrix\Main\Type\Date;
use Cbit\Mc\Core\Helper\Iblock\IblockElement;
use Cbit\Mc\Staffing\Internals\Control\ServiceManager;
use Cbit\Mc\Staffing\Internals\Model\UserProjectTable;
use Cbit\Mc\Staffing\Service\Access\Permission;
use Cbit\Mc\Staffing\Helper\Employment;
use Exception;
use Throwable;

/**
 * Class Bind
 * @package Cbit\Mc\Staffing\Service\Operation\Recruitment
 */
class Bind extends Base
{
    /**
     * @param array $params
     * @param array $customPerDiemData
     * @return \Cbit\Mc\Staffing\Service\Operation\Recruitment\Base
     * @throws \Exception
     */
    public function configureParams(array $params, array $customPerDiemData = []): Base
    {
        return parent::configureParams($params, $customPerDiemData)
                        ->checkValidityOfCustomPerDiemData();
    }

    /**
     * @return \Cbit\Mc\Staffing\Service\Operation\Recruitment\Base
     * @throws \Exception
     */
    protected function checkValidityOfCustomPerDiemData(): Base
    {
        if (!empty($this->customPerDiemData))
        {
            $datesToCheck = [];
            foreach ($this->customPerDiemData as $key => $item)
            {
                $rowNumber = $key + 1;

                [$value, $from, $to, $reasonId] = $item;

                if (0 >= (int)$value)
                {
                    throw new Exception("Error: value in row $rowNumber must be more than 0");
                }

                if (empty($reasonId) || !((int)$reasonId > 0))
                {
                    throw new Exception("Error: reason in row $rowNumber is incorrect");
                }

                $datesToCheck[$rowNumber] = [new Date($from), new Date($to)];
            }

            if (!empty($datesToCheck))
            {
                $dateForCompare = null;
                foreach ($datesToCheck as $rowNumber => [$from, $to])
                {
                    if ($rowNumber < 1)
                    {
                        throw new Exception("$rowNumber can not be less than 0");
                    }

                    if (!($from instanceof Date) || !($to instanceof Date))
                    {
                        throw new Exception("'from' and 'to' must be instances od Type/Date class");
                    }

                    if ($rowNumber === 1)
                    {
                        //$from in first row must be equal to $this->from, because it is start of employment period
                        if ($this->from->getTimestamp() !== $from->getTimestamp())
                        {
                            throw new Exception(
                                'Start of employment period is ' . $this->from->format('d.m.Y')
                                . ', but start of per diem changing period is ' . $from->format('d.m.Y')
                                . '. These dates should be equal'
                            );
                        }

                        $dateForCompare = $to;
                        continue;
                    }

                    //from comparing with date to compare
                    if ($from->getTimestamp() !== ($dateForCompare->getTimestamp() + 86400))
                    {
                        $previousRow = $rowNumber - 1;
                        $expectedDate = Date::createFromTimestamp($dateForCompare->getTimestamp() + 86400);
                        throw new Exception(
                            "'from' in row-$rowNumber must be ". $expectedDate->format('d.m.Y')
                            .", because 'to' in row-$previousRow = " . $dateForCompare->format('d.m.Y')
                        );
                    }

                    $dateForCompare = $to;

                    if ($rowNumber === count($datesToCheck))
                    {
                        //$to in last row must be equal to $this->to, because it is end of employment period
                        if ($this->to->getTimestamp() !== $to->getTimestamp())
                        {
                            throw new Exception(
                                'End of employment period is ' . $this->to->format('d.m.Y')
                                . ', but end of per diem changing period is ' . $to->format('d.m.Y')
                                . '. These dates should be equal'
                            );
                        }
                    }
                }
            }
        }

        return $this;
    }

    /**
     * @return \Bitrix\Main\Result
     */
    public function launch(): Result
    {
        $moduleId = ServiceManager::getModuleId();
        $result = new Result();

        try
        {
            $maxEmploymentInPeriod = Employment::getUserMaxEmploymentInPeriod($this->userId, $this->from, $this->to);
            if (($maxEmploymentInPeriod + $this->employmentPercent) > 100)
            {
                throw new Exception(Loc::getMessage($moduleId."_OPERATION_MAX_PERCENT_ERROR", [
                    '#MAX_PERCENT#' => $maxEmploymentInPeriod,
                    '#ADD_PERCENT#' => $this->employmentPercent
                ]));
            }

            if (Employment::userHasSameEmploymentInPeriod($this->userId, $this->userRole, $this->from, $this->to))
            {
                throw new Exception(Loc::getMessage($moduleId."_OPERATION_HAS_SAME_STAFFING_ERROR"));
            }

            $needResult = Employment::findRequiredNeedInProject(
                $this->projectId, $this->userRole, $this->employmentPercent, $this->from, $this->to
            );
            if (!$needResult->isSuccess())
            {
                throw new Exception(implode('; ', $needResult->getErrorMessages()));
            }

            $needId = (int)$needResult->getData()['ID'];
            if ($needId <= 0)
            {
                throw new Exception('Need ID not found in needResult');
            }

            if (empty($this->customPerDiemData))
            {
                $result = $this->saveUserEmployment($needId, $this->from, $this->to);
            }
            else
            {
                $result = $this->saveUserEmploymentWithCustomPerDiemData($needId, $this->customPerDiemData);
            }
        }
        catch(Throwable $e)
        {
            $result->addError(new Error($e->getMessage()));
        }

        return $result;
    }

    /**
     * @param int $needId
     * @param \Bitrix\Main\Type\Date $from
     * @param \Bitrix\Main\Type\Date $to
     * @param int|null $perDiemValue
     * @param string $perDiemReason
     * @return \Bitrix\Main\ORM\Data\AddResult
     * @throws \Exception
     */
    private function saveUserEmployment(
        int $needId, Date $from, Date $to,
        ?int $perDiemValue = null, string $perDiemReason = ''): AddResult
    {
        $addResult = UserProjectTable::add([
            'USER_ID'                 => $this->userId,
            'PROJECT_ID'              => $this->projectId,
            'USER_ROLE'               => $this->userRole,
            'USER_EMPLOYMENT_PERCENT' => $this->employmentPercent,
            'USER_EMPLOYMENT_TYPE'    => $this->employmentType,
            'STAFFING_DATE_FROM'      => $from,
            'STAFFING_DATE_TO'        => $to,
            'RELATED_NEED_ID'         => $needId,
            'USER_PER_DIEM'           => !empty($perDiemValue) ? $perDiemValue : $this->perDiemValue,
            'PER_DIEM_REASON'         => $perDiemReason,
            'PER_DIEM_COMMENT'        => ''
        ]);

        if ($addResult->isSuccess())
        {
            $updNeedRes = Employment::reduceProjectNeedData($this->projectId, $needId, $from, $to);
            if (!$updNeedRes->isSuccess())
            {
                $addResult->addErrors($updNeedRes->getErrors());
            }
        }

        return $addResult;
    }

    /**
     * @param int $needId
     * @param array $customPerDiemData
     * @return \Bitrix\Main\Result
     * @throws \Exception
     */
    private function saveUserEmploymentWithCustomPerDiemData(int $needId, array $customPerDiemData): Result
    {
        $result          = new Result();
        $createdNeeds    = [];
        $createdBindings = [];

        foreach ($customPerDiemData as $item)
        {
            [$value, $from, $to, $reasonId] = $item;

            $from = new Date($from);
            $to   = new Date($to);

            $addNeedResult = Employment::addNeed([
                'PROJECT_ID'              => $this->projectId,
                'USER_ROLE'               => $this->userRole,
                'USER_EMPLOYMENT_PERCENT' => $this->employmentPercent,
                'NEEDLE_DATE_FROM'        => $from,
                'NEEDLE_DATE_TO'          => $to,
            ]);

            $reasonText = (string)IblockElement::getElementById($reasonId)['NAME'];

            if ($addNeedResult->isSuccess())
            {
                $newNeedId      = $addNeedResult->getId();
                $createdNeeds[] = $newNeedId;

                $saveResult = $this->saveUserEmployment($newNeedId, $from, $to, $value, $reasonText);
                if (!$saveResult->isSuccess())
                {
                    $result->addErrors($saveResult->getErrors());
                }
                else
                {
                    $createdBindings[] = $saveResult->getId();
                }
            }
            else
            {
                $result->addErrors($addNeedResult->getErrors());
            }
        }

        if ($result->isSuccess())
        {
            $deleteRes = Employment::deleteNeed($needId);
            if (!$deleteRes->isSuccess())
            {
                $result->addError(new Error("Can not delete old 'need' with id $needId"));
                $result->addErrors($deleteRes->getErrors());
            }
        }
        else
        {
            foreach ($createdNeeds as $createdNeedId)
            {
                Employment::deleteNeed($createdNeedId);
            }

            foreach ($createdBindings as $createdBindingId)
            {
                UserProjectTable::delete($createdBindingId);
            }
        }

        return $result;
    }
}