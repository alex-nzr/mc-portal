<?php
/**
 * ==================================================
 * Developer: Alexey Nazarov
 * E-mail: alsnazarov@1cbit.ru
 * Copyright (c) 2019 - 2022
 * ==================================================
 * mc-portal - Common.php
 * 17.01.2023 12:30
 * ==================================================
 */


namespace Cbit\Mc\Staffing\Agent;


use Bitrix\Main\Config\Option;
use Bitrix\Main\Type;
use Bitrix\Main\UserTable;
use Cbit\Mc\Staffing\Config\Constants;
use Cbit\Mc\Staffing\Helper\Employment;
use Cbit\Mc\Staffing\Internals\Debug\Logger;
use Cbit\Mc\Staffing\Internals\Control\ServiceManager;
use Cbit\Mc\Staffing\Service\Integration\Timesheets\OneC\Reader;
use Cbit\Mc\Staffing\Service\Integration\Timesheets\OneC\Writer;
use DateTime as PhpDateTime;
use Exception;
use Throwable;

/**
 * Class Common
 * @package Cbit\Mc\Staffing\Agent
 */
class Common
{
    protected static string $logFile = Constants::PATH_TO_LOGFILE;

    /**
     * @return string
     */
    public static function updateProjectsData(): string
    {
        try
        {
            $isoLastDateFrom = Option::get(ServiceManager::getModuleId(), Constants::OPTION_KEY_SYNC_LAST_GET_PROJECTS);

            if (!empty($isoLastDateFrom))
            {
                $lastDateFromUnix = Type\DateTime::createFromPhp(new PhpDateTime($isoLastDateFrom));
            }
            else
            {
                $lastDateFromUnix = null;
            }

            $reader = new Reader();
            $result = $reader->loadProjectsFromOneC($lastDateFromUnix);
            if ($result->isSuccess())
            {
                Option::set(
                    ServiceManager::getModuleId(),
                    Constants::OPTION_KEY_SYNC_LAST_GET_PROJECTS,
                    (new Type\DateTime())->format('Y-m-d\TH:m:s')
                );
            }
            else
            {
                throw new Exception(implode('; ', $result->getErrorMessages()));
            }
        }
        catch (Throwable $e)
        {
            $method = __METHOD__;
            static::logError($e, $method);
        }

        return __METHOD__.'();';
    }

    /**
     * @return string
     */
    public static function updateAllUsersAvailabilityStatus(): string
    {
        try
        {
            @set_time_limit(0);
            $users = UserTable::query()
                ->setSelect(['ID'])
                ->setFilter([])
                ->fetchAll();

            foreach ($users as $user) {
                $id = (int)$user['ID'];
                if ($user > 0)
                {
                    Employment::updateUserAvailabilityStatus($id);
                }
            }
        }
        catch (Throwable $e)
        {
            $method = __METHOD__;
            static::logError($e, $method);
        }

        return __METHOD__.'();';
    }

    /**
     * @return string
     */
    public static function sendStaffingRecordsFromQueue(): string
    {
        try
        {
            @set_time_limit(0);
            $writer = new Writer();
            $result = $writer->sendNewStaffingData();
            if ($result->isSuccess())
            {
                throw new Exception(implode('; ', $result->getErrorMessages()));
            }
        }
        catch (Throwable $e)
        {
            $method = __METHOD__;
            static::logError($e, $method);
        }

        return __METHOD__.'();';
    }

    /**
     * @return string
     */
    public static function updateStaffingRecordsFromQueue(): string
    {
        try
        {
            @set_time_limit(0);
            $writer = new Writer();
            $result = $writer->updateStaffingData();
            if ($result->isSuccess())
            {
                throw new Exception(implode('; ', $result->getErrorMessages()));
            }
        }
        catch (Throwable $e)
        {
            $method = __METHOD__;
            static::logError($e, $method);
        }

        return __METHOD__.'();';
    }

    /**
     * @return string
     */
    public static function deleteStaffingRecordsFromQueue(): string
    {
        try
        {
            @set_time_limit(0);
            $writer = new Writer();
            $result = $writer->deleteStaffingData();
            if ($result->isSuccess())
            {
                throw new Exception(implode('; ', $result->getErrorMessages()));
            }
        }
        catch (Throwable $e)
        {
            $method = __METHOD__;
            static::logError($e, $method);
        }

        return __METHOD__.'();';
    }

    /**
     * @param \Throwable $e
     * @param string $method
     */
    private static function logError(Throwable $e, string $method): void
    {
        $code = !empty($e->getCode()) ? $e->getCode() : 0;
        Logger::writeToFile(
            "Code: $code. Description: " . $e->getMessage(),
            date("d.m.Y H:i:s") . ' ' . $method,
            static::$logFile
        );
    }
}