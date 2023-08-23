<?php
/**
 * ==================================================
 * Developer: Alexey Nazarov
 * E-mail: alsnazarov@1cbit.ru
 * Copyright (c) 2019 - 2022
 * ==================================================
 * mc-portal - Reader.php
 * 21.11.2022 21:37
 * ==================================================
 */


namespace Cbit\Mc\Zup\Service\Integration\OneC;

use Bitrix\Main\Error;
use Bitrix\Main\Result;
use Bitrix\Main\Type\DateTime;
use Bitrix\Main\Web\HttpClient;
use Bitrix\Main\Web\Json;
use Cbit\Mc\Zup\Helper\Orm\EducationType;
use Cbit\Mc\Zup\Helper\Orm\EmployeeEducation;
use Cbit\Mc\Zup\Internals\Model\Education\EducationTypesTable;
use Exception;

/**
 * Class Reader
 * @package Cbit\Mc\Zup\Service\Integration\OneC
 */
class Reader extends Base
{
    /**
     * @param \Bitrix\Main\Type\DateTime|null $from
     * @return \Bitrix\Main\Result
     */
    public function getEducationTypes(?DateTime $from = null): Result
    {
        $endpoint = '/educationtypes';
        $params = ($from instanceof DateTime) ? ['from' => $from->format("Y-m-d\TH:m:s")] : [];

        $result = $this->send($endpoint, HttpClient::HTTP_GET, $params);

        if ($result->isSuccess())
        {
            try
            {
                $oEducationType = new EducationType;
                foreach ($result->getData() as $type)
                {
                    if (is_array($type))
                    {
                        $saveResult = $oEducationType->saveEducationType($type);
                        if (!$saveResult->isSuccess())
                        {
                            $result->addErrors($saveResult->getErrors());
                        }
                    }
                }
            }
            catch(Exception $e)
            {
                $result->addError(new Error($e->getMessage()));
            }
        }

        return $result;
    }

    /**
     * @param \Bitrix\Main\Type\DateTime|null $from
     * @return \Bitrix\Main\Result
     */
    public function getEmployeeEducation(?DateTime $from = null): Result
    {
        $endpoint = '/employeeeducation';
        $params = ($from instanceof DateTime) ? ['from' => $from->format("Y-m-d\TH:m:s")] : [];
        $result =  $this->send($endpoint, HttpClient::HTTP_GET, $params);

        if ($result->isSuccess())
        {
            try
            {
                $oEmployeeEducation = new EmployeeEducation;
                foreach ($result->getData() as $item)
                {
                    if (is_array($item))
                    {
                        //put "Y", because the data has already come from 1c and it is not required to send them back
                        $item['SENT_TO_ONE_C'] = 'Y';

                        $saveResult = $oEmployeeEducation->saveEmployeeEducation($item);
                        if (!$saveResult->isSuccess())
                        {
                            $result->addErrors($saveResult->getErrors());
                        }
                    }
                }
            }
            catch(Exception $e)
            {
                $result->addError(new Error($e->getMessage()));
            }
        }

        return $result;
    }
}