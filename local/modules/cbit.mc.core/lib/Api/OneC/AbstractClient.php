<?php
/**
 * ==================================================
 * Developer: Alexey Nazarov
 * E-mail: alsnazarov@1cbit.ru
 * Copyright (c) 2019 - 2022
 * ==================================================
 * mc-portal - AbstractClient.php
 * 12.12.2022 19:11
 * ==================================================
 */
namespace Cbit\Mc\Core\Api\OneC;

use Bitrix\Main\Error;
use Bitrix\Main\Result;
use Bitrix\Main\Web\HttpClient;
use Bitrix\Main\Web\Json;
use Cbit\Mc\Staffing\Internals\Debug\Logger;
use Exception;

/**
 * Class AbstractClient
 * @package Cbit\Mc\Core\Api\OneC
 */
abstract class AbstractClient
{
    const HTTP_SUCCESS_CODES = [200, 201, 202];

    protected ?HttpClient $httpClient = null;
    protected Result      $result;
    protected string      $moduleId;
    protected string      $baseUrl;
    protected string      $login;
    protected string      $password;
    protected string      $clientId;
    protected string      $clientSecret;
    protected string      $apiKey;

    public function __construct()
    {
        try {
            $this->result = new Result();

            $this->setBaseOptions();

            $options = [
                "redirect"               => true,
                "waitResponse"           => true,
                "socketTimeout"          => 30,
                "streamTimeout"          => 0,
                "version"                => HttpClient::HTTP_1_1,
                "disableSslVerification" => true,
            ];

            $this->httpClient = new HttpClient($options);
            $this->httpClient->setHeader('Content-Type','application/json');
            $this->httpClient->setHeader('Accept','*/*');
            $this->httpClient->setHeader('api_key',$this->apiKey);
            $this->httpClient->setAuthorization($this->login,$this->password);
            $this->setAuthToken();

        }
        catch (Exception $e)
        {
            $this->result->addError(new Error($e->getMessage()));
        }
    }

    /**
     * @param string $endpoint
     * @param string $method
     * @param array $params
     * @param bool $authRequest
     * @return \Bitrix\Main\Result
     */
    public function call(
        string $endpoint, string $method = HttpClient::HTTP_GET, array $params = [], bool $authRequest = false
    ): Result
    {
        $this->result = new Result();

        try
        {
            if (mb_substr($endpoint, 0, 1) !== "/"){
                $endpoint = '/' . $endpoint;
            }

            switch (strtoupper($method)){
                case HttpClient::HTTP_GET:
                    $response = $this->httpClient->get($this->baseUrl.$endpoint . '?' . http_build_query($params));
                    break;
                case HttpClient::HTTP_POST:
                //case HttpClient::HTTP_PUT:
                //case HttpClient::HTTP_PATCH:
                case HttpClient::HTTP_DELETE:
                    $this->httpClient->query(
                        $method,
                        $this->baseUrl.$endpoint,
                        $authRequest ? Json::encode($params) : Json::encode(["dataset" => $params])
                    );
                    $response = $this->httpClient->getResult();
                    /*if (!$authRequest)
                    {
                        Logger::print(
                            $this->httpClient->getStatus(),
                            Json::encode(["dataset" => $params]),
                            $response
                        );
                    }*/
                    break;
                default:
                    throw new Exception("Method not allowed", 405);
            }

            if (in_array($this->httpClient->getStatus(), static::HTTP_SUCCESS_CODES))
            {
                if ($method === HttpClient::HTTP_POST || $method === HttpClient::HTTP_GET)
                {
                    if (gettype($response) === 'string')
                    {
                        $data = Json::decode($response);
                        if (!is_array($data))
                        {
                            throw new Exception('Unexpected response: "'. $response . '"');
                        }

                        if ((bool)$data['ok'] === true)
                        {
                            $this->result->setData((array)$data['data']);
                        }
                        else
                        {
                            $error = is_array($data['errors']) ? implode('; ', $data['errors']) : $data['errors'];
                            throw new Exception(
                                $error . "\r\n In file " . __FILE__ . ' on line ' . __LINE__
                            );
                        }
                    }
                    else
                    {
                        throw new Exception(
                            'Unknown type of response.'  . "\r\n In file " . __FILE__ . ' on line ' . __LINE__
                        );
                    }
                }
            }
            else
            {
                throw new Exception(
                    'Server responded with status - '. $this->httpClient->getStatus()
                    . "\r\n" . $response
                );
            }
        }
        catch (Exception $e){
            $this->result->addError(new Error($e->getMessage()));
        }
        return $this->result;
    }

    /**
     * @return \Bitrix\Main\Result
     */
    public function getResult(): Result
    {
        return $this->result;
    }

    /**
     * @throws \Exception
     */
    private function setAuthToken(): void
    {
        $authEndpoint = '/auth';
        $authData = [
            'client_id'     => $this->clientId,
            'client_secret' => $this->clientSecret,
        ];

        $res = $this->call($authEndpoint, HttpClient::HTTP_POST, $authData, true);
        if ($res->isSuccess())
        {
            $data = $res->getData();
            if (!empty($data['access_token']))
            {
                $token = $data['access_token'];
                $this->httpClient->setHeader('access_token',$token);
            }
            else
            {
                throw new Exception(
                    'Access token not found in response.' . "\r\n In file " . __FILE__ . ' on line ' . __LINE__
                );
            }
        }
    }

    protected abstract function setBaseOptions(): void;
}