<?php
/**
 * ==================================================
 * Developer: Alexey Nazarov
 * E-mail: alsnazarov@1cbit.ru
 * Copyright (c) 2019 - 2022
 * ==================================================
 * mc-portal - Education.php
 * 22.11.2022 17:46
 * ==================================================
 */


namespace Cbit\Mc\Profile\Service\Integration\Zup;

use Bitrix\Main\Error;
use Bitrix\Main\Result;
use Cbit\Mc\Zup\Helper\Orm\EmployeeEducation;
use Cbit\Mc\Zup\Internals\Model\Education\EducationTypesTable;
use Cbit\Mc\Zup\Internals\Model\Education\EmployeeEducationTable;
use Exception;

/**
 * Class Education
 * @package Cbit\Mc\Profile\Service\Integration\Zup
 */
class Education
{
    /**
     * @param int $userId
     * @return \Bitrix\Main\Result
     */
    public static function getUserEducationData(int $userId): Result
    {
        $result = new Result();
        try 
        {
            $data = EmployeeEducationTable::query()
                ->where('USER_ID', '=', $userId)
                ->setSelect([
                    '*',
                    'EDU_TYPE_RU' => 'EDUCATION_TYPE.DESCRIPTION_RU',
                    'EDU_TYPE_EN' => 'EDUCATION_TYPE.DESCRIPTION_EN'
                ])
                ->fetchAll();
            
            $preparedData = [
                'APPROVED' => [],
                'NOT_APPROVED' => []
            ];

            foreach ($data as $item)
            {
                $item['EDUCATION_TYPE'] = $item['EDU_TYPE_RU'] . " (" . $item['EDU_TYPE_EN'] . ")";
                if ($item['CONFIRMED_BY_HR'] === 'Y')
                {
                    $preparedData['APPROVED'][] = $item;
                }
                else
                {
                    $preparedData['NOT_APPROVED'][] = $item;
                }
            }

            $result->setData($preparedData);
        }
        catch(Exception $e)
        {
            $result->addError(new Error($e->getMessage()));
        }
        return $result;
    }

    /**
     * @return \Bitrix\Main\Result
     */
    public static function getEducationTypes(): Result
    {
        $result = new Result();
        try
        {
            $data = EducationTypesTable::query()
                ->setSelect(['*'])
                ->fetchAll();

            $result->setData($data);
        }
        catch(Exception $e)
        {
            $result->addError(new Error($e->getMessage()));
        }
        return $result;
    }

    /**
     * @param array $postData
     * @return \Bitrix\Main\Result
     */
    public static function sendEmployeeEducation(array $postData): Result
    {
        $data = [
            'USER_ID' => (int)$postData['USER_ID'],
            'EducationType' => [
                'UUID' => $postData['EDUCATION_TYPE_UUID']
            ],
            'institution' => [
                'Description_ru' => $postData['INSTITUTION_RU'],
                'Description_en' => $postData['INSTITUTION_EN']
            ],
            'Speciality' => [
                'Description_ru' => $postData['SPECIALTY_RU'],
                'Description_en' => $postData['SPECIALTY_EN']
            ],
            'Qualification' => [
                'Description_ru' => $postData['QUALIFICATION_RU'],
                'Description_en' => $postData['QUALIFICATION_EN']
            ],
            'Begin' => $postData['DATE_BEGIN_STUDYING'],
            'End' => $postData['DATE_END_STUDYING'],
            'OutsideRussia' => ($postData['OUTSIDE_RUSSIA'] === 'Y'),
            'SENT_TO_ONE_C' => 'N',
        ];
        return (new EmployeeEducation)->saveEmployeeEducation($data);
    }
}   