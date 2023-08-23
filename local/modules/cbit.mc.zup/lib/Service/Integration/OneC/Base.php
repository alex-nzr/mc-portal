<?php
/**
 * ==================================================
 * Developer: Alexey Nazarov
 * E-mail: alsnazarov@1cbit.ru
 * Copyright (c) 2019 - 2022
 * ==================================================
 * mc-portal - Base.php
 * 21.11.2022 21:43
 * ==================================================
 */


namespace Cbit\Mc\Zup\Service\Integration\OneC;

use Bitrix\Main\Error;
use Bitrix\Main\Localization\Loc;
use Bitrix\Main\Result;
use CBit\Mc\Zup\Api\Client;
use Exception;

/**
 * Class Base
 * @package Cbit\Mc\Zup\Service\Integration\OneC
 */
abstract class Base
{
    protected Client $client;

    public function __construct()
    {
        $this->client = new Client();
    }

    /**
     * @param string $endpoint
     * @param string $method
     * @param array $params
     * @return \Bitrix\Main\Result
     */
    public function send(string $endpoint, string $method, array $params = []): Result
    {
        return $this->client->call($endpoint, $method, $params);
    }
}