<?php
/**
 * ==================================================
 * Developer: Alexey Nazarov
 * E-mail: alsnazarov@1cbit.ru
 * Copyright (c) 2019 - 2022
 * ==================================================
 * mc-portal - Project.php
 * 14.12.2022 14:58
 * ==================================================
 */
namespace Cbit\Mc\Staffing\Helper;

use Bitrix\Main\Error;
use Bitrix\Main\Result;
use Bitrix\Main\Type\DateTime;
use Cbit\Mc\Core\Config\Configuration as CoreConfig;
use Cbit\Mc\Core\Helper\Iblock\IblockElement;
use Cbit\Mc\Core\Helper\Main\User;
use Cbit\Mc\Core\Helper\Main\UserField;
use Cbit\Mc\Staffing\Config\Constants;
use Cbit\Mc\Staffing\Entity\Dynamic;
use DateTime as PhpDateTime;
/**
 * Class Project
 * @package Cbit\Mc\Staffing\Helper
 */
class Project
{
    /**
     * @param array $item
     * @return \Bitrix\Main\Result
     * @throws \Exception
     */
    public static function processProjectItem(array $item): Result
    {
        $validationResult = static::validateProjectItemFields($item);
        if ($validationResult->isSuccess())
        {
            $typeId = Dynamic::getInstance()->getTypeId();
            $fields = [
                "TITLE" => $item['ChargeCode'],
                "XML_ID"=> $item['UUID'],
                "UF_CRM_" . $typeId . "_CHARGE_CODE" => $item['ChargeCode'],
                "UF_CRM_" . $typeId . "_DESCRIPTION" => $item['Description'],
                "UF_CRM_" . $typeId . "_ACTIVITY"    => IblockElement::getElementIdByXmlId(
                    CoreConfig::getInstance()->getActivitiesIBlockId(),
                    (string)$item['Activity']['UUID']
                ),

                "UF_CRM_" . $typeId . "_ED"               => User::getUserIdByFMNO((string)$item['ED']['FMNO']),
                "UF_CRM_" . $typeId . "_DURATION"         => (string)$item['Duration'],
                "UF_CRM_" . $typeId . "_LOCATION"         => $item['Location'],
                "UF_CRM_" . $typeId . "_MASTER_CLIENT"    => $item['MasterClient'],
                "UF_CRM_" . $typeId . "_CLIENT"           => $item['Client'],

                "UF_CRM_" . $typeId . "_ALLOW_STAFFING"   => (bool)$item['Activity']['AllowStaffing'],
                "UF_CRM_" . $typeId . "_ALLOW_EXPENSE"    => (bool)$item['Activity']['AllowExpenses'],
                "UF_CRM_" . $typeId . "_PSSS"             => (bool)$item['PSSS'],

                "UF_CRM_" . $typeId . "_EMPLOYMENT_TYPE"   => ($item['Activity']['AllowReStaffing'])
                    ? UserField::getUfListIdByValue(
                        "UF_CRM_" . $typeId . "_EMPLOYMENT_TYPE",
                        Constants::STAFFING_EMPLOYMENT_TYPE_BEACH
                    )
                    : UserField::getUfListIdByValue(
                        "UF_CRM_" . $typeId . "_EMPLOYMENT_TYPE",
                        Constants::STAFFING_EMPLOYMENT_TYPE_STAFF
                    ) ,
            ];

            if (!empty($item['Industrie']))
            {
                $fields["UF_CRM_" . $typeId . "_INDUSTRY"] = IblockElement::getElementIdByXmlId(
                    CoreConfig::getInstance()->getIndustriesIblockId(),
                    (string)$item['Industrie']['UUID']
                );
            }

            if (!empty($item['Function']))
            {
                $fields["UF_CRM_" . $typeId . "_FUNCTION"] = IblockElement::getElementIdByXmlId(
                    CoreConfig::getInstance()->getFunctionsIBlockId(),
                    (string)$item['Function']['UUID']
                );
            }

            if (!empty($item['Phase']))
            {
                $fields["UF_CRM_" . $typeId . "_PHASE"] = IblockElement::getElementIdByXmlId(
                    CoreConfig::getInstance()->getProjectPhasesIBlockId(),
                    (string)$item['Phase']['UUID']
                );
            }

            if (!empty($item['State']))
            {
                $fields["UF_CRM_" . $typeId . "_STATE"] = IblockElement::getElementIdByXmlId(
                    CoreConfig::getInstance()->getProjectStatesIBlockId(),
                    (string)$item['State']['UUID']
                );
            }

            if (!empty($item['TeamComposition']))
            {
                $fields["UF_CRM_" . $typeId . "_TEAM_COMPOSITION"] = IblockElement::getElementIdByXmlId(
                    CoreConfig::getInstance()->getTeamCompositionsIBlockId(),
                    (string)$item['TeamComposition']['UUID']
                );
            }

            if (!empty($item['StartDate']))
            {
                $fields["UF_CRM_" . $typeId . "_START_DATE"] = DateTime::createFromPhp(new PhpDateTime($item['StartDate']));
            }

            if (!empty($item['EndDate']))
            {
                $fields["UF_CRM_" . $typeId . "_END_DATE"] = DateTime::createFromPhp(new PhpDateTime($item['EndDate']));
            }

            if (!empty($item['DiscussionDate']))
            {
                $fields["UF_CRM_" . $typeId . "_DISCUSSION_DATE"] = DateTime::createFromPhp(new PhpDateTime($item['DiscussionDate']));
            }
            if (!empty($item['DevelopmentDate']))
            {
                $fields["UF_CRM_" . $typeId . "_DEVELOPMENT_DATE"] = DateTime::createFromPhp(new PhpDateTime($item['DevelopmentDate']));
            }
            if (!empty($item['ConfirmedDate']))
            {
                $fields["UF_CRM_" . $typeId . "_CONFIRMED_DATE"] = DateTime::createFromPhp(new PhpDateTime($item['ConfirmedDate']));
            }
            if (!empty($item['FinishedOrOutDate']))
            {
                $fields["UF_CRM_" . $typeId . "_FINISH_OR_OUT_DATE"] = DateTime::createFromPhp(new PhpDateTime($item['FinishedOrOutDate']));
            }

            $existsItem = Dynamic::getInstance()->getByXmlId($item['UUID']);
            if (!empty($existsItem))
            {
                return Dynamic::getInstance()->update($existsItem, $fields);
            }
            else
            {
                return Dynamic::getInstance()->add($fields);
            }
        }
        else
        {
            return $validationResult;
        }
    }

    /**
     * @param array $item
     * @return \Bitrix\Main\Result
     */
    private static function validateProjectItemFields(array $item): Result
    {
        $requiredFields = [
            'UUID', 'ChargeCode', 'Description', 'Activity', 'ED',
            //'Phase', 'State', 'Industrie', 'Function', 'TeamComposition', 'StartDate', 'EndDate',
        ];

        $result = new Result();
        foreach ($requiredFields as $field)
        {
            if (empty($item[$field]))
            {
                $result->addError(new Error("Field '$field' is required"));
            }
        }
        return $result;
    }
}