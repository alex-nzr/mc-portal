<?php
/**
 * ==================================================
 * Developer: Alexey Nazarov
 * E-mail: alsnazarov@1cbit.ru
 * Copyright (c) 2019 - 2022
 * ==================================================
 * mc-portal - UserField.php
 * 25.11.2022 21:12
 * ==================================================
 */


namespace Cbit\Mc\Core\Helper\Main;

use Bitrix\Main\Error;
use Bitrix\Main\Result;
use CUserFieldEnum;
use CUserTypeEntity;

/**
 * Class UserField
 * @package Cbit\Mc\Core\Helper\Main
 */
class UserField
{
    /**
     * @param array $userFields
     * @param string $entityIdForUf
     * @param string $ufPrefix
     * @return array
     */
    public static function prepareUserFieldsData(array $userFields, string $entityIdForUf, string $ufPrefix): array
    {
        $preparedUserFields = [];
        foreach ($userFields as $key => $userField) {

            $userField['ENTITY_ID']     = $entityIdForUf;
            $userField['FIELD_NAME']    = $ufPrefix . $userField['FIELD_NAME'];
            $userField['XML_ID']        = $userField['FIELD_NAME'];
            $userField['SORT']          = (int)$key > 0 ? (int)$key * 10 : 10;
            $userField['SHOW_IN_LIST']  = 'N';//($userField['HIDDEN'] === 'Y') ? 'N' : 'Y';
            $userField['IS_SEARCHABLE'] = 'Y';

            if ($userField['HIDDEN'] === 'Y'){
                $userField['SHOW_FILTER'] = 'N';
            }
            else{
                $userField['SHOW_FILTER'] = ($userField['USER_TYPE_ID'] === 'string') ? 'S' : 'I';
            }

            $title = [
                'ru'    => $userField['TITLE_RU'],
                'en'    => $userField['TITLE_EN'],
            ];

            $userField['EDIT_FORM_LABEL'] = $title;
            $userField['LIST_COLUMN_LABEL'] = $title;
            $userField['LIST_FILTER_LABEL'] = $title;
            $userField['ERROR_MESSAGE']   = [
                'ru'    => 'ERROR ON FILLING ' . $userField['TITLE_RU'],
                'en'    => 'ERROR ON FILLING ' . $userField['TITLE_EN'],
            ];
            $userField['HELP_MESSAGE']   = ['ru'    => '', 'en'    => ''];

            unset($userField['TITLE_RU'], $userField['TITLE_EN'], $userField['HIDDEN']);

            if (is_array($userField['LIST']))
            {
                $userField['LIST'] = static::prepareUserFieldEnumData($userField['FIELD_NAME'], $userField['LIST']);
            }

            $preparedUserFields[] = $userField;
        }
        return $preparedUserFields;
    }

    /**
     * @param string $fieldName
     * @param array $values
     * @return array
     */
    public static function prepareUserFieldEnumData(string $fieldName, array $values): array
    {
        $arAddEnum = [];
        $counter = 0;
        foreach ($values as $xmlId => $value)
        {
            if (empty($xmlId) || (intval($xmlId) === $counter))
            {
                $xmlId = $fieldName.'_'.$counter;
            }
            $arAddEnum['n'.$counter] = [
                'XML_ID' => $xmlId,
                'VALUE' => $value,
                'DEF' => 'N',
                'SORT' => $counter > 0 ? $counter * 10 : 10
            ];
            $counter++;
        }
        return $arAddEnum;
    }

    /**
     * @param array $fieldsData
     * @return \Bitrix\Main\Result
     */
    public static function setupUserFields(array $fieldsData): Result
    {
        global $APPLICATION;
        $result = new Result;
        $oUserTypeEntity = new CUserTypeEntity();

        $newFields = [];
        foreach ($fieldsData as $userField)
        {
            $ufRes = $oUserTypeEntity::GetList([], ['FIELD_NAME' => $userField['FIELD_NAME']]);
            if ($arField = $ufRes->Fetch())
            {
                $ufId = $arField['ID'];
                $updated = $oUserTypeEntity->Update($ufId, $userField);
                if (!$updated){
                    $result->addError(new Error($userField['FIELD_NAME'] . " - " . $APPLICATION->LAST_ERROR));
                }
                else
                {
                    if ($userField['USER_TYPE_ID'] === 'enumeration' && is_array($userField['LIST']))
                    {
                        $currentXmlIds = static::getUfListXmlIdsByFieldId((int)$arField['ID']);

                        foreach ($userField['LIST'] as $key => $valueAr)
                        {
                            if (in_array($valueAr['XML_ID'], $currentXmlIds))
                            {
                                $enumId = array_search($valueAr['XML_ID'], $currentXmlIds);
                                $userField['LIST'][$enumId] = $valueAr;
                                unset(
                                    $currentXmlIds[array_search($valueAr['XML_ID'], $currentXmlIds)],
                                    $userField['LIST'][$key]
                                );
                            }
                        }

                        if (count($currentXmlIds) > 0)
                        {
                            foreach ($currentXmlIds as $enumId => $xmlId)
                            {
                                $userField['LIST'][$enumId] = [
                                    "DEL" => "Y",
                                ];
                            }
                        }

                        $obEnum = new CUserFieldEnum;
                        $enumSuccess = $obEnum->SetEnumValues($ufId, $userField['LIST']);
                        if(!$enumSuccess){
                            $result->addError(new Error($userField['FIELD_NAME'] . " - " . $APPLICATION->LAST_ERROR));
                        }
                    }
                }
            }
            else
            {
                $newFields[] = $userField;
            }
        }

        if (!empty($newFields))
        {
            $addRes = static::addUserFields($newFields);
            if(!$addRes->isSuccess()){
                $result->addErrors($addRes->getErrors());
            }
        }

        return $result;
    }

