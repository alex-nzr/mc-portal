<?php

namespace Cbit\Mc\RI\Internals\Installation;

use Bitrix\Crm\Model\ItemCategoryTable;
use Bitrix\Crm\PhaseSemantics;
use Bitrix\Crm\StatusTable;
use Bitrix\Main\Localization\Loc;
use Bitrix\Main\Result;
use Cbit\Mc\RI\Config\Constants;
use Cbit\Mc\RI\Internals\Control\ServiceManager;
use CCrmStatus;


Loc::loadMessages(__FILE__);

/**
 * Class CategoryInstaller
 * @package Cbit\Mc\RI\Internals\Installation
 */
class CategoryInstaller
{
    /**
     * @param int $entityTypeId
     * @return \Bitrix\Main\Result
     * @throws \Exception
     */
    public static function install(int $entityTypeId): Result
    {
        $result = new Result;

        $defaultCategoryResult = static::updateDefaultCategory($entityTypeId);
        if(!$defaultCategoryResult->isSuccess())
        {
            $result->addErrors($defaultCategoryResult->getErrors());
        }

        return $result;
    }

    /**
     * @param int $entityTypeId
     * @return \Bitrix\Main\Result
     * @throws \Exception
     */
    protected static function updateDefaultCategory(int $entityTypeId): Result
    {
        $result = new Result();
        $cat = ItemCategoryTable::query()
            ->setSelect(['ID'])
            ->setFilter(['=ENTITY_TYPE_ID' => $entityTypeId, 'IS_DEFAULT' => 'Y'])
            ->fetchObject();

        if (!empty($cat->getId()))
        {
            $result->setData(['ID' => $cat->getId()]);

            $cat->setName(Constants::DYNAMIC_CATEGORY_DEFAULT_TITLE);
            //$cat->set('CODE', Constants::DYNAMIC_CATEGORY_DEFAULT_CODE);
            $changeRes = $cat->save();
            if ($changeRes->isSuccess())
            {
                $currentStatuses = StatusTable::query()
                    ->setSelect(['*'])
                    ->setFilter(['=CATEGORY_ID' => $cat->getId()])
                    ->fetchCollection()
                    ->getAll();

                $updRes = static::setCategoryStatuses(
                    $currentStatuses,
                    static::getStatusList(Constants::DYNAMIC_CATEGORY_DEFAULT_CODE),
                    $entityTypeId,
                    $cat->getId()
                );
                if (!$updRes->isSuccess())
                {
                    $result->addErrors($updRes->getErrors());
                }
            }
            else
            {
                $result->addErrors($changeRes->getErrors());
            }
        }
        return $result;
    }

    /**
     * @param array $currentStatuses
     * @param array $newStatuses
     * @param int $entityTypeId
     * @param int $categoryId
     * @return \Bitrix\Main\Result
     * @throws \Exception
     */
    protected static function setCategoryStatuses(
        array $currentStatuses, array $newStatuses, int $entityTypeId, int $categoryId): Result
    {
        $result = new Result();

        foreach ($currentStatuses as $status)
        {
            $statusCode = substr($status->getStatusId(), strlen(static::getStatusPrefix($entityTypeId, $categoryId)));
            if (array_key_exists($statusCode, $newStatuses))
            {
                $status->setName($newStatuses[$statusCode]["NAME"]);
                $status->setSort($newStatuses[$statusCode]["SORT"]);
                $status->setColor($newStatuses[$statusCode]["COLOR"]);
                $status->setSemantics($newStatuses[$statusCode]["SEMANTICS"]);
                $res = $status->save();
                unset($newStatuses[$statusCode]);
            }
            else
            {
                if (!$status->getSystem())
                {
                    $res = $status->delete();
                }
            }

            if (!empty($res) && !$res->isSuccess()){
                $result->addErrors($res->getErrors());
            }
        }

        foreach ($newStatuses as $statusId => $statusData)
        {
            $fullStatusId = static::getStatusPrefix($entityTypeId, $categoryId) . $statusId;
            $entityId     = "DYNAMIC_".$entityTypeId."_STAGE_".$categoryId;
            $statusObj = StatusTable::createObject();
            $statusObj->setEntityId($entityId);
            $statusObj->setStatusId($fullStatusId);
            $statusObj->setCategoryId($categoryId);
            $statusObj->setName($statusData["NAME"]);
            $statusObj->setSort($statusData["SORT"]);
            $statusObj->setColor($statusData["COLOR"]);
            $statusObj->setSemantics($statusData["SEMANTICS"]);
            $res = $statusObj->save();
            if (!$res->isSuccess()){
                $result->addErrors($res->getErrors());
            }
        }

        return $result;
    }

    /**
     * @param string $categoryCode
     * @return array
     */
    protected static function getStatusList(string $categoryCode): array
    {
        $moduleId = ServiceManager::getModuleId();
        $categories = [
            Constants::DYNAMIC_CATEGORY_DEFAULT_CODE => [
                Constants::DYNAMIC_STAGE_DEFAULT_NEW => [
                    "NAME"      => Loc::getMessage($moduleId.'_DEFAULT_STAGE_NEW_TITLE'),
                    "SORT"      => 10,
                    "COLOR"     => "#47E4C2",
                    "SEMANTICS" => PhaseSemantics::PROCESS,
                ],
                Constants::DYNAMIC_STAGE_DEFAULT_REVIEW => [
                    "NAME"      => Loc::getMessage($moduleId.'_DEFAULT_STAGE_REVIEW_TITLE'),
                    "SORT"      => 20,
                    "COLOR"     => "#FFA900",
                    "SEMANTICS" => PhaseSemantics::PROCESS,
                ],

                Constants::DYNAMIC_STAGE_DEFAULT_ASSIGNED => [
                    "NAME"      => Loc::getMessage($moduleId.'_DEFAULT_STAGE_ASSIGNED_TITLE'),
                    "SORT"      => 30,
                    "COLOR"     => "#025EA1",
                    "SEMANTICS" => PhaseSemantics::PROCESS,
                ],
                /*Constants::DYNAMIC_STAGE_DEFAULT_POSTPONED => [
                    "NAME"      => Loc::getMessage($moduleId.'_DEFAULT_STAGE_POSTPONED_TITLE'),
                    "SORT"      => 40,
                    "COLOR"     => "#456078",
                    "SEMANTICS" => PhaseSemantics::PROCESS,
                ],*/
                Constants::DYNAMIC_STAGE_DEFAULT_SUCCESS => [
                    "NAME"      => Loc::getMessage($moduleId.'_DEFAULT_STAGE_SUCCESS_TITLE'),
                    "SORT"      => 50,
                    "COLOR"     => "#90EE90",
                    "SEMANTICS" => PhaseSemantics::SUCCESS,
                ],
                Constants::DYNAMIC_STAGE_DEFAULT_FAIL => [
                    "NAME"      => Loc::getMessage($moduleId.'_DEFAULT_STAGE_FAIL_TITLE'),
                    "SORT"      => 60,
                    "COLOR"     => "#F1361B",
                    "SEMANTICS" => PhaseSemantics::FAILURE,
                ],
            ],
        ];

        return is_array($categories[$categoryCode]) ? $categories[$categoryCode] : [];
    }

    /**
     * @param int $entityTypeId
     * @param int $categoryId
     * @return string
     */
    public static function getStatusPrefix(int $entityTypeId, int $categoryId): string
    {
        return CCrmStatus::getDynamicEntityStatusPrefix($entityTypeId, $categoryId) . ":";
    }
}
