<?php
/**
 * ==================================================
 * Developer: Alexey Nazarov
 * E-mail: alsnazarov@1cbit.ru
 * Copyright (c) 2019 - 2022
 * ==================================================
 * mc-portal - Installer.php
 * 10.11.2022 21:17
 * ==================================================
 */


namespace Cbit\Mc\Profile\Internals\Installation;

use Bitrix\Main\Error;
use Bitrix\Main\Result;
use Cbit\Mc\Core\Service\Integration\Zup\Fields;
use CUserTypeEntity;

/**
 * Class Installer
 * @package Cbit\Mc\Profile\Internals\Installation
 */
class Installer
{
    /**
     * @return \Bitrix\Main\Result
     * @throws \Exception
     */
    public static function installModule(): Result
    {
        $finalRes = new Result();

        $ufResult = UserFieldInstaller::install();
        if (!$ufResult->isSuccess())
        {
            $finalRes->addErrors($ufResult->getErrors());
        }

        $zupUfResult = static::updateZupUserFields(Fields::getFieldCodes());
        if (!$zupUfResult->isSuccess())
        {
            $finalRes->addErrors($zupUfResult->getErrors());
        }

        return $finalRes;
    }

    /**
     * @param array $getFieldCodes
     * @return \Bitrix\Main\Result
     */
    private static function updateZupUserFields(array $getFieldCodes): Result
    {
        global $APPLICATION;
        $result = new Result;
        $oUserTypeEntity = new CUserTypeEntity();

        foreach ($getFieldCodes as $getFieldCode)
        {
            $ufRes = $oUserTypeEntity::GetList([], ['FIELD_NAME' => $getFieldCode]);
            if ($arField = $ufRes->Fetch())
            {
                $ufId = $arField['ID'];
                $updated = $oUserTypeEntity->Update($ufId, [
                    'EDIT_IN_LIST' => 'N'
                ]);
                if (!$updated)
                {
                    $result->addError(new Error($getFieldCode . " - " . $APPLICATION->LAST_ERROR));
                }
            }
        }

        return $result;
    }
}