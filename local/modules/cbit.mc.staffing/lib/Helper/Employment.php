<?php
/**
 * ==================================================
 * Developer: Alexey Nazarov
 * E-mail: alsnazarov@1cbit.ru
 * Copyright (c) 2019 - 2022
 * ==================================================
 * mc-portal - Employment.php
 * 01.12.2022 13:31
 * ==================================================
 */

namespace Cbit\Mc\Staffing\Helper;

use Bitrix\Main\Error;
use Bitrix\Main\Localization\Loc;
use Bitrix\Main\ORM\Data\AddResult;
use Bitrix\Main\Result;
use Bitrix\Main\Type\Date;
use Cbit\Mc\Core\Config\Constants as CoreConstants;
use Cbit\Mc\Core\Helper\Main\DateTimeCalculator;
use Cbit\Mc\Core\Helper\Main\User;
use Cbit\Mc\Core\Helper\Main\UserField;
use Cbit\Mc\Staffing\Config\Configuration;
use Cbit\Mc\Staffing\Config\Constants;
use Cbit\Mc\Staffing\Entity\Dynamic;
use Cbit\Mc\Staffing\Internals\Control\ServiceManager;
use Cbit\Mc\Staffing\Internals\Debug\Logger;
use Cbit\Mc\Staffing\Internals\Model\EmploymentNeedTable;
use Cbit\Mc\Staffing\Internals\Model\UserProjectTable;
use Cbit\Mc\Staffing\Service\Container;
use CIntranetUtils;
use CUser;
use Exception;
use Throwable;

/**
 * Class Employment
 * @package Cbit\Mc\Staffing\Helper
 */
class Employment
{
    private static array $projects = [];

    /**
     * @param int $userId
     * @return int
     * @throws \Exception
     */
    public static function getUserCurrentEmployment(int $userId): int
    {
        $data = UserProjectTable::query()
            ->setSelect(['USER_EMPLOYMENT_PERCENT'])
            ->setFilter([
                '=USER_ID'              => $userId,
                '!=DELETION_MARK'       => 'Y',
                '<=STAFFING_DATE_FROM'  => new Date(),
                '>=STAFFING_DATE_TO'    => new Date(),
            ])
            ->fetchAll();

        $totalEmployment = 0;
        foreach ($data as $item)
        {
            $totalEmployment += (int)$item['USER_EMPLOYMENT_PERCENT'];
        }

        return $totalEmployment;
    }

    /**
     * @param int $userId
     * @param \Bitrix\Main\Type\Date $startDate
     * @param \Bitrix\Main\Type\Date $endDate
     * @return int
     * @throws \Exception
     */
    public static function getUserMaxEmploymentInPeriod(int $userId, Date $startDate, Date $endDate): int
    {
        $data = UserProjectTable::query()
            ->setSelect(['ID', 'USER_EMPLOYMENT_PERCENT', 'STAFFING_DATE_FROM', 'STAFFING_DATE_TO'])
            ->setFilter([
                '=USER_ID' => $userId,
                '!=DELETION_MARK' => 'Y',
                '<STAFFING_DATE_FROM' => $endDate,
                '>STAFFING_DATE_TO'   => $startDate,
            ])
            ->fetchAll();

        $employmentIntersects = [];
        foreach ($data as $employment)
        {
            $intersect = DateTimeCalculator::getInstance()->getDateIntervalsIntersect(
                $startDate, $endDate, $employment['STAFFING_DATE_FROM'], $employment['STAFFING_DATE_TO']
            );

            if (!empty($intersect))
            {
                $intersect['USER_EMPLOYMENT_PERCENT'] = $employment['USER_EMPLOYMENT_PERCENT'];
                $employmentIntersects[$employment['ID']] = $intersect;
            }
        }

        $overlapping = [];
        foreach ($employmentIntersects as $empId => $empIntersect)
        {
            $overlapping[$empId] = [$empId];
            foreach ($employmentIntersects as $key => $value) {
                if ($key !== $empId)
                {
                    $overAr = DateTimeCalculator::getInstance()->getDateIntervalsIntersect(
                        $empIntersect['start'], $empIntersect['end'], $value['start'], $value['end']
                    );

                    if (!empty($overAr))
                    {
                        $overlapping[$empId][] = $key;
                    }
                }
            }
        }

        $maxEmploymentInPeriod = 0;
        foreach ($overlapping as $item)
        {
            $total = 0;
            foreach ($item as $employmentId)
            {
                $total += (int)$employmentIntersects[$employmentId]['USER_EMPLOYMENT_PERCENT'];
            }

            if ($total > $maxEmploymentInPeriod)
            {
                $maxEmploymentInPeriod = $total;
            }
        }

        return $maxEmploymentInPeriod;
    }

