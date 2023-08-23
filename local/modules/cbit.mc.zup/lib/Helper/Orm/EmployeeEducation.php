<?php
/**
 * ==================================================
 * Developer: Alexey Nazarov
 * E-mail: alsnazarov@1cbit.ru
 * Copyright (c) 2019 - 2022
 * ==================================================
 * mc-portal - EmployeeEducation.php
 * 22.11.2022 16:11
 * ==================================================
 */


namespace Cbit\Mc\Zup\Helper\Orm;


use Bitrix\Main\Error;
use Bitrix\Main\ORM\Query\Filter\ConditionTree;
use Bitrix\Main\ORM\Query\Query;
use Bitrix\Main\Result;
use Bitrix\Main\Type;
use Bitrix\Main\UserTable;
use Bitrix\Main\Web\Json;
use Cbit\Mc\Core\Service\Integration\Zup\Fields;
use Cbit\Mc\Zup\Internals\Model\Education\EmployeeEducationTable;
use DateTime;
use Exception;

/**
 * Class EmployeeEducation
 * @package Cbit\Mc\Zup\Helper\Orm
 */
class EmployeeEducation
{
    /**
     * @param array $item
     * @return \Bitrix\Main\Result
     */
    public function saveEmployeeEducation(array $item): Result
    {
        $result = new Result();

        try
        {
            if ($this->checkValidityOfEmployeeEducation($item))
            {
                $data = [
                    'UUID'                  => (string)$item['UUID'],
                    'EDUCATION_TYPE_UUID'   => (string)$item['EducationType']['UUID'],
                    'INSTITUTION_RU'        => (string)$item['institution']['Description_ru'],
                    'INSTITUTION_EN'        => (string)$item['institution']['Description_en'],
                    'SPECIALTY_RU'          => (string)$item['Speciality']['Description_ru'],
                    'SPECIALTY_EN'          => (string)$item['Speciality']['Description_en'],
                    'QUALIFICATION_RU'      => (string)$item['Qualification']['Description_ru'],
                    'QUALIFICATION_EN'      => (string)$item['Qualification']['Description_en'],
                    'DATE_BEGIN_STUDYING'   => Type\DateTime::createFromPhp(new DateTime($item['Begin'])),
                    'DATE_END_STUDYING'     => Type\DateTime::createFromPhp(new DateTime($item['End'])),
                    'OUTSIDE_RUSSIA'        => ((bool)$item['OutsideRussia'] === true) ? 'Y' : 'N',
                    'CONFIRMED_BY_HR'       => ((bool)$item['ConfirmedByHR'] === true) ? 'Y' : 'N',
                    'SENT_TO_ONE_C'         => ($item['SENT_TO_ONE_C']) ?? 'N',
                ];

                $existingEducation = EmployeeEducationTable::query()
                    ->setSelect(['ID'])
                    ->where(Query::filter()
                        ->logic(ConditionTree::LOGIC_OR)
                        ->where('UUID', '=', $item['UUID'])
                        ->where('ID', '=', $item['BitrixId'])
                    )
                    ->fetch();

                if (!empty($existingEducation))
                {
                    $data['DATE_MODIFY'] = new Type\DateTime();
                    $ormResult = EmployeeEducationTable::update($existingEducation['ID'], $data);
                }
                else
                {
                    if (empty($item['USER_ID']))
                    {
                        $user = UserTable::query()
                            ->setSelect(['ID'])
                            ->where(Fields::getFmnoUfCode(), '=', $item['FMNO'])
                            ->fetch();
                        if (!empty($user))
                        {
                            $data['USER_ID'] = $user['ID'];
                        }
                        else
                        {
                            throw new Exception('Can not find user with FMNO - ' . $item['FMNO']);
                        }
                    }
                    else
                    {
                        $data['USER_ID'] = $item['USER_ID'];
                    }

                    $ormResult = EmployeeEducationTable::add($data);
                }
                if (!$ormResult->isSuccess())
                {
                    throw new Exception(implode('; ', $ormResult->getErrorMessages()));
                }
            }
            else
            {
                throw new Exception('Data of education type is invalid. ' . Json::encode($item));
            }
        }
        catch(Exception $e)
        {
            $result->addError(new Error($e->getMessage()));
        }

        return $result;
    }

    /**
     * @param array $education
     * @return bool
     * @throws \Exception
     */
    private function checkValidityOfEmployeeEducation(array $education): bool
    {
        if (empty($education['USER_ID']) && empty($education['FMNO'])){
            throw new Exception("Required field 'FMNO' is empty. ". $education['UUID']);
        }

        if (!is_array($education['EducationType']) || empty($education['EducationType']['UUID'])){
            throw new Exception("Required field ['EducationType']['UUID'] is empty. ". $education['UUID']);
        }

        if (!is_array($education['institution']) || !is_array($education['Speciality']) || !is_array($education['Qualification']))
        {
            throw new Exception("'institution', 'Speciality' and 'Qualification' must be an array. ". $education['UUID']);
        }

        if (empty($education['Begin']) || empty($education['End'])){
            throw new Exception("Fields 'Begin' and 'End' are both required. ". $education['UUID']);
        }

        if (new DateTime($education['Begin']) >= new DateTime($education['End'])){
            throw new Exception("End date can't be less then begin date. ". $education['UUID']);
        }

        return true;
    }
}