    /**
     * @param array $userFields
     * @return \Bitrix\Main\Result
     */
    public static function addUserFields(array $userFields): Result
    {
        global $APPLICATION;
        $result = new Result;
        $oUserTypeEntity = new CUserTypeEntity();

        foreach ($userFields as $userField)
        {
            $ufId   = $oUserTypeEntity->Add($userField);
            if (!(int)$ufId > 0){
                $result->addError(new Error($userField['FIELD_NAME'] . " - " . $APPLICATION->LAST_ERROR));
            }
            else
            {
                if ($userField['USER_TYPE_ID'] === 'enumeration' && is_array($userField['LIST']))
                {
                    $obEnum = new CUserFieldEnum;
                    $enumSuccess = $obEnum->SetEnumValues($ufId, $userField['LIST']);
                    if(!$enumSuccess){
                        $result->addError(new Error($userField['FIELD_NAME'] . " - " . $APPLICATION->LAST_ERROR));
                    }
                }
            }
        }

        return $result;
    }

    /**
     * @param int $userFieldId
     * @return array
     */
    public static function getUfListValuesByFieldId(int $userFieldId): array
    {
        $filter = [
            "USER_FIELD_ID" => $userFieldId
        ];
        $userField = CUserFieldEnum::GetList([], $filter);

        $values = [];
        while($userFieldAr = $userField->GetNext())
        {
            $values[$userFieldAr["ID"]] = $userFieldAr["VALUE"];
        }
        return $values;
    }

    /**
     * @param $id
     * @return string
     */
    public static function getUfListValueById($id): string
    {
        $value = '';
        if (!empty($id))
        {
            $userField = CUserFieldEnum::GetList([], ["ID" => (int)$id]);
            if($userFieldAr = $userField->GetNext())
            {
                $value =  $userFieldAr["VALUE"];
            }
        }
        return $value;
    }

    /**
     * @param string $userFieldCode
     * @param $value
     * @return int|null
     */
    public static function getUfListIdByValue(string $userFieldCode, $value): ?int
    {
        $id = null;

        if (!empty($value))
        {
            $filter = [
                "VALUE" => $value,
                "USER_FIELD_ID" => static::getUserFieldIdByCode($userFieldCode)
            ];
            $userField = CUserFieldEnum::GetList([], $filter);

            if($userFieldAr = $userField->GetNext())
            {
                $id = (int)$userFieldAr["ID"];
            }
        }
        return $id;
    }

    /**
     * @param string $userFieldCode
     * @return array
     */
    public static function getUfListValuesByCode(string $userFieldCode): array
    {
        $filter = [
            "USER_FIELD_ID" => static::getUserFieldIdByCode($userFieldCode)
        ];
        $userField = CUserFieldEnum::GetList([], $filter);

        $values = [];
        while($userFieldAr = $userField->GetNext())
        {
            $values[$userFieldAr["ID"]] = $userFieldAr["VALUE"];
        }
        return $values;
    }

    /**
     * @param string $code
     * @return int|null
     */
    public static function getUserFieldIdByCode(string $code): ?int
    {
        $rsData = CUserTypeEntity::GetList([], ['FIELD_NAME' => $code]);
        if($arRes = $rsData->Fetch())
        {
            return (int)$arRes['ID'];
        }
        return null;
    }

    /**
     * @param int $userFieldId
     * @return array
     */
    public static function getUfListXmlIdsByFieldId(int $userFieldId): array
    {
        $filter = [
            "USER_FIELD_ID" => $userFieldId
        ];
        $userField = CUserFieldEnum::GetList([], $filter);

        $values = [];
        while($userFieldAr = $userField->GetNext())
        {
            $values[$userFieldAr["ID"]] = $userFieldAr["XML_ID"];
        }
        return $values;
    }
}