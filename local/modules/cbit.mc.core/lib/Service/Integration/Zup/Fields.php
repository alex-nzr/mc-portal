<?php
/**
 * ==================================================
 * Developer: Alexey Nazarov
 * E-mail: alsnazarov@1cbit.ru
 * Copyright (c) 2019 - 2022
 * ==================================================
 * mc-portal - Fields.php
 * 18.11.2022 13:32
 * ==================================================
 */


namespace Cbit\Mc\Core\Service\Integration\Zup;

use Bitrix\Main\Config\Option;
use Cbit\Mc\Core\Config\Constants;
use Cbit\Mc\Core\Internals\Control\ServiceManager;

/**
 * Class Fields
 * @package Cbit\Mc\Core\Service\Integration\Zup
 */
class Fields
{
    /**
     * @return string
     */
    public static function getTenureCompanyUfCode(): string
    {
        return Option::get(ServiceManager::getModuleId(), Constants::OPTION_KEY_TENURE_COMPANY_UF_CODE);
    }

    /**
     * @return string
     */
    public static function getTenurePositionUfCode(): string
    {
        return Option::get(ServiceManager::getModuleId(), Constants::OPTION_KEY_TENURE_POSITION_UF_CODE);
    }

    /**
     * @return string
     */
    public static function getAbsenceUfCode(): string
    {
        return Option::get(ServiceManager::getModuleId(), Constants::OPTION_KEY_ABSENCE_UF_CODE);
    }

    /**
     * @return string
     */
    public static function getZupStatusUfCode(): string
    {
        return Option::get(ServiceManager::getModuleId(), Constants::OPTION_KEY_ZUP_STATUS_UF_CODE);
    }

    /**
     * @return string
     */
    public static function getFioEnUfCode(): string
    {
        return Option::get(ServiceManager::getModuleId(), Constants::OPTION_KEY_FIO_EN_UF_CODE);
    }

    /**
     * @return string
     */
    public static function getFmnoUfCode(): string
    {
        return Option::get(ServiceManager::getModuleId(), Constants::OPTION_KEY_FMNO_UF_CODE);
    }

    /**
     * @return string
     */
    public static function getBasePerDiemUfCode(): string
    {
        return Option::get(ServiceManager::getModuleId(), Constants::OPTION_KEY_BASE_PER_DIEM_UF_CODE);
    }

    /**
     * @return string
     */
    public static function getPositionEnUfCode(): string
    {
        return Option::get(ServiceManager::getModuleId(), Constants::OPTION_KEY_POSITION_EN_UF_CODE);
    }

    /**
     * @return string
     */
    public static function getTrackUfCode(): string
    {
        return Option::get(ServiceManager::getModuleId(), Constants::OPTION_KEY_TRACK_UF_CODE);
    }

    /**
     * @return string
     */
    public static function getCspOspUfCode(): string
    {
        return Option::get(ServiceManager::getModuleId(), Constants::OPTION_KEY_CSP_UF_CODE);
    }

    /**
     * @return string
     */
    public static function getRatingUfCode(): string
    {
        return Option::get(ServiceManager::getModuleId(), Constants::OPTION_KEY_RATING_UF_CODE);
    }

    /**
     * @return string[]
     */
    public static function getFieldCodes(): array
    {
        return [
            static::getTenureCompanyUfCode(),
            static::getTenurePositionUfCode(),
            static::getAbsenceUfCode(),
            static::getZupStatusUfCode(),
            static::getFioEnUfCode(),
            static::getFmnoUfCode(),
            static::getBasePerDiemUfCode(),
            static::getPositionEnUfCode(),
            static::getTrackUfCode(),
            static::getCspOspUfCode(),
        ];
    }
}