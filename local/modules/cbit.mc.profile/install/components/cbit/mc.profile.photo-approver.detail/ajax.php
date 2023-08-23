<?php
namespace Cbit\Mc\Profile\Component;

if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die();

use Bitrix\Main\Engine\ActionFilter\Authentication;
use Bitrix\Main\Engine\ActionFilter\Csrf;
use Bitrix\Main\Engine\ActionFilter\HttpMethod;
use Bitrix\Main\Engine\Controller;
use Bitrix\Main\Error;
use Cbit\Mc\Profile\Service\Approval\PersonalPhoto;
use Exception;

class PhotoApproverDetailAjax extends Controller
{
    /**
     * @return array
     */
    protected function getDefaultPreFilters(): array
    {
        return [
            new Authentication(),
            new Csrf(),
        ];
    }

    /**
     * @return array
     */
    public function configureActions(): array
    {
        return [
            'approve'     => [
                '+prefilters' => [ new HttpMethod([HttpMethod::METHOD_POST]) ],
            ],
            'decline'     => [
                '+prefilters' => [ new HttpMethod([HttpMethod::METHOD_POST]) ],
            ],
        ];
    }

    /**
     * @param int $userId
     * @param int $newFileId
     * @return array|null
     */
    public function approveAction(int $userId, int $newFileId): ?array
    {
        try
        {
            $result = PersonalPhoto::getInstance()->approveNewProfilePhoto($userId, $newFileId);
            if ($result->isSuccess())
            {
                return [];
            }
            else
            {
                throw new Exception($result->getErrorMessages()[0]);
            }
        }
        catch (Exception $e)
        {
            $this->addError(new Error($e->getMessage()));
            return null;
        }
    }

    /**
     * @param int $userId
     * @param int $newFileId
     * @param string $reason
     * @return array|null
     */
    public function declineAction(int $userId, int $newFileId, string $reason = ''): ?array
    {
        try
        {
            $result = PersonalPhoto::getInstance()->declineNewProfilePhoto($userId, $newFileId, $reason);
            if ($result->isSuccess())
            {
                return [];
            }
            else
            {
                throw new Exception($result->getErrorMessages()[0]);
            }
        }
        catch (Exception $e)
        {
            $this->addError(new Error($e->getMessage()));
            return null;
        }
    }
}