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


namespace Cbit\Mc\Timesheets\Service\Integration\OneC;

use Bitrix\Main\Error;
use Bitrix\Main\Result;
use Bitrix\Main\Web\HttpClient;
use Exception;

/**
 * Class Writer
 * @package Cbit\Mc\Timesheets\Service\Integration\OneC
 */
class Writer extends Base
{
    /**
     * @param int $id
     * @param array $data
     * @return \Bitrix\Main\Result
     */
    public function sendStaffingRecord(int $id, array $data): Result
    {
        try
        {
            $endpoint = "/staffing/$id/";
            return $this->send($endpoint, HttpClient::HTTP_POST, $data);
        }
        catch(Exception $e)
        {
            return (new Result)->addError(new Error($e->getMessage()));
        }
    }

    /**
     * @param int $id
     * @param array $data
     * @return \Bitrix\Main\Result
     */
    public function updateStaffingRecord(int $id, array $data): Result
    {
        try
        {
            $endpoint = "/staffing/$id/";
            return $this->send($endpoint, HttpClient::HTTP_POST, $data);
        }
        catch(Exception $e)
        {
            return (new Result)->addError(new Error($e->getMessage()));
        }
    }

    /**
     * @param int $id
     * @return \Bitrix\Main\Result
     */
    public function deleteStaffingRecord(int $id): Result
    {
        try
        {
            $endpoint = "/staffing/$id/";
            return $this->send($endpoint, HttpClient::HTTP_DELETE);
        }
        catch(Exception $e)
        {
            return (new Result)->addError(new Error($e->getMessage()));
        }
    }
}