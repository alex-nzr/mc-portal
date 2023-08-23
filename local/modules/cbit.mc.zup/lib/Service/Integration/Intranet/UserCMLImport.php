<?php
/**
 * ==================================================
 * Developer: Alexey Nazarov
 * E-mail: alsnazarov@1cbit.ru
 * Copyright (c) 2019 - 2023
 * ==================================================
 * mc-portal - UserCMLImport.php
 * 09.01.2023 16:45
 * ==================================================
 */

namespace Cbit\Mc\Zup\Service\Integration\Intranet;

use Bitrix\Iblock\PropertyEnumerationTable;
use Bitrix\Main\Config\Option;
use Bitrix\Main\Localization\Loc;
use Bitrix\Main\UserTable;
use Cbit\Mc\Core\Service\Integration\Zup\Fields;
use Cbit\Mc\Zup\Config\Constants;
use Cbit\Mc\Zup\Internals\Control\ServiceManager;
use Cbit\Mc\Zup\Internals\Debug\Logger;
use CFile;
use CIBlockElement;
use CIBlockPropertyEnum;
use CIBlockSection;
use CUserCMLImport;
use CUserTypeEntity;
use Throwable;

class UserCMLImport extends CUserCMLImport
{
    protected $STATE_HISTORY_IBLOCK_ID;