    /**
     * @param int $userId
     * @param string $userRole
     * @param \Bitrix\Main\Type\Date $startDate
     * @param \Bitrix\Main\Type\Date $endDate
     * @return bool
     * @throws \Exception
     */
    public static function userHasSameEmploymentInPeriod(int $userId, string $userRole, Date $startDate, Date $endDate): bool
    {
        $data = UserProjectTable::query()
            ->setSelect(['ID', 'USER_EMPLOYMENT_PERCENT', 'STAFFING_DATE_FROM', 'STAFFING_DATE_TO'])
            ->setFilter([
                '=USER_ID'            => $userId,
                '=USER_ROLE'          => $userRole,
                '!=DELETION_MARK'     => 'Y',
                '<STAFFING_DATE_FROM' => $endDate,
                '>STAFFING_DATE_TO'   => $startDate,
            ])
            ->fetchAll();

        return !empty($data);
    }

    /**
     * @param int $projectId
     * @param string $userRole
     * @param int $employmentPercent
     * @param \Bitrix\Main\Type\Date $from
     * @param \Bitrix\Main\Type\Date $to
     * @return \Bitrix\Main\Result
     * @throws \Exception
     */
    public static function findRequiredNeedInProject(
        int $projectId, string $userRole, int $employmentPercent, Date $from, Date $to): Result
    {
        $result = new Result();

        $data = EmploymentNeedTable::query()
            ->setSelect(['*'])
            ->setFilter([
                '=PROJECT_ID' => $projectId,
                '=ACTIVE'     => 'Y'
            ])
            ->setOrder(['ID' => 'ASC'])
            ->fetchAll();

        $needsWithRelevantRole = array_filter($data, function($item) use ($userRole){
            return ($item['USER_ROLE'] === $userRole);
        });

        if (empty($needsWithRelevantRole))
        {
            $result->addError(new Error(
                Loc::getMessage(ServiceManager::getModuleId(). '_RELEVANT_ROLE_NOT_FOUND_IN_PROJECT', [
                    '#ROLE#' => $userRole
                ])
            ));
        }
        else {
            $needsWithSuitablePercent = array_filter($needsWithRelevantRole, function ($item) use ($employmentPercent) {
                return ((int)$item['USER_EMPLOYMENT_PERCENT'] === $employmentPercent);
            });

            if (empty($needsWithSuitablePercent)) {
                $result->addError(new Error(
                    Loc::getMessage(ServiceManager::getModuleId() . '_PERCENT_IS_MORE_THAN_NEED', [
                        '#ROLE#' => $userRole,
                        '#PERCENTS#' => static::getPercentDataFromNeeds($needsWithRelevantRole)
                    ])
                ));
            }
            else
            {
                $needsWithCorrectStartDate = array_filter($needsWithSuitablePercent, function ($item) use ($from) {
                    return ($item['NEEDLE_DATE_FROM']->getTimestamp() === $from->getTimestamp());
                });

                if (empty($needsWithCorrectStartDate)) {
                    $result->addError(new Error(
                        Loc::getMessage(ServiceManager::getModuleId() . '_START_DATE_IS_INCORRECT')
                    ));
                }
                else
                {
                    $needId      = null;
                    $largestDate = null;
                    $message     = null;
                    foreach ($needsWithCorrectStartDate as $need) {
                        $periodCorrect = ($need['NEEDLE_DATE_TO'] >= $to);
                        if ($periodCorrect)
                        {
                            $needId  = $need['ID'];
                            $message = null;
                            break;
                        }
                        else
                        {
                            if (($largestDate === null) || ($need['NEEDLE_DATE_TO'] > $largestDate))
                            {
                                $needId      = $need['ID'];
                                $largestDate = $need['NEEDLE_DATE_TO'];
                            }
                            $message = Loc::getMessage(ServiceManager::getModuleId() . '_END_DATE_IS_CHANGED', [
                                '#OLD_END_DATE#' => $largestDate->format('d.m.Y'),
                                '#NEW_END_DATE#' => $to->format('d.m.Y')
                            ]);
                        }
                    }
                    $result->setData([
                        'ID'      => $needId,
                        'message' => $message
                    ]);
                }
            }
        }

        return $result;
    }

