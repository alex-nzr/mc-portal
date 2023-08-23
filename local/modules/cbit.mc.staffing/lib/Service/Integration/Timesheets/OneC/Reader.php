<?php
/**
 * ==================================================
 * Developer: Alexey Nazarov
 * E-mail: alsnazarov@1cbit.ru
 * Copyright (c) 2019 - 2022
 * ==================================================
 * mc-portal - Reader.php
 * 16.12.2022 00:30
 * ==================================================
 */

namespace Cbit\Mc\Staffing\Service\Integration\Timesheets\OneC;


use Bitrix\Main\Error;
use Bitrix\Main\Result;
use Bitrix\Main\Type\DateTime;
use Cbit\Mc\Staffing\Helper\Project;
use Exception;

/**
 * Class Reader
 * @package Cbit\Mc\Staffing\Service\Integration\Timesheets\OneC
 */
class Reader extends \Cbit\Mc\Timesheets\Service\Integration\OneC\Reader
{
    public function loadProjectsFromOneC(?DateTime $from = null): Result
    {
        $result = $this->getProjects($from);
        if ($result->isSuccess())
        {
            try
            {
                foreach ($result->getData() as $item)
                {
                    if (is_array($item))
                    {
                        $saveResult = Project::processProjectItem($item);
                        if (!$saveResult->isSuccess())
                        {
                            $result->addError(new Error('Error on processing project ' . $item['ChargeCode']));
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