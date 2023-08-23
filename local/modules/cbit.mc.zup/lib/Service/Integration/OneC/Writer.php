<?php
/**
 * ==================================================
 * Developer: Alexey Nazarov
 * E-mail: alsnazarov@1cbit.ru
 * Copyright (c) 2019 - 2022
 * ==================================================
 * mc-portal - Writer.php
 * 21.11.2022 21:38
 * ==================================================
 */


namespace Cbit\Mc\Zup\Service\Integration\OneC;

use Bitrix\Main\Error;
use Bitrix\Main\Result;
use Bitrix\Main\Type\Date;
use Bitrix\Main\Type\DateTime;
use Bitrix\Main\Web\HttpClient;
use Cbit\Mc\Core\Service\Integration\Zup\Fields;
use Cbit\Mc\Zup\Internals\Model\Education\EmployeeEducationTable;
use Exception;

/**
 * Class Writer
 * @package Cbit\Mc\Zup\Service\Integration\OneC
 */
class Writer extends Base
{
    /**
     * @return \Bitrix\Main\Result
     */
    public function sendEmployeeEducation(): Result
    {
        $endpoint = '/employeeeducation';
        try
        {
            $data = EmployeeEducationTable::query()
                ->whereNot('SENT_TO_ONE_C', '=', 'Y')
                ->setSelect(['*', 'USER_FMNO' => 'USER.'.Fields::getFmnoUfCode()])
                ->fetchAll();

            $oneCData = [];
            foreach ($data as $item)
            {
                $oneCData[] = [
                    "BitrixId"      => $item['ID'],
                    "FMNO"          => $item['USER_FMNO'],
                    "EducationType" => [
                        "UUID" => $item['EDUCATION_TYPE_UUID']
                    ],
                    "institution"   => [
                        "Description_ru" => $item['INSTITUTION_RU'],
                        "Description_en" => $item['INSTITUTION_EN']
                    ],
                    "Speciality"    => [
                        "Description_ru" => $item['SPECIALTY_RU'],
                        "Description_en" => $item['SPECIALTY_EN']
                    ],
                    "Qualification" => [
                        "Description_ru" => $item['QUALIFICATION_RU'],
                        "Description_en" => $item['QUALIFICATION_EN']
                    ],
                    "Begin"         => ($item['DATE_BEGIN_STUDYING'] instanceof Date) ? ($item['DATE_BEGIN_STUDYING'])->format("Y-m-d\TH:m:s") : '',
                    "End"           => ($item['DATE_END_STUDYING'] instanceof Date) ? ($item['DATE_END_STUDYING'])->format("Y-m-d\TH:m:s") : '',
                    "OutsideRussia" => ($item['OUTSIDE_RUSSIA'] === 'Y')
                ];
            }

            if (!empty($oneCData))
            {
                $result = $this->send($endpoint, HttpClient::HTTP_POST, $oneCData);
                if ($result->isSuccess())
                {
                    foreach ($result->getData() as $item)
                    {
                        if (!empty($item['BitrixId']) && !empty($item['UUID']))
                        {
                            EmployeeEducationTable::update($item['BitrixId'], [
                                'UUID'          => $item['UUID'],
                                'SENT_TO_ONE_C' => 'Y',
                                'DATE_MODIFY'   => new DateTime(),
                            ]);
                        }
                    }
                }
            }
            else
            {
                $result = new Result();
            }
            return $result;
        }
        catch(Exception $e)
        {
            return (new Result)->addError(new Error($e->getMessage()));
        }
    }
}