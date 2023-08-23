<?php
/**
 * ==================================================
 * Developer: Alexey Nazarov
 * E-mail: alsnazarov@1cbit.ru
 * Copyright (c) 2019 - 2022
 * ==================================================
 * mc-portal - Client.php
 * 21.11.2022 14:46
 * ==================================================
 */

namespace Cbit\Mc\Timesheets\Api;

use Bitrix\Main\Config\Option;
use Bitrix\Main\Localization\Loc;
use Cbit\Mc\Core\Api\OneC\AbstractClient;
use Cbit\Mc\Timesheets\Config\Constants;
use Cbit\Mc\Timesheets\Internals\Control\ServiceManager;
use Exception;

/**
 * Class Client
 * @package Cbit\Mc\Timesheets\Api
 */
class Client extends AbstractClient
{
    /**
     * Client constructor.
     * @throws \Exception
     */
    public function __construct()
    {
        $this->moduleId = ServiceManager::getModuleId();
        parent::__construct();
    }

    /**
     * @throws \Exception
     */
    protected function setBaseOptions(): void
    {
        $this->baseUrl = Option::get($this->moduleId, Constants::OPTION_KEY_API_URL);
        if (empty($this->baseUrl))
        {
            throw new Exception(
                Loc::getMessage($this->moduleId."_URL_ERROR") . "\r\n In file "
                        . __FILE__ . ' on line ' . __LINE__
            );
        }
        else{
            if (mb_substr($this->baseUrl, -1) === "/"){
                $this->baseUrl = mb_substr($this->baseUrl, 0, -1);
            }
        }

        $this->login    = Option::get($this->moduleId, Constants::OPTION_KEY_API_LOGIN);
        $this->password = Option::get($this->moduleId, Constants::OPTION_KEY_API_PASSWORD);
        if (empty($this->login) || empty($this->password))
        {
            throw new Exception(
                Loc::getMessage($this->moduleId."_AUTH_ERROR")
                        . "\r\n In file " . __FILE__ . ' on line ' . __LINE__
            );
        }

        $this->clientId = Option::get($this->moduleId, Constants::OPTION_KEY_API_CLIENT_ID);
        if (empty($this->clientId))
        {
            throw new Exception(
                Loc::getMessage($this->moduleId."_CLIENT_ID_ERROR")
                . "\r\n In file " . __FILE__ . ' on line ' . __LINE__
            );
        }

        $this->clientSecret = Option::get($this->moduleId, Constants::OPTION_KEY_API_CLIENT_SECRET);
        if (empty($this->clientSecret))
        {
            throw new Exception(
                Loc::getMessage($this->moduleId."_CLIENT_SECRET_ERROR")
                . "\r\n In file " . __FILE__ . ' on line ' . __LINE__
            );
        }

        $this->apiKey = Option::get($this->moduleId, Constants::OPTION_KEY_API_API_KEY);
        if (empty($this->apiKey))
        {
            throw new Exception(
                Loc::getMessage($this->moduleId."_API_KEY_ERROR")
                . "\r\n In file " . __FILE__ . ' on line ' . __LINE__
            );
        }
    }
}