    /**
     * @param $arXMLElement
     * @param $counter
     * @return int|null
     */
    public function LoadUser($arXMLElement, &$counter): ?int
    {
        try {
            static $USER_COUNTER = null;

            static $property_state_final = 0;

            if (!is_array($property_state_final)) {
                $property_state_final = array();
                $property_state = CIBlockPropertyEnum::GetList(
                    [],
                    [
                        "IBLOCK_ID" => $this->STATE_HISTORY_IBLOCK_ID,
                        "CODE" => "STATE"
                    ]
                );
                while ($property_state_enum = $property_state->GetNext()) {
                    $property_state_final[strtolower($property_state_enum["VALUE"])] = $property_state_enum["ID"];
                }
            }

            $obUser = &$this->__user;

            // this counter will be used for generating users login name
            if ($USER_COUNTER === null) {
                $lastUser = UserTable::query()
                    ->setSelect(['ID'])
                    ->setOrder(['ID' => 'DESC'])
                    ->setLimit(1)
                    ->fetch();

                $USER_COUNTER = is_array($lastUser) ? (int)$lastUser['ID'] : 0;
            }

            // check user existence
            $fmno = $this->getFMNOFromXmlElement($arXMLElement);
            $arCurrentUser = $this->getUserByFMNO($fmno);

            $existingUserId = (int)$arCurrentUser['ID'];

            if (0 >= $existingUserId) {
                // ZUP can not create new users
                return null;
            }

            // common user data
            $arFields = array(
                'ACTIVE' => $arXMLElement[GetMessage('IBLOCK_XML2_USER_TAG_STATUS')] == GetMessage('IBLOCK_XML2_USER_VALUE_DELETED') ? 'N' : 'Y',
                'UF_1C' => 'Y',
                'XML_ID' => $arXMLElement[GetMessage('IBLOCK_XML2_USER_TAG_ID')],
                'LID' => $this->arParams['SITE_ID'],
                'LAST_NAME' => $arXMLElement[GetMessage('IBLOCK_XML2_USER_TAG_LAST_NAME')],
                'NAME' => $arXMLElement[GetMessage('IBLOCK_XML2_USER_TAG_FIRST_NAME')],
                'SECOND_NAME' => $arXMLElement[GetMessage('IBLOCK_XML2_USER_TAG_SECOND_NAME')],
                'PERSONAL_BIRTHDAY' => !empty($arXMLElement[GetMessage('IBLOCK_XML2_USER_TAG_BIRTH_DATE')]) ? ConvertTimeStamp(MakeTimeStamp($arXMLElement[GetMessage('IBLOCK_XML2_USER_TAG_BIRTH_DATE')], 'YYYY-MM-DD')) : '',
                'PERSONAL_GENDER' => $arXMLElement[GetMessage('IBLOCK_XML2_USER_TAG_GENDER')] == GetMessage('IBLOCK_XML2_USER_VALUE_FEMALE') ? 'F' : 'M',
                'UF_INN' => $arXMLElement[GetMessage('IBLOCK_XML2_USER_TAG_INN')],
                'WORK_POSITION' => $arXMLElement[GetMessage('IBLOCK_XML2_USER_TAG_POST')],
                'PERSONAL_PROFESSION' => $arXMLElement[GetMessage('IBLOCK_XML2_USER_TAG_POST')],
            );

            if (array_key_exists(GetMessage('IBLOCK_XML2_USER_TAG_PHOTO'), $arXMLElement)) {
                if ($arCurrentUser['PERSONAL_PHOTO'] > 0) {
                    CFile::Delete($arCurrentUser['PERSONAL_PHOTO']);
                }

                if ($arXMLElement[GetMessage('IBLOCK_XML2_USER_TAG_PHOTO')] <> '') {
                    $arFields['PERSONAL_PHOTO'] = $this->MakeFileArray($arXMLElement[GetMessage('IBLOCK_XML2_USER_TAG_PHOTO')]);
                }
            }

            // address fields
            if (is_array($arXMLElement[GetMessage('IBLOCK_XML2_USER_TAG_ADDRESS')])) {
                foreach ($arXMLElement[GetMessage('IBLOCK_XML2_USER_TAG_ADDRESS')] as $key => $arAddressField) {
                    if (GetMessage('IBLOCK_XML2_USER_TAG_FULLADDRESS') == $key)
                        $arFields['PERSONAL_STREET'] = $arAddressField;
                    else {
                        $type = $arAddressField[GetMessage('IBLOCK_XML2_USER_TAG_TYPE')];
                        $value = $arAddressField[GetMessage('IBLOCK_XML2_USER_TAG_VALUE')];
                        switch ($type) {
                            case GetMessage('IBLOCK_XML2_USER_VALUE_ZIP'):
                                $arFields['PERSONAL_ZIP'] = $value;
                                break;
                            case GetMessage('IBLOCK_XML2_USER_VALUE_STATE'):
                                $arFields['PERSONAL_STATE'] = $value;
                                break;
                            case GetMessage('IBLOCK_XML2_USER_VALUE_DISTRICT'):
                                $arFields['UF_DISTRICT'] = $value;
                                break;
                            case GetMessage('IBLOCK_XML2_USER_VALUE_CITY1'):
                            case GetMessage('IBLOCK_XML2_USER_VALUE_CITY2'):
                                if ($arFields['PERSONAL_CITY'])
                                    $arFields['PERSONAL_CITY'] .= ', ';
                                $arFields['PERSONAL_CITY'] .= $value;
                                break;
                            default:
                                break;
                        }
                    }
                }
            }

            // contact fields
            if (is_array($arXMLElement[GetMessage('IBLOCK_XML2_USER_TAG_CONTACTS')])) {
                foreach ($arXMLElement[GetMessage('IBLOCK_XML2_USER_TAG_CONTACTS')] as $arContactsField) {
                    $type = $arContactsField[GetMessage('IBLOCK_XML2_USER_TAG_TYPE')];
                    $value = $arContactsField[GetMessage('IBLOCK_XML2_USER_TAG_VALUE')];
                    switch ($type) {
                        case GetMessage('IBLOCK_XML2_USER_VALUE_PHONE_INNER'):
                            $arFields['UF_PHONE_INNER'] = $value;
                            break;
                        case GetMessage('IBLOCK_XML2_USER_VALUE_PHONE_WORK'):
                            $arFields['WORK_PHONE'] = $value;
                            break;
                        case GetMessage('IBLOCK_XML2_USER_VALUE_PHONE_MOBILE'):
                            $arFields['PERSONAL_MOBILE'] = $value;
                            break;
                        case GetMessage('IBLOCK_XML2_USER_VALUE_PHONE_PERSONAL'):
                            $arFields['PERSONAL_PHONE'] = $value;
                            break;
                        case GetMessage('IBLOCK_XML2_USER_VALUE_PAGER'):
                            $arFields['PERSONAL_PAGER'] = $value;
                            break;
                        case GetMessage('IBLOCK_XML2_USER_VALUE_FAX'):
                            $arFields['PERSONAL_FAX'] = $value;
                            break;
                        case GetMessage('IBLOCK_XML2_USER_VALUE_EMAIL'):
                            $arFields['EMAIL'] = $value; // b_user.EMAIL
                            break;
                        case GetMessage('IBLOCK_XML2_USER_VALUE_ICQ'):
                            $arFields['PERSONAL_ICQ'] = $value;
                            break;
                        case GetMessage('IBLOCK_XML2_USER_VALUE_WWW'):
                            $arFields['PERSONAL_WWW'] = $value;
                            break;
                        default:
                            break;
                    }
                }
            }

            //departments data
            $arFields['UF_DEPARTMENT'] = array();
            if (is_array($arXMLElement[GetMessage('IBLOCK_XML2_USER_TAG_DEPARTMENTS')])) {
                foreach ($arXMLElement[GetMessage('IBLOCK_XML2_USER_TAG_DEPARTMENTS')] as $DEPT_XML_ID) {
                    $DEPT_ID = $this->GetSectionByXML_ID($this->DEPARTMENTS_IBLOCK_ID, $DEPT_XML_ID);
                    if ($DEPT_ID) {
                        $arFields['UF_DEPARTMENT'][] = $DEPT_ID;
                    }
                }
            }

            // state history
            if (is_array($arXMLElement[GetMessage('IBLOCK_XML2_USER_TAG_STATE_HISTORY')])) {
                $last_state_date = 0;
                $first_state_date = 1767132000;
                $arStateHistory = array();

                foreach ($arXMLElement[GetMessage('IBLOCK_XML2_USER_TAG_STATE_HISTORY')] as $arState) {
                    $state = $arState[GetMessage('IBLOCK_XML2_USER_TAG_VALUE')];

                    $date = intval(MakeTimeStamp($arState[GetMessage('IBLOCK_XML2_USER_TAG_DATE')], 'YYYY-MM-DD'));
                    while (is_array($arStateHistory[$date]))
                        $date++;

                    if (!$last_state_date || doubleval($last_state_date) < doubleval($date))
                        $last_state_date = $date;
                    if (doubleval($first_state_date) > doubleval($date))
                        $first_state_date = $date;

                    $DEPARTMENT_ID = $this->GetSectionByXML_ID($this->DEPARTMENTS_IBLOCK_ID, $arState[GetMessage('IBLOCK_XML2_USER_TAG_DEPARTMENT')]);

                    $arStateHistory[$date] = array(
                        'STATE' => $state,
                        'POST' => $arState[GetMessage('IBLOCK_XML2_USER_TAG_POST')],
                        'DEPARTMENT' => $DEPARTMENT_ID,
                    );
                }

                ksort($arStateHistory);

                // if person's last state is "Fired" - deactivate him.
                if (GetMessage('IBLOCK_XML2_USER_VALUE_FIRED') == $arStateHistory[$last_state_date]['STATE'])
                    $arFields['ACTIVE'] = 'N';
                // save data serialized
                //$arFields['UF_1C_STATE_HISTORY'] = serialize($arStateHistory);
            } else {
                $arStateHistory = array();
            }

            // properties data
            if (is_array($arXMLElement[GetMessage('IBLOCK_XML2_USER_TAG_PROPERTY_VALUES')])) {
                foreach ($arXMLElement[GetMessage('IBLOCK_XML2_USER_TAG_PROPERTY_VALUES')] as $arPropertyData) {
                    $PROP_XML_ID = $arPropertyData[GetMessage('IBLOCK_XML2_USER_TAG_ID')];
                    $PROP_VALUE = $arPropertyData[GetMessage('IBLOCK_XML2_USER_TAG_VALUE')];
                    $arFields[$this->CalcPropertyFieldName($PROP_XML_ID)] = $PROP_VALUE;
                }
            }

            foreach ($arFields as $key => $value) {
                if ($key !== 'ACTIVE' && $key !== 'UF_DEPARTMENT' && !in_array($key, $this->arParams['UPDATE_PROPERTIES'])) {
                    unset($arFields[$key]);
                }
            }

            // update existing user
            $res = $obUser->Update($existingUserId, $arFields);
            if ($res) {
                $counter[$arFields['ACTIVE'] == 'Y' ? 'UPD' : 'DEA']++;

                if (isset($this->next_step['_TEMPORARY']['DEPARTMENT_HEADS'][$arFields['XML_ID']])) {
                    $obSection = new CIBlockSection();
                    foreach ($this->next_step['_TEMPORARY']['DEPARTMENT_HEADS'][$arFields['XML_ID']] as $dpt) {
                        $obSection->Update($dpt, array('UF_HEAD' => $existingUserId), false, false);
                    }
                }

                if (is_array($arStateHistory) && count($arStateHistory) > 0) {
                    if (null == $this->__ib)
                        $this->__ib = new CIBlockElement();

                    $dbRes = $this->__ib->GetList(
                        array(),
                        array(
                            'PROPERTY_USER' => $existingUserId,
                            'IBLOCK_ID' => $this->STATE_HISTORY_IBLOCK_ID
                        ),
                        false,
                        false,
                        array('ID', 'IBLOCK_ID')
                    );
                    while ($arRes = $dbRes->Fetch()) {
                        $this->__ib->Delete($arRes['ID']);
                    }

                    foreach ($arStateHistory as $date => $arState) {
                        $arStateFields = array(
                            'IBLOCK_SECTION' => false,
                            'IBLOCK_ID' => $this->STATE_HISTORY_IBLOCK_ID,
                            'DATE_ACTIVE_FROM' => ConvertTimeStamp($date),
                            'ACTIVE' => 'Y',
                            'NAME' => $arState['STATE'] . ' - ' . $arFields['LAST_NAME'] . ' ' . $arFields['NAME'],
                            'PREVIEW_TEXT' => $arState['STATE'],
                            'PROPERTY_VALUES' => array(
                                'POST' => $arState['POST'],
                                'USER' => $existingUserId,
                                'DEPARTMENT' => $arState['DEPARTMENT'],
                                'STATE' => array("VALUE" => $property_state_final[strtolower($arState['STATE'])])
                            ),
                        );

                        if (!$this->__ib->Add($arStateFields, false, false)) {
                            Logger::printToFile(
                                date('d.m.Y H:i:s') . ' cml2-import-state',
                                $this->__ib->LAST_ERROR, $arStateFields
                            );
                        }
                    }
                }
            } else {
                $counter['ERR']++;
                Logger::printToFile(
                    date('d.m.Y H:i:s') . ' cml2-import-user',
                    $obUser->LAST_ERROR, $arFields
                );
            }

            return $existingUserId;
        } catch (Throwable $e) {
            Logger::printToFile(date('d.m.Y H:i:s') . ' Error on user import: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * @param string|null $fmno
     * @return array
     * @throws \Exception
     */
    protected function getUserByFMNO(?string $fmno): array
    {
        if ($fmno === null) {
            return [];
        }

        $res = UserTable::query()
            ->setSelect(['*', 'UF_*'])
            ->setFilter([
                Fields::getFmnoUfCode() => $fmno
            ])
            ->fetch();

        return is_array($res) ? $res : [];
    }

    /**
     * @param $arXMLElement
     * @return string|null
     */
    protected function getFMNOFromXmlElement($arXMLElement): ?string
    {
        $fmno = null;
        if (is_array($arXMLElement[Loc::getMessage('IBLOCK_XML2_USER_TAG_PROPERTY_VALUES')])) {
            $fmnoXmlId = Option::get(ServiceManager::getModuleId(), Constants::OPTION_KEY_SYNC_FMNO_XML_ID);
            if (!empty($fmnoXmlId)) {
                foreach ($arXMLElement[Loc::getMessage('IBLOCK_XML2_USER_TAG_PROPERTY_VALUES')] as $arPropertyData) {
                    $propXmlId = $arPropertyData[Loc::getMessage('IBLOCK_XML2_USER_TAG_ID')];
                    if ($propXmlId === $fmnoXmlId) {
                        $fmno = $arPropertyData[Loc::getMessage('IBLOCK_XML2_USER_TAG_VALUE')];
                    }
                }
            }
        }
        return $fmno;
    }

    /**
     * @param $XML_ID
     * @param $arData
     * @return mixed
     */
    public function GetPropertyByXML_ID($XML_ID, $arData = null): mixed
    {
        if (!$this->arPropertiesCache[$XML_ID]) {
            $dbRes = CUserTypeEntity::GetList([], ['ENTITY_ID' => 'USER', 'XML_ID' => $XML_ID]);
            while ($arRes = $dbRes->Fetch()) {
                $this->arPropertiesCache[$arRes['XML_ID']] = $arRes['FIELD_NAME'];
            }
        }

        if ($arData !== null) {
            if (!$this->arPropertiesCache[$XML_ID]) {
                $bAdd = true;
                $arFields = [
                    'ENTITY_ID' => 'USER',
                    'FIELD_NAME' => $this->CalcPropertyFieldName($XML_ID),
                    'USER_TYPE_ID' => 'string',
                    'XML_ID' => $XML_ID,
                    'MULTIPLE' => 'N',
                    'MANDATORY' => 'N',
                    'SHOW_FILTER' => 'I',
                    'SHOW_IN_LIST' => 'Y',
                    'EDIT_IN_LIST' => 'N',
                    'IS_SEARCHABLE' => 'Y',
                    'SETTINGS' => ['ROWS' => 1],
                ];
            } else {
                $bAdd = false;
                $arFields = [];
            }

            $arFields['EDIT_FORM_LABEL'] = $arFields['LIST_COLUMN_LABEL'] = $arFields['LIST_FILTER_LABEL'] = ['ru' => $arData['NAME']];

            $ob = new CUserTypeEntity();

            if ($bAdd) {
                $this->arPropertiesCache[$XML_ID] = $ob->Add($arFields);
            } else {
                $ob->Update($this->arPropertiesCache[$XML_ID], $arFields);
            }
        }

        return $this->arPropertiesCache[$XML_ID];
    }

    /**
     * @param $TYPE
     * @return mixed
     * @throws \Exception
     */
    public function __GetAbsenceType($TYPE)
    {
        /**
         * ВАЖНО: для корректного определения типа отсутствия нужно
         * изменить свойство "ABSENCE_TYPE" инфоблока отсутствий,
         * чтобы список выглядел следующим образом ("XML_ID в битриксе" => "Значение из ЗУП")
         *
         * VACATION => Vacation
         * BUSINESS => Business trip
         * SICK => Sick leave
         * MATERNITY => Maternity
         * UNPAID => Unpaid leave
         * EDUCATION => Educational leave
         * OTHER => Other
         *
         * Для сопоставления нужно, чтобы XML_ID значения в Б24 содержалось как подстрока в значении из ЗУП
         * и не повторялось в других типах, иначе возможны ошибки
         * При добавлении новых типов отсутствий в ЗУП, нужно добавить их в Б24, следуя вышеуказанным правилам
         */

        if (!is_array($this->arAbsenceTypes)) {
            $this->fillAbsenceTypes();
        }

        $type = strtoupper($TYPE);

        $result = $this->arAbsenceTypes['OTHER'];

        foreach ($this->arAbsenceTypes as $xmlId => $id)
        {
            if (str_contains($type, $xmlId))
            {
                $result = $this->arAbsenceTypes[$xmlId];
                break;
            }
        }

        return $result;
    }

    /**
     * @return void
     * @throws \Exception
     */
    public function fillAbsenceTypes(): void
    {
        $this->arAbsenceTypes = [];

        $query = \Bitrix\Iblock\PropertyEnumerationTable::query()
            ->setSelect(['XML_ID', 'ID'])
            ->where('PROPERTY.IBLOCK_ID', 1)
            ->where('PROPERTY.CODE', 'ABSENCE_TYPE')
            ->exec();

        while ($type = $query->fetch())
        {
            $this->arAbsenceTypes[$type['XML_ID']] = $type['ID'];
        }
    }
}