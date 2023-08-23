<?php
/**
 * ==================================================
 * Developer: Alexey Nazarov
 * E-mail: alsnazarov@1cbit.ru
 * Copyright (c) 2019 - 2022
 * ==================================================
 * mc-portal - EducationType.php
 * 22.11.2022 16:15
 * ==================================================
 */


namespace Cbit\Mc\Zup\Helper\Orm;


use Bitrix\Main\Error;
use Bitrix\Main\Result;
use Bitrix\Main\Type\DateTime;
use Bitrix\Main\Web\Json;
use Cbit\Mc\Zup\Internals\Model\Education\EducationTypesTable;
use Exception;

/**
 * Class EducationType
 * @package Cbit\Mc\Zup\Helper\Orm
 */
class EducationType
{
    /**
     * @param array $type
     * @return \Bitrix\Main\Result
     */
    public function saveEducationType(array $type): Result
    {
        $result = new Result();
        try
        {
            if (is_array($type) && $this->checkValidityOfType($type))
            {
                $existingType = EducationTypesTable::query()
                    ->setSelect(['ID'])
                    ->where('UUID', '=', $type['UUID'])
                    ->fetch();

                if (!empty($existingType))
                {
                    $res = EducationTypesTable::update($existingType['ID'], [
                        'DATE_MODIFY'    => new DateTime(),
                        'DESCRIPTION_RU' => (string)$type['Description_ru'],
                        'DESCRIPTION_EN' => (string)$type['Description_en'],
                    ]);
                    if (!$res->isSuccess())
                    {
                        $result->addErrors($res->getErrors());
                    }
                }
                else
                {
                    $res = EducationTypesTable::add([
                        'UUID'           => (string)$type['UUID'],
                        'DESCRIPTION_RU' => (string)$type['Description_ru'],
                        'DESCRIPTION_EN' => (string)$type['Description_en'],
                    ]);
                    if (!$res->isSuccess())
                    {
                        $result->addErrors($res->getErrors());
                    }
                }
            }
            else
            {
                throw new Exception('Data of education type is invalid. ' . Json::encode($type));
            }
        }
        catch(Exception $e)
        {
            $result->addError(new Error($e->getMessage()));
        }

        return $result;
    }

    /**
     * @param array $type
     * @return bool
     */
    private function checkValidityOfType(array $type): bool
    {
        return !empty($type['UUID']) && is_string($type['UUID']);
    }
}