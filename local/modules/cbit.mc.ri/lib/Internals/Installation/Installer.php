<?php
/**
 * ==================================================
 * Developer: Alexey Nazarov
 * E-mail: alsnazarov@1cbit.ru
 * Copyright (c) 2019 - 2022
 * ==================================================
 * mc-portal - Installer.php
 * 17.01.2023 19:17
 * ==================================================
 */
namespace Cbit\Mc\RI\Internals\Installation;

use Bitrix\Main\Config\Option;
use Bitrix\Main\Localization\Loc;
use Bitrix\Main\Result;
use Bitrix\Main\SystemException;
use Cbit\Mc\RI\Config\Constants;
use Cbit\Mc\RI\Internals\Control\ServiceManager;
use Cbit\Mc\RI\Service\Integration\Crm\Entity\EntityEditorConfig;

Loc::loadMessages(__FILE__);
/**
 * Class Installer
 * @package Cbit\Mc\RI\Internals\Installation
 */
class Installer
{
    /**
     * @return \Bitrix\Main\Result
     * @throws \Exception
     */
    public static function installModule(): Result
    {
        $moduleId = ServiceManager::getModuleId();
        $finalRes = new Result();

        $customSectionResult = CustomSectionInstaller::installCustomSection();
        if (!$customSectionResult->isSuccess())
        {
            $finalRes->addErrors($customSectionResult->getErrors());
        }

        $typeResult = TypeInstaller::install($customSectionResult->getId());
        if (!$typeResult->isSuccess())
        {
            $finalRes->addErrors($typeResult->getErrors());
        }
        else
        {
            Option::set($moduleId, Constants::OPTION_KEY_DYNAMIC_TYPE_ID, (int)$typeResult->getPrimary());
            $entityTypeId = (int)$typeResult->getData()['ENTITY_TYPE_ID'];
            CustomSectionInstaller::installCustomPages($entityTypeId, $customSectionResult->getId());
            DBTableInstaller::install();

            $ufResult = UserFieldInstaller::install($entityTypeId);
            if (!$ufResult->isSuccess())
            {
                $finalRes->addErrors($ufResult->getErrors());
            }

            $categoryResult = CategoryInstaller::install($entityTypeId);
            if (!$categoryResult->isSuccess())
            {
                $finalRes->addErrors($categoryResult->getErrors());
            }

            $cardConfigResult = EntityEditorConfig::setTypeCardConfig($entityTypeId);
            if (!$cardConfigResult->isSuccess())
            {
                $finalRes->addErrors($cardConfigResult->getErrors());
            }
        }

        return $finalRes;
    }

    /**
     * @throws \Exception
     */
    public static function uninstallModule(): void
    {
        $result = TypeInstaller::uninstall();
        if (!$result->isSuccess())
        {
            throw new SystemException(implode("; ", $result->getErrorMessages()));
        }
        else
        {
            //Don't remove custom section because there are another smart processes
            //CustomSectionInstaller::uninstallCustomSection();
            DBTableInstaller::uninstall();
        }
    }
}