<?php
/**
 * ==================================================
 * Developer: Alexey Nazarov
 * E-mail: alsnazarov@1cbit.ru
 * Copyright (c) 2019 - 2022
 * ==================================================
 * mc-portal - User.php
 * 07.11.2022 19:34
 * ==================================================
 */


namespace Cbit\Mc\Profile\Handler;

use Bitrix\Main\UserTable;
use Cbit\Mc\Profile\Helper\Intranet\LeftMenu;
use Cbit\Mc\Profile\Service\Approval\PersonalPhoto;
use Cbit\Mc\Profile\Service\Field\DataFiller;
use Exception;

/**
 * Class User
 * @package Cbit\Mc\Profile\Handler
 */
class User
{
    /**
     * @param $arFields
     * @return bool
     */
    public static function onBeforeUpdate(&$arFields): bool
    {
        try
        {
            $userId = (int)$arFields['ID'];
            if(!empty($arFields['PERSONAL_PHOTO']) && is_array($arFields['PERSONAL_PHOTO']))
            {
                if (!($arFields['DELETE_PERSONAL_PHOTO_ID'] > 0 || $arFields['IS_APPROVED'] === 'Y'))
                {
                    PersonalPhoto::getInstance()->startApprovingProcess($userId, $arFields['PERSONAL_PHOTO']);
                    unset($arFields['PERSONAL_PHOTO']);
                }
            }

            if (isset($arFields['UF_ASSISTANT']))
            {
                if (is_array($arFields['UF_ASSISTANT']) && (count($arFields['UF_ASSISTANT']) > 4))
                {
                    $arFields['UF_ASSISTANT'] = array_slice($arFields['UF_ASSISTANT'], 0, 4);
                }

                $userData = (array)UserTable::query()
                    ->setFilter(['ID' => $userId])
                    ->setSelect(['UF_ASSISTANT'])
                    ->fetch();

                $currentAssistants = is_array($userData['UF_ASSISTANT']) ? $userData['UF_ASSISTANT'] : [];

                DataFiller::getInstance()->saveCurrentAssistants($currentAssistants);
            }

            return true;
        }
        catch(Exception $e)
        {
            $GLOBALS['APPLICATION']->ThrowException($e->getMessage());
            return false;
        }
    }

    /**
     * @param $arFields
     * @return void
     * @throws \Exception
     */
    public static function onAfterUpdate($arFields): void
    {
        $userId = (int)$arFields["ID"];
        if($userId > 0)
        {
            if ($arFields['DELETE_PERSONAL_PHOTO_ID'] > 0)
            {
                PersonalPhoto::getInstance()->deletePhotoFromCollection(
                    $userId, $arFields['DELETE_PERSONAL_PHOTO_ID']
                );
            }

            if (isset($arFields['UF_ASSISTANT']))
            {
                DataFiller::getInstance()->setExecutiveFieldByAssistant($userId, (array)$arFields['UF_ASSISTANT']);
            }
        }
    }

    /**
     * @param $arFields
     * @throws \Exception
     */
    public static function onAfterAdd($arFields): void
    {
        $userId = (int)$arFields["ID"];
        if($userId > 0)
        {
            LeftMenu::setMenuForAllUsers();
            DataFiller::getInstance()->setUploadedDocsLink($userId);
            DataFiller::getInstance()->setDefaultDepartment($userId);
        }
    }
}