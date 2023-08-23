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
namespace Cbit\Mc\Expense\Internals\Installation;

use Bitrix\Main\Config\Option;
use Bitrix\Main\Error;
use Bitrix\Main\Localization\Loc;
use Bitrix\Main\Result;
use Bitrix\Main\SystemException;
use Cbit\Mc\Expense\Config\Constants;
use Cbit\Mc\Expense\Internals\Control\ServiceManager;
use Cbit\Mc\Expense\Service\Integration\Crm\Entity\EntityEditorConfig;

Loc::loadMessages(__FILE__);
/**
 * Class Installer
 * @package Cbit\Mc\Expense\Internals\Installation
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

        $ibResult = IBlockInstaller::install();
        if (!$ibResult->isSuccess())
        {
            $finalRes->addErrors($ibResult->getErrors());
        }

        $typeResult = TypeInstaller::install($customSectionResult->getId());
        if (!$typeResult->isSuccess())
        {
            $finalRes->addErrors($typeResult->getErrors());
        }
        else
        {
            $typeId = (int)$typeResult->getPrimary();
            if ($typeId <= 0)
            {
                $finalRes->addError(new Error('Error in '.__METHOD__.': typeId must be greater than 0'));
            }
            else
            {
                Option::set($moduleId, Constants::OPTION_KEY_DYNAMIC_TYPE_ID, $typeId);
                $entityTypeId = $typeResult->getData()['ENTITY_TYPE_ID'];
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
                    $categoryResult->addErrors($categoryResult->getErrors());
                }

                $cardConfigResult = EntityEditorConfig::setTypeCardConfig( $entityTypeId );
                if (!$cardConfigResult->isSuccess())
                {
                    $finalRes->addErrors($cardConfigResult->getErrors());
                }
            }
        }

        return $finalRes;
    }

    /**
     * @throws \Exception
     */
    public static function uninstallModule()
    {
        $result = TypeInstaller::uninstall();
        if (!$result->isSuccess())
        {
            throw new SystemException(implode("; ", $result->getErrorMessages()));
        }
        else
        {
            CustomSectionInstaller::uninstallCustomSection();
            DBTableInstaller::uninstall();
        }
    }
}