    /**
     * @param int $projectId
     * @param int $needId
     * @param \Bitrix\Main\Type\Date $from
     * @param \Bitrix\Main\Type\Date $to
     * @return \Bitrix\Main\Result
     * @throws \Exception
     */
    public static function reduceProjectNeedData(
        int $projectId, int $needId, Date $from, Date $to
    ): Result
    {
        $need = EmploymentNeedTable::query()
            ->setSelect(['*'])
            ->setFilter([
                '=ID'         => $needId,
                '=PROJECT_ID' => $projectId,
            ])
            ->fetch();

        if (empty($need))
        {
            throw new Exception("Need with ID $needId not found in project with ID $projectId");
        }

        if ($to->getTimestamp() < $need['NEEDLE_DATE_TO']->getTimestamp())
        {
            static::addNeed([
                'PROJECT_ID'              => $need['PROJECT_ID'],
                'USER_ROLE'               => $need['USER_ROLE'],
                'USER_EMPLOYMENT_PERCENT' => $need['USER_EMPLOYMENT_PERCENT'],
                'NEEDLE_DATE_FROM'        => Date::createFromTimestamp($to->getTimestamp() + 86400),
                'NEEDLE_DATE_TO'          => $need['NEEDLE_DATE_TO'],
            ]);
        }

        return static::updateNeed($needId, [
            'NEEDLE_DATE_FROM'        => $from,
            'NEEDLE_DATE_TO'          => $to,
            'ACTIVE'                  => 'N',
        ]);
    }

    /**
     * @param array $needsWithRelevantRole
     * @return string
     */
    public static function getPercentDataFromNeeds(array $needsWithRelevantRole): string
    {
        $res = [];
        foreach ($needsWithRelevantRole as $need)
        {
            $res[] = $need['USER_EMPLOYMENT_PERCENT'];
        }
        return implode(', ', array_unique($res));
    }

