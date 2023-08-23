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


namespace Cbit\Mc\Timesheets\Service\Integration\OneC;

use Bitrix\Main\Error;
use Bitrix\Main\Result;
use Bitrix\Main\Type\DateTime;
use Bitrix\Main\Web\HttpClient;
use Cbit\Mc\Timesheets\Helper\ActivitiesRegistry;
use Cbit\Mc\Timesheets\Helper\EnumerationsRegistry;
use Cbit\Mc\Timesheets\Helper\FunctionsRegistry;
use Cbit\Mc\Timesheets\Helper\IndustriesRegistry;
use Cbit\Mc\Timesheets\Helper\TeamCompositionsRegistry;
use Exception;

/**
 * Class Reader
 * @package Cbit\Mc\Timesheets\Service\Integration\OneC
 */
class Reader extends Base
{
    /**
     * @param \Bitrix\Main\Type\DateTime|null $from
     * @return \Bitrix\Main\Result
     */
    public function getActivitiesRegistry(?DateTime $from = null): Result
    {
        $endpoint = '/activities';
        $params = ($from instanceof DateTime) ? ['from' => $from->format("Y-m-d\TH:m:s")] : [];
        $result =  $this->send($endpoint, HttpClient::HTTP_GET, $params);

        if ($result->isSuccess())
        {
            try
            {
                foreach ($result->getData() as $item)
                {
                    if (is_array($item))
                    {
                        $saveResult = ActivitiesRegistry::processActivityItem($item);
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
    public function getIndustriesRegistry(?DateTime $from = null): Result
    {
        $endpoint = '/industries';
        $params = ($from instanceof DateTime) ? ['from' => $from->format("Y-m-d\TH:m:s")] : [];
        $result =  $this->send($endpoint, HttpClient::HTTP_GET, $params);

        if ($result->isSuccess())
        {
            try
            {
                foreach ($result->getData() as $item)
                {
                    if (is_array($item))
                    {
                        $saveResult = IndustriesRegistry::processIndustryItem($item);
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
    public function getFunctionsRegistry(?DateTime $from = null): Result
    {
        $endpoint = '/functions';
        $params = ($from instanceof DateTime) ? ['from' => $from->format("Y-m-d\TH:m:s")] : [];
        $result =  $this->send($endpoint, HttpClient::HTTP_GET, $params);

        if ($result->isSuccess())
        {
            try
            {
                foreach ($result->getData() as $item)
                {
                    if (is_array($item))
                    {
                        $saveResult = FunctionsRegistry::processFunctionItem($item);
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
    public function getTeamCompositionsRegistry(?DateTime $from = null): Result
    {
        $endpoint = '/team_compositions';
        $params = ($from instanceof DateTime) ? ['from' => $from->format("Y-m-d\TH:m:s")] : [];
        $result =  $this->send($endpoint, HttpClient::HTTP_GET, $params);

        if ($result->isSuccess())
        {
            try
            {
                foreach ($result->getData() as $item)
                {
                    if (is_array($item))
                    {
                        $saveResult = TeamCompositionsRegistry::processTeamCompositionItem($item);
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
     * @return \Bitrix\Main\Result
     */
    public function getEnumerationsData(): Result
    {
        $endpoint = '/static_data';
        $result =  $this->send($endpoint, HttpClient::HTTP_GET);

        if ($result->isSuccess())
        {
            try
            {
                $data = $result->getData();
                if (is_array($data['ProjectPhases']))
                {
                    foreach ($data['ProjectPhases'] as $item)
                    {
                        if (is_array($item))
                        {
                            $saveResult = EnumerationsRegistry::processPhaseItem($item);
                            if (!$saveResult->isSuccess())
                            {
                                $result->addErrors($saveResult->getErrors());
                            }
                        }
                    }
                }

                if (is_array($data['ProjectStates']))
                {
                    foreach ($data['ProjectStates'] as $item)
                    {
                        if (is_array($item))
                        {
                            $saveResult = EnumerationsRegistry::processStateItem($item);
                            if (!$saveResult->isSuccess())
                            {
                                $result->addErrors($saveResult->getErrors());
                            }
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
    public function getProjects(?DateTime $from = null): Result
    {
        $endpoint = '/projects';
        $params = ($from instanceof DateTime) ? ['from' => $from->format("Y-m-d\TH:m:s")] : [];
        return $this->send($endpoint, HttpClient::HTTP_GET, $params);
    }
}