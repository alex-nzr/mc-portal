<?php
/**
 * ==================================================
 * Developer: Alexey Nazarov
 * E-mail: alsnazarov@1cbit.ru
 * Copyright (c) 2019 - 2022
 * ==================================================
 * mc-portal - Common.php
 * 17.01.2023 12:30
 * ==================================================
 */
namespace Cbit\Mc\RI\Agent;


use Bitrix\Crm\Item;
use Bitrix\Main\Type\DateTime;
use Cbit\Mc\Core\Helper\Main\DateTimeCalculator;
use Cbit\Mc\RI\Config\Configuration;
use Cbit\Mc\RI\Config\Constants;
use Cbit\Mc\RI\Entity\Dynamic;
use Cbit\Mc\RI\Helper\Notify\Sender;
use Cbit\Mc\RI\Internals\Debug\Logger;
use Cbit\Mc\RI\Service\Container;
use Exception;
use Throwable;

/**
 * Class Common
 * @package Cbit\Mc\RI\Agent
 */
class Common
{
    /**
     * @return string
     */
    public static function sendNoteAboutUnassignedRequests(): string
    {
        try
        {
            Container::getInstance()->getLocalization()->loadMessages();
            $dateCalc = DateTimeCalculator::getInstance();
            $entity   = Dynamic::getInstance();
            $typeId   = $entity->getTypeId();
            $unassignedStageId = $entity->getStatusPrefix(
                    $entity->getDefaultCategoryId()
                ) . Constants::DYNAMIC_STAGE_DEFAULT_NEW;

            $items = $entity->select(
                [Item::FIELD_NAME_ID, Item::FIELD_NAME_CREATED_TIME, Item::FIELD_NAME_TITLE],
                [
                    '<='.Item::FIELD_NAME_CREATED_TIME    => $dateCalc->addBack(new DateTime(), 30*60),
                    '='.Item::FIELD_NAME_STAGE_ID         => $unassignedStageId,
                    'UF_CRM_'.$typeId.'_UNASSIGNED_NOTED' => false
                ]
            );

            foreach ($items as $item)
            {
                Sender::getInstance($item)->sendUnassignedItemMessages();
                $res = $entity->update($item, ['UF_CRM_'.$typeId.'_UNASSIGNED_NOTED' => true]);
                if (!$res->isSuccess())
                {
                    throw new Exception(implode('; ', $res->getErrorMessages()));
                }
            }
        }
        catch (Throwable $e)
        {
            $method = __METHOD__;
            static::logError($e, $method);
        }

        return __METHOD__.'();';
    }

    /**
     * @param \Throwable $e
     * @param string $method
     */
    private static function logError(Throwable $e, string $method): void
    {
        $code = !empty($e->getCode()) ? $e->getCode() : 0;
        Logger::writeToFile(
            "Code: $code. Description: " . $e->getMessage(),
            date("d.m.Y H:i:s") . ' ' . $method,
            Configuration::getInstance()->getLogFilePath()
        );
    }
}