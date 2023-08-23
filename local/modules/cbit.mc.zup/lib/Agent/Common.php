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


namespace Cbit\Mc\Zup\Agent;


use Bitrix\Main\Config\Option;
use Bitrix\Main\Type;
use Cbit\Mc\Zup\Config\Constants;
use Cbit\Mc\Zup\Internals\Control\ServiceManager;
use Cbit\Mc\Zup\Internals\Debug\Logger;
use Cbit\Mc\Zup\Service\Integration\OneC\Reader;
use Cbit\Mc\Zup\Service\Integration\OneC\Writer;
use DateTime as PhpDateTime;
use Exception;
use Throwable;

/**
 * Class Common
 * @package Cbit\Mc\Zup\Agent
 */
class Common
{
    protected static string $logFile = Constants::PATH_TO_LOGFILE;

    /**
     * @return string
     */
    public static function updateEducationTypes(): string
    {
        try
        {
            $isoLastDateFrom = Option::get(ServiceManager::getModuleId(), Constants::OPTION_KEY_SYNC_LAST_GET_EDU_TYPES);

            if (!empty($isoLastDateFrom))
            {
                $lastDateFromUnix = Type\DateTime::createFromPhp(new PhpDateTime($isoLastDateFrom));
            }
            else
            {
                $lastDateFromUnix = null;
            }

            $reader = new Reader();
            $result = $reader->getEducationTypes($lastDateFromUnix);
            if ($result->isSuccess())
            {
                Option::set(
                    ServiceManager::getModuleId(),
                    Constants::OPTION_KEY_SYNC_LAST_GET_EDU_TYPES,
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
    public static function updateEmployeeEducation(): string
    {
        try
        {
            $isoLastDateFrom = Option::get(ServiceManager::getModuleId(), Constants::OPTION_KEY_SYNC_LAST_GET_EMP_EDU);

            if (!empty($isoLastDateFrom))
            {
                $lastDateFromUnix = Type\DateTime::createFromPhp(new PhpDateTime($isoLastDateFrom));
            }
            else
            {
                $lastDateFromUnix = null;
            }

            $reader = new Reader();
            $result = $reader->getEmployeeEducation($lastDateFromUnix);
            if ($result->isSuccess())
            {
                Option::set(
                    ServiceManager::getModuleId(),
                    Constants::OPTION_KEY_SYNC_LAST_GET_EMP_EDU,
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
    public static function sendEmployeeEducation(): string
    {
        try
        {
            $writer = new Writer();
            $result = $writer->sendEmployeeEducation();
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
    private static function logError(Throwable $e, string $method)
    {
        $code = !empty($e->getCode()) ? $e->getCode() : 0;
        Logger::writeToFile(
            "Code: $code. Description: " . $e->getMessage(),
            date("d.m.Y H:i:s") . ' ' . $method,
            static::$logFile
        );
    }
}