    /**
     * @param int $userId
     * @param array $additionalFilter
     * @param int|null $limit
     * @return array
     * @throws \Exception
     */
    public static function getUserProjectsData(int $userId, array $additionalFilter = [], ?int $limit = null): array
    {
        $typeId = Dynamic::getInstance()->getTypeId();
        $industries = Configuration::getInstance()->getProjectIndustriesList();
        $functions = Configuration::getInstance()->getProjectFunctionsList();
        $data = UserProjectTable::query()
            ->setSelect([
                'PROJECT_ID', 'USER_ROLE', 'STAFFING_DATE_FROM', 'STAFFING_DATE_TO', 'USER_EMPLOYMENT_PERCENT',
                'PROJECT_TITLE'       => 'PROJECT.TITLE',
                'INDUSTRY_ID'         => 'PROJECT.UF_CRM_'.$typeId.'_INDUSTRY',
                'FUNCTION_ID'         => 'PROJECT.UF_CRM_'.$typeId.'_FUNCTION',
                'PROJECT_CLIENT'      => 'PROJECT.UF_CRM_'.$typeId.'_CLIENT',
                'PROJECT_DESCRIPTION' => 'PROJECT.UF_CRM_'.$typeId.'_DESCRIPTION',
                'PROJECT_START_DATE'  => 'PROJECT.UF_CRM_'.$typeId.'_START_DATE',
                'PROJECT_END_DATE'    => 'PROJECT.UF_CRM_'.$typeId.'_END_DATE',
                'PROJECT_ED'          => 'PROJECT.UF_CRM_'.$typeId.'_ED',
            ])
            ->setFilter(array_merge(
                [ '=USER_ID' => $userId, '!=DELETION_MARK' => 'Y' ],
                $additionalFilter
            ))
            ->setLimit($limit)
            ->setOrder(['STAFFING_DATE_FROM' => 'DESC', 'PROJECT_ID' => 'ASC'])
            ->fetchAll();

        $result = [];

        foreach ($data as $item)
        {
            $project = [
                'ID'                  => $item['PROJECT_ID'],
                'INDUSTRY_ID'         => $item['INDUSTRY_ID'],
                'INDUSTRY_COLOR'      => $industries[$item['INDUSTRY_ID']]['COLOR'],
                'INDUSTRY_NAME'       => $industries[$item['INDUSTRY_ID']]['NAME'],
                'FUNCTION_ID'         => $item['FUNCTION_ID'],
                'FUNCTION_COLOR'      => $functions[$item['FUNCTION_ID']]['COLOR'],
                'FUNCTION_NAME'       => $functions[$item['FUNCTION_ID']]['NAME'],
                'PROJECT_CLIENT'      => $item['PROJECT_CLIENT'],
                'PROJECT_NAME'        => $item['PROJECT_TITLE'],
                'PROJECT_ED'          => !empty($item['PROJECT_ED']) ? User::getUserNameById($item['PROJECT_ED']) : '',
                'PROJECT_DESCRIPTION' => $item['PROJECT_DESCRIPTION'],
                'PROJECT_LINK'        => Container::getInstance()->getRouter()->getItemDetailUrl(
                    Dynamic::getInstance()->getEntityTypeId(), $item['PROJECT_ID']
                ),
                'USER_ROLE'               => $item['USER_ROLE'],
                'USER_EMPLOYMENT_PERCENT' => $item['USER_EMPLOYMENT_PERCENT'],
            ];

            if (($item['STAFFING_DATE_FROM'] instanceof Date) && ($item['STAFFING_DATE_TO'] instanceof Date))
            {
                $project['WORK_DATE_START']  = $item['STAFFING_DATE_FROM']->format('d.m.Y');
                $project['WORK_DATE_FINISH'] = $item['STAFFING_DATE_TO']->format('d.m.Y');

                $project['WORK_DAYS_COUNT'] = (int)$item['STAFFING_DATE_TO']->getDiff($item['STAFFING_DATE_FROM'])->days + 1;
                $project['WEEKS_IN_PROJECT'] = round($project['WORK_DAYS_COUNT'] / 7);
            }
            else
            {
                $project['STAFFING_DATE_FROM'] = $project['WORK_DATE_START'] = '';
                $project['STAFFING_DATE_TO']   = $project['WORK_DATE_FINISH'] = '';
            }

            $result[] = $project;
        }

        return $result;
    }

    /**
     * @param int $userId
     * @return array
     * @throws \Exception
     */
    public static function calculateIndustriesPartData(int $userId): array
    {
        if (empty(static::$projects))
        {
            static::$projects = static::getUserProjectsData($userId);
        }

        $data = [];
        $totalDays = 0.01;
        foreach (static::$projects as $project)
        {
            if (!is_array($data[$project['INDUSTRY_ID']]))
            {
                if (empty($project['INDUSTRY_ID']))
                {
                    $project['INDUSTRY_NAME'] = 'Empty';
                    $project['INDUSTRY_COLOR'] = '#f5f5f5';
                }
                $data[$project['INDUSTRY_ID']] = [
                    'NAME'                    => $project['INDUSTRY_NAME'],
                    'COLOR'                   => $project['INDUSTRY_COLOR'],
                    'EMPLOYMENT_PERCENT'      => $project['USER_EMPLOYMENT_PERCENT'],
                    'WORK_DAYS'               => 0
                ];
            }

            $totalDays += round((int)$project['WORK_DAYS_COUNT']*(int)$project['USER_EMPLOYMENT_PERCENT'] / 100);
            $data[$project['INDUSTRY_ID']]['WORK_DAYS'] += round((int)$project['WORK_DAYS_COUNT']*(int)$project['USER_EMPLOYMENT_PERCENT'] / 100);
        }

        foreach ($data as $key => $item)
        {
            $data[$key]['PART']  = (round($item['WORK_DAYS'] / $totalDays, 2) * 100) . '%';
        }

        return $data;
    }

