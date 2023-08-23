<?php
namespace Cbit\Mc\Profile\Component;

if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die();

use Bitrix\Main\Loader;
use Bitrix\Main\Localization\Loc;
use Bitrix\Main\UserTable;
use Cbit\Mc\Core\Component\BaseComponent;
use Cbit\Mc\Core\Helper\Main\User;
use Cbit\Mc\Profile\Service\Approval\PersonalPhoto;
use Exception;

/**
 * Class PhotoApproverDetail
 * @package Cbit\Mc\Profile\Component
 */
class PhotoApproverDetail extends BaseComponent
{
    public string $moduleId;
    private bool $requiredParamsFilled = false;

    public function __construct($component = null)
    {
        $this->moduleId = 'cbit.mc.profile';
        parent::__construct($component);
    }

    /**
     * @param $arParams
     * @return array
     */
    public function onPrepareComponentParams($arParams): array
    {
        try
        {
            if (empty($arParams['NEW_FILE_ID']) || empty($arParams['USER_ID']))
            {
                $this->requiredParamsFilled = false;
                throw new Exception(Loc::getMessage($this->moduleId."_COMPONENT_REQUIRED_PARAMS_EMPTY"));
            }
            $this->requiredParamsFilled = true;
        }
        catch (Exception $e)
        {
            $this->showMessage($e->getMessage(), true);
        }

        return array_merge($arParams, [
            "CACHE_TYPE" => $arParams["CACHE_TYPE"] ?? "N",
            "CACHE_TIME" => $arParams["CACHE_TIME"] ?? 0,
        ]);
    }

    /**
     * @return array
     * @throws \Exception
     */
    public function getResult(): array
    {
        $user = UserTable::query()
            ->setFilter(['ID' => $this->arParams['USER_ID']])
            ->setSelect(['NAME', 'LAST_NAME'])
            ->fetch();
        $fullName = '';
        if (!empty($user))
        {
            $fullName = $user['NAME'] . " " . $user['LAST_NAME'];
        }
        return [
            'USER_FULL_NAME'    => $fullName,
            'USER_PROFILE_LINK' => User::getUserProfileLink((int)$this->arParams['USER_ID'])
        ];
    }

    /**
     * @return bool
     * @throws \Exception
     */
    function checkRequirements(): bool
    {
        if (!Loader::includeModule($this->moduleId))
        {
            throw new Exception("Can not include module " . $this->moduleId);
        }

        if (!PersonalPhoto::getInstance()->canCurrentUserApprovePhoto())
        {
            throw new Exception(Loc::getMessage($this->moduleId."_COMPONENT_ERROR_PERMISSIONS"));
        }

        if (!PersonalPhoto::getInstance()->isPhotoInQueue($this->arParams['USER_ID'], $this->arParams['NEW_FILE_ID']))
        {
            throw new Exception(Loc::getMessage($this->moduleId."_COMPONENT_ERROR_PHOTO_NOT_IN_QUEUE"));
        }

        if (!$this->requiredParamsFilled)
        {
            throw new Exception("Required params not filled");
        }

        return true;
    }
}