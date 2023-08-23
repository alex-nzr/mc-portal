<?php
/**
 * ==================================================
 * Developer: Alexey Nazarov
 * E-mail: alsnazarov@1cbit.ru
 * Copyright (c) 2019 - 2022
 * ==================================================
 * mc-portal - Common.php
 * 21.11.2022 12:30
 * ==================================================
 */
namespace Cbit\Mc\Timesheets\Agent;

use Bitrix\Main\Config\Option;
use Bitrix\Main\Type;
use Cbit\Mc\Timesheets\Config\Constants;
use Cbit\Mc\Timesheets\Internals\Control\ServiceManager;
use Cbit\Mc\Timesheets\Internals\Debug\Logger;
use Cbit\Mc\Timesheets\Service\Integration\OneC\Reader;
use DateTime as PhpDateTime;
use Exception;
use Throwable;

/**
 * Class Common
 * @package Cbit\Mc\Timesheets\Agent
 */
class Common
{
    protected static string $logFile = Constants::PATH_TO_LOGFILE;

    /**
     * @return string
     */
    public static function updateActivitiesRegistry(): string
    {
        try
        {
            $isoLastDateFrom = Option::get(ServiceManager::getModuleId(), Constants::OPTION_KEY_SYNC_LAST_GET_ACTIVITIES);

            if (!empty($isoLastDateFrom))
            {
                $lastDateFromUnix = Type\DateTime::createFromPhp(new PhpDateTime($isoLastDateFrom));
            }
            else
            {
                $lastDateFromUnix = null;
            }

            $reader = new Reader();
            $result = $reader->getActivitiesRegistry($lastDateFromUnix);
            if ($result->isSuccess())
            {
                Option::set(
                    ServiceManager::getModuleId(),
                    Constants::OPTION_KEY_SYNC_LAST_GET_ACTIVITIES,
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
    public static function updateIndustriesRegistry(): string
    {
        try
        {
            $isoLastDateFrom = Option::get(ServiceManager::getModuleId(), Constants::OPTION_KEY_SYNC_LAST_GET_INDUSTRIES);

            if (!empty($isoLastDateFrom))
            {
                $lastDateFromUnix = Type\DateTime::createFromPhp(new PhpDateTime($isoLastDateFrom));
            }
            else
            {
                $lastDateFromUnix = null;
            }

            $reader = new Reader();
            $result = $reader->getIndustriesRegistry($lastDateFromUnix);
            if ($result->isSuccess())
            {
                Option::set(
                    ServiceManager::getModuleId(),
                    Constants::OPTION_KEY_SYNC_LAST_GET_INDUSTRIES,
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
    public static function updateFunctionsRegistry(): string
    {
        try
        {
            $isoLastDateFrom = Option::get(ServiceManager::getModuleId(), Constants::OPTION_KEY_SYNC_LAST_GET_FUNCTIONS);

            if (!empty($isoLastDateFrom))
            {
                $lastDateFromUnix = Type\DateTime::createFromPhp(new PhpDateTime($isoLastDateFrom));
            }
            else
            {
                $lastDateFromUnix = null;
            }

            $reader = new Reader();
            $result = $reader->getFunctionsRegistry($lastDateFromUnix);
            if ($result->isSuccess())
            {
                Option::set(
                    ServiceManager::getModuleId(),
                    Constants::OPTION_KEY_SYNC_LAST_GET_FUNCTIONS,
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
    public static function updateTeamCompositionsRegistry(): string
    {
        try
        {
            $isoLastDateFrom = Option::get(ServiceManager::getModuleId(), Constants::OPTION_KEY_SYNC_LAST_GET_TEAM_COMP);

            if (!empty($isoLastDateFrom))
            {
                $lastDateFromUnix = Type\DateTime::createFromPhp(new PhpDateTime($isoLastDateFrom));
            }
            else
            {
                $lastDateFromUnix = null;
            }

            $reader = new Reader();
            $result = $reader->getTeamCompositionsRegistry($lastDateFromUnix);
            if ($result->isSuccess())
            {
                Option::set(
                    ServiceManager::getModuleId(),
                    Constants::OPTION_KEY_SYNC_LAST_GET_TEAM_COMP,
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
    public static function updateEnumerationsData(): string
    {
        try
        {
            $reader = new Reader();
            $result = $reader->getEnumerationsData();
            if (!$result->isSuccess())
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