    /**
     * @param int $userId
     * @return array
     * @throws \Exception
     */
    public static function calculateFunctionsPartData(int $userId): array
    {
        if (empty(static::$projects))
        {
            static::$projects = static::getUserProjectsData($userId);
        }

        $data = [];
        $totalDays = 0.01;
        foreach (static::$projects as $project)
        {
            if (!is_array($data[$project['FUNCTION_ID']]))
            {
                if (empty($project['FUNCTION_ID']))
                {
                    $project['FUNCTION_NAME'] = 'Empty';
                    $project['FUNCTION_COLOR'] = '#f5f5f5';
                }

                $data[$project['FUNCTION_ID']] = [
                    'NAME'                    => $project['FUNCTION_NAME'],
                    'COLOR'                   => $project['FUNCTION_COLOR'],
                    'EMPLOYMENT_PERCENT'      => $project['USER_EMPLOYMENT_PERCENT'],
                    'WORK_DAYS'               => 0
                ];
            }

            $totalDays += round((int)$project['WORK_DAYS_COUNT']*(int)$project['USER_EMPLOYMENT_PERCENT'] / 100);
            $data[$project['FUNCTION_ID']]['WORK_DAYS'] += round((int)$project['WORK_DAYS_COUNT']*(int)$project['USER_EMPLOYMENT_PERCENT'] / 100);
        }

        foreach ($data as $key => $item)
        {
            $data[$key]['PART']  = (round($item['WORK_DAYS'] / $totalDays, 2) * 100) . '%';
        }

        return $data;
    }

    /**
     * @param int $userId
     * @param array $projectPartData
     * @return string
     * @throws \Exception
     */
    public static function calculateGeneralUserNetwork(int $userId, array $projectPartData = []): string
    {
        if (empty($projectPartData))
        {
            $projectPartData = static::calculateIndustriesPartData($userId);
        }

        $result = '';
        $mostDaysCount = 0;
        foreach ($projectPartData as $item)
        {
            if ($item['WORK_DAYS'] > $mostDaysCount)
            {
                $result = $item['NAME'];
                $mostDaysCount = $item['WORK_DAYS'];
            }
        }
        return (string)$result;
    }

    /**
     * TODO make logic for status "Learning"
     * @param int $userId
     */
    public static function updateUserAvailabilityStatus(int $userId): void
    {
        try
        {
            $isUserAbsent = CIntranetUtils::IsUserAbsent($userId);
            if ($isUserAbsent)
            {
                $status = UserField::getUfListIdByValue(
                    'UF_USER_AVAILABLE',
                    CoreConstants::USER_AVAILABILITY_STATUS_LOA
                );
            }
            else
            {
                $data = UserProjectTable::query()
                    ->setSelect([
                        'ID', 'USER_EMPLOYMENT_PERCENT', 'USER_EMPLOYMENT_TYPE',
                    ])
                    ->setFilter([
                        '=USER_ID' => $userId,
                        '!=DELETION_MARK' => 'Y',
                        '<=STAFFING_DATE_FROM' => new Date(),
                        '>=STAFFING_DATE_TO'   => new Date(),
                    ])
                    ->fetchAll();

                if (!empty($data))
                {
                    $status = UserField::getUfListIdByValue(
                        'UF_USER_AVAILABLE',
                        CoreConstants::USER_AVAILABILITY_STATUS_BEACH
                    );

                    foreach ($data as $item)
                    {
                        if ($item['USER_EMPLOYMENT_TYPE'] === Constants::STAFFING_EMPLOYMENT_TYPE_STAFF)
                        {
                            $status = UserField::getUfListIdByValue(
                                'UF_USER_AVAILABLE',
                                CoreConstants::USER_AVAILABILITY_STATUS_STAFFED
                            );
                            break;
                        }
                    }
                }
                else
                {
                    $status = UserField::getUfListIdByValue(
                        'UF_USER_AVAILABLE',
                        CoreConstants::USER_AVAILABILITY_STATUS_FREE
                    );
                }
            }

            $oUser = new CUser;
            $res = $oUser->Update($userId, ['UF_USER_AVAILABLE' => $status]);
            if (!$res)
            {
                throw new Exception($oUser->LAST_ERROR);
            }
        }
        catch (Throwable $e)
        {
            Logger::printToFile("Error on updating status of user $userId. " . $e->getMessage());
        }
    }

    /**
     * @param array $data
     * @return \Bitrix\Main\ORM\Data\AddResult
     * @throws \Exception
     */
    public static function addNeed(array $data): AddResult
    {
        if (empty($data['USER_EMPLOYMENT_PERCENT']))
        {
            throw new Exception("Employment percent is required.");
        }

        $start = ($data['NEEDLE_DATE_FROM'] instanceof Date) ? $data['NEEDLE_DATE_FROM'] :new Date($data['NEEDLE_DATE_FROM']);
        $end   = ($data['NEEDLE_DATE_TO'] instanceof Date) ? $data['NEEDLE_DATE_TO'] :new Date($data['NEEDLE_DATE_TO']);
        if ($start > $end)
        {
            throw new Exception("End date can't be less then begin date.");
        }

        return EmploymentNeedTable::add([
            'PROJECT_ID'              => $data['PROJECT_ID'],
            'USER_ROLE'               => $data['USER_ROLE'],
            'USER_EMPLOYMENT_PERCENT' => $data['USER_EMPLOYMENT_PERCENT'],
            'NEEDLE_DATE_FROM'        => $start,
            'NEEDLE_DATE_TO'          => $end,
        ]);
    }

    /**
     * @param int $id
     * @param array $data
     * @return \Bitrix\Main\Result
     * @throws \Exception
     */
    public static function updateNeed(int $id, array $data): Result
    {
        $updateData = [];

        if (!empty($data['USER_ROLE']))
        {
            $updateData['USER_ROLE'] = $data['USER_ROLE'];
        }

        if (!empty($data['NEEDLE_DATE_FROM']))
        {
            $updateData['NEEDLE_DATE_FROM'] = ($data['NEEDLE_DATE_FROM'] instanceof Date)
                                               ? $data['NEEDLE_DATE_FROM'] : new Date($data['NEEDLE_DATE_FROM']);
        }

        if (!empty($data['NEEDLE_DATE_TO']))
        {
            $updateData['NEEDLE_DATE_TO'] = ($data['NEEDLE_DATE_TO'] instanceof Date)
                ? $data['NEEDLE_DATE_TO'] : new Date($data['NEEDLE_DATE_TO']);
        }

        if ($updateData['NEEDLE_DATE_FROM'] > $updateData['NEEDLE_DATE_TO'])
        {
            throw new Exception("End date can't be less then begin date.");
        }

        if (!empty($data['USER_EMPLOYMENT_PERCENT']))
        {
            $updateData['USER_EMPLOYMENT_PERCENT'] = $data['USER_EMPLOYMENT_PERCENT'];
        }

        if (!empty($data['ACTIVE']))
        {
            $updateData['ACTIVE'] = $data['ACTIVE'];
        }

        //PROJECT_ID is readonly field

        return EmploymentNeedTable::update($id, $updateData);
    }

    /**
     * @param int $id
     * @return \Bitrix\Main\Result
     * @throws \Exception
     */
    public static function deleteNeed(int $id): Result
    {
        $item = EmploymentNeedTable::query()
            ->setSelect(['ID'])
            ->where('ID', '=' ,$id)
            ->fetch();

        if (!empty($item))
        {
            return EmploymentNeedTable::delete($id);
        }
        else
        {
            throw new Exception("Element with ID $id not found");
        }
    }
}