<?php
/**
 * ==================================================
 * Developer: Alexey Nazarov
 * E-mail: alsnazarov@1cbit.ru
 * Copyright (c) 2019 - 2022
 * ==================================================
 * mc-portal - Base.php
 * 17.01.2023 12:17
 * ==================================================
 */
namespace Cbit\Mc\RI\Controller;

use Bitrix\Main\Engine\ActionFilter\Authentication;
use Bitrix\Main\Engine\ActionFilter\Csrf;
use Bitrix\Main\Engine\ActionFilter\HttpMethod;
use Bitrix\Main\Engine\Controller;
use Bitrix\Main\Engine\CurrentUser;
use Bitrix\Main\Error;
use Cbit\Mc\RI\Config\Constants;
use Cbit\Mc\RI\Entity\Dynamic;
use Cbit\Mc\RI\Service\Container;

/**
 * Class Base
 * @package Cbit\Mc\RI\Controller
 */
class Base extends Controller
{
    /**
     * @return array
     * @throws \Exception
     */
    public function setRequestScoreAction(): array
    {
        $postData = $this->request->getPostList()->toArray();
        $typeId   = Dynamic::getInstance()->getTypeId();
        if (!empty($postData['ITEM_ID']))
        {
            $item = Dynamic::getInstance()->getById((int)$postData['ITEM_ID']);
            if (!empty($item))
            {
                $fields = [
                    "UF_CRM_".$typeId."_SCORE_SPEED"         => (int)$postData['SCORING_FORM_SPEED'],
                    "UF_CRM_".$typeId."_SCORE_WORK"          => (int)$postData['SCORING_FORM_WORK'],
                    "UF_CRM_".$typeId."_SCORE_COMMUNICATION" => (int)$postData['SCORING_FORM_COMMUNICATIONS'],
                    "UF_CRM_".$typeId."_SCORE_COMMENT"       => (string)$postData['SCORING_FORM_COMMENT'],
                ];
                $updResult = Dynamic::getInstance()->update($item, $fields);
                if (!$updResult->isSuccess())
                {
                    $this->addErrors($updResult->getErrors());
                }
            }
            else
            {
                $this->addError(new Error("Could not find item with id - " . $postData['ITEM_ID']));
            }
        }
        else
        {
            $this->addError(new Error('ITEM_ID is empty'));
        }
        return [];
    }

    /**
     * @return array
     * @throws \Exception
     */
    public function cancelRequestAction(): array
    {
        $postData = $this->request->getPostList()->toArray();
        $typeId   = Dynamic::getInstance()->getTypeId();
        if (!empty($postData['ITEM_ID']))
        {
            $item = Dynamic::getInstance()->getById((int)$postData['ITEM_ID']);
            if (empty($item))
            {
                $this->addError(new Error("Item with id '".$postData['ITEM_ID']."' not found"));
            }
            else
            {
                if (Container::getInstance()->getUserPermissions()->canUserCancelRequest($item))
                {
                    $item->setStageId(
                        Dynamic::getInstance()->getStatusPrefix($item->getCategoryId()).Constants::DYNAMIC_STAGE_DEFAULT_FAIL
                    );
                    $item->set('UF_CRM_'.$typeId.'_CANCEL_REASON', $postData['CANCEL_FORM_REASON']);
                    $item->set('UF_CRM_'.$typeId.'_CANCEL_COMMENT', $postData['CANCEL_FORM_COMMENT']);
                    $result = Dynamic::getInstance()->getItemFactory()->getUpdateOperation($item)->launch();
                    if (!$result->isSuccess())
                    {
                        $this->addErrors($result->getErrors());
                    }
                }
                else
                {
                    $this->addError(new Error("No permissions to cancel this request"));
                }
            }
        }
        else
        {
            $this->addError(new Error('ITEM_ID is empty'));
        }
        return [];
    }

    /**
     * @param int $itemId
     * @return array
     * @throws \Exception
     */
    public function getCurrentStageAction(int $itemId): array
    {
        if ($itemId > 0)
        {
            $item = Dynamic::getInstance()->getById($itemId);
            if (empty($item))
            {
                $this->addError(new Error("Item with id $itemId not found"));
            }
            else
            {
                $stagePrefix = Dynamic::getInstance()->getStatusPrefix($item->getCategoryId());
                $stageCode = str_replace($stagePrefix, '', $item->getStageId());
                $allowQuickCancel = in_array($stageCode, [
                    Constants::DYNAMIC_STAGE_DEFAULT_NEW,
                    Constants::DYNAMIC_STAGE_DEFAULT_REVIEW,
                ]);
                return [
                    'stageCode' => $stageCode,
                    'allowQuickCancelling' => $allowQuickCancel ? 'Y' : 'N'
                ];
            }
        }
        else
        {
            $this->addError(new Error('itemId is empty'));
        }
        return [];
    }

    /**
     * @param int $itemId
     * @return array
     * @throws \Exception
     */
    public function cancelRequestWithoutReasonAction(int $itemId): array
    {
        $typeId   = Dynamic::getInstance()->getTypeId();
        if ($itemId > 0)
        {
            $item = Dynamic::getInstance()->getById($itemId);
            if (empty($item))
            {
                $this->addError(new Error("Item with id $itemId not found"));
            }
            else
            {
                if (Container::getInstance()->getUserPermissions()->canUserCancelRequest($item))
                {
                    $item->setStageId(
                        Dynamic::getInstance()->getStatusPrefix($item->getCategoryId()).Constants::DYNAMIC_STAGE_DEFAULT_FAIL
                    );

                    if (!empty($GLOBALS['USER']))
                    {
                        $item->set('UF_CRM_'.$typeId.'_CANCEL_COMMENT', 'Cancelled by ' . CurrentUser::get()->getFormattedName());
                    }

                    $result = Dynamic::getInstance()->getItemFactory()->getUpdateOperation($item)->launch();
                    if (!$result->isSuccess())
                    {
                        $this->addErrors($result->getErrors());
                    }
                }
                else
                {
                    $this->addError(new Error("No permissions to cancel this request"));
                }
            }
        }
        else
        {
            $this->addError(new Error('itemId is empty'));
        }
        return [];
    }

    /**
     * @return array
     */
    protected function getDefaultPreFilters(): array
    {
        return [
            new HttpMethod([HttpMethod::METHOD_POST]),
            new Csrf(),
            new Authentication()
        ];
    }

    /**
     * @return array
     */
    public function configureActions(): array
    {
        return [];
    }
}