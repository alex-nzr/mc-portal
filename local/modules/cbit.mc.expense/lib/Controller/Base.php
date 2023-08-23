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
namespace Cbit\Mc\Expense\Controller;

use Bitrix\Main\Engine\Action;
use Bitrix\Main\Engine\ActionFilter\Authentication;
use Bitrix\Main\Engine\ActionFilter\Csrf;
use Bitrix\Main\Engine\ActionFilter\HttpMethod;
use Bitrix\Main\Engine\Controller;
use Bitrix\Main\Error;
use Bitrix\Main\Localization\Loc;
use Bitrix\Main\UserTable;
use Cbit\Mc\Core\Service\Integration\Zup\Fields;
use Cbit\Mc\Expense\Entity\Dynamic;
use Cbit\Mc\Expense\Helper\Crm\Timeline;
use Cbit\Mc\Expense\Internals\Model\ExternalParticipantsTable;
use Cbit\Mc\Expense\Service\Container;
use Cbit\Mc\Expense\Service\Csv\RatingParser;
use CFile;
use CUser;

/**
 * Class Base
 * @package Cbit\Mc\Expense\Controller
 */
class Base extends Controller
{
    /**
     * @param \Bitrix\Main\Engine\Action $action
     * @return bool
     * @throws \Exception
     */
    protected function processBeforeAction(Action $action): bool
    {
        Container::getInstance()->getLocalization()->loadMessages();
        return parent::processBeforeAction($action);
    }

    /**
     * @return array
     * @throws \Exception
     */
    public function splitRequestAmountAction(): array
    {
        $postData = $this->request->getPostList()->toArray();
        $typeId   = Dynamic::getInstance()->getTypeId();
        $entityId = (int)$postData['ITEM_ID'];
        $allowedFields = [
            'OPPORTUNITY', 'UF_CRM_'.$typeId.'_AMOUNT_REJECTED','UF_CRM_'.$typeId.'_REASON',
        ];

        if ($entityId > 0)
        {
            $item = Dynamic::getInstance()->getById($entityId);
            if (empty($item))
            {
                $this->addError(new Error("Item with id '$entityId' not found"));
            }
            else
            {
                if (Container::getInstance()->getUserPermissions()->canUserSplitAmount($item))
                {
                    foreach ($postData as $field => $value)
                    {
                        if (in_array($field, $allowedFields))
                        {
                            if ($field === 'UF_CRM_'.$typeId.'_AMOUNT_REJECTED')
                            {
                                $currentValue = 0;
                                $currentValueAr = explode('|', $item->get($field));
                                if (is_array($currentValueAr) && count($currentValueAr) === 2)
                                {
                                    $currentValue = (int)$currentValueAr[0];
                                }

                                $value = ($value + $currentValue).'|'.$item->getCurrencyId();
                            }
                            $item->set($field, $value);
                        }
                    }

                    $result = Dynamic::getInstance()
                                ->getItemFactory()
                                ->getUpdateOperation($item)
                                ->disableCheckFields()
                                ->launch();
                    if (!$result->isSuccess())
                    {
                        $this->addErrors($result->getErrors());
                    }
                }
                else
                {
                    $this->addError(new Error("No permissions to split this request"));
                }
            }
        }
        else
        {
            $this->addError(new Error('ITEM_ID is empty or 0'));
        }
        return [];
    }

    /**
     * @return array
     * @throws \Exception
     */
    public function rejectRequestAction(): array
    {
        $postData     = $this->request->getPostList()->toArray();
        $entityId     = (int)$postData['ITEM_ID'];
        $reasonText   = Loc::getMessage('REJECT_REASON_TEXT_PREFIX') . $postData['REJECT_REASON'];
        if ($entityId > 0 && !empty($reasonText))
        {
            $item = Dynamic::getInstance()->getById($entityId);
            if (!empty($item))
            {
                if (Container::getInstance()->getUserPermissions()->canUserRejectRequest($item))
                {
                    $commentId = Timeline::createComment($entityId, $reasonText, true);
                    if ($commentId > 0)
                    {
                        $result = Dynamic::getInstance()->moveItemToRejectStage($item);
                        if(!$result->isSuccess())
                        {
                            $this->addErrors($result->getErrors());
                        }
                    }
                    else
                    {
                        $this->addError(new Error('Error on creating comment'));
                    }
                }
                else
                {
                    $this->addError(new Error("No permissions to reject, or request at stage that cannot be rejected"));
                }
            }
            else
            {
                $this->addError(new Error("Item with id '$entityId' not found"));
            }
        }
        else
        {
            $this->addError(new Error('entityId or rejectReason is empty'));
        }
        return [];
    }

    /**
     * @return array
     * @throws \Exception
     */
    public function addExternalParticipantAction(): array
    {
        $postData = $this->request->getPostList()->toArray();

        $addRes = ExternalParticipantsTable::add([
            'NAME'        => $postData['NAME'],
            'LAST_NAME'   => $postData['LAST_NAME'],
            'SECOND_NAME' => $postData['SECOND_NAME'],
            'COMPANY'     => $postData['COMPANY'],
            'POSITION'    => $postData['POSITION'],
        ]);
        if ($addRes->isSuccess())
        {
            return array_merge($postData, ['ID' => $addRes->getId()]);
        }
        else
        {
            $this->addErrors($addRes->getErrors());
            return [];
        }
    }

    /**
     * @return array
     * @throws \Exception
     */
    public function updateUsersRatingAction(): array
    {
        if (!Container::getInstance()->getUserPermissions()->canUserUploadRatingFile())
        {
            $this->addError(new Error('No permissions to update rating'));
            return [];
        }

        $file = $this->getRequest()->getFile('UF_TYB_RATING_FILE');

        if (!empty($file))
        {
            $fid = (int)CFile::SaveFile($file, 'tmp');

            if ($fid > 0)
            {
                $ratingArr = RatingParser::getArrayFromCsvFile($fid);
                if (!is_array($ratingArr))
                {
                    $this->addError(new Error('Error on parsing csv'));
                }
                else
                {
                    $usersData = [];
                    $usersResult = UserTable::query()
                        ->setSelect([
                            'ID', Fields::getFmnoUfCode()
                        ])
                        ->whereNotNull(Fields::getFmnoUfCode())
                        ->where('ACTIVE', 'Y')
                        ->fetchAll();

                    foreach ($usersResult as $user)
                    {
                        $usersData[$user[Fields::getFmnoUfCode()]] = $user['ID'];
                    }
                    unset($usersResult);

                    $cUser = new CUser;
                    foreach ($ratingArr as $fmno => $ratingValue)
                    {
                        if (array_key_exists($fmno, $usersData))
                        {
                            $updateResult = $cUser->Update($usersData[$fmno], ['UF_TYB_RATING' => $ratingValue]);
                            if (!$updateResult)
                            {
                                $this->addError(new Error($cUser->LAST_ERROR));
                            }
                        }
                    }
                }

                CFile::Delete($fid);
            }
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