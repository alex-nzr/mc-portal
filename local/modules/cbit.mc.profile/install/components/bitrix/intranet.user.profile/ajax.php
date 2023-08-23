<?php
if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();

use Bitrix\Intranet\Util;
use Bitrix\Main\Engine\Action;
use Bitrix\Main\Engine\ActionFilter\Authentication;
use Bitrix\Main\Engine\ActionFilter\Csrf;
use Bitrix\Main\Engine\ActionFilter\HttpMethod;
use Bitrix\Main\Loader;
use Bitrix\Main\Localization\Loc;
use Bitrix\Main\Engine\CurrentUser;
use Bitrix\Main\Config\Option;
use Bitrix\Main\Engine\Response\Component;
use Bitrix\Main\UserTable;
use Bitrix\Socialservices\Network;
use Cbit\Mc\Core\Internals\Orm\Modifier;
use Cbit\Mc\Profile\Service\Access\Permission;
use Cbit\Mc\Profile\Service\Approval\PersonalPhoto;
use Cbit\Mc\Profile\Service\Integration\Zup\Education;

class McUserProfileComponentAjaxController extends \Bitrix\Main\Engine\Controller
{
    protected int $userId;

    /**
     * @param \Bitrix\Main\Engine\Action $action
     * @return bool
     */
    protected function processBeforeAction(Action $action): bool
    {
        parent::processBeforeAction($action);

        if ($action->getName() === 'showWidget')
        {
            return true;
        }

        if (!$this->getRequest()->isPost() || !$this->getRequest()->getPost('signedParameters'))
        {
            return false;
        }

        $parameters = $this->getUnsignedParameters();

        if (isset($parameters['ID']))
        {
            $this->userId = (int)$parameters['ID'];
        }
        else
        {
            $userId = $this->getRequest()->getPost('userId');
            if (!empty($userId))
            {
                $this->userId = (int)$userId;
            }
            else
            {
                return false;
            }
        }

        return true;
    }

    /**
     * @return bool
     */
    protected function canEditProfile(): bool
    {
        return Permission::canUserEditProfile($this->userId);
    }

    /**
     * @return array
     */
    public function addEmployeeEducationAction(): array
    {
        $postData = $this->request->getPostList()->toArray();
        $postData['USER_ID'] = $this->userId;
        $result = Education::sendEmployeeEducation($postData);
        if (!$result->isSuccess())
        {
            $this->addErrors($result->getErrors());
        }
        return [];
    }

    /**
     * @return bool
     */
    public function fireUserAction(): bool
    {
        $currentUser = CurrentUser::get();

        return Util::deactivateUser([
            'userId' => $this->userId,
            'currentUserId' => $currentUser->getId(),
            'isCurrentUserAdmin' => $currentUser->isAdmin()
        ]);
    }

    /**
     * @return bool
     */
    public function hireUserAction(): bool
    {
        $currentUser = CurrentUser::get();

        return Util::activateUser([
            'userId' => $this->userId,
            'currentUserId' => $currentUser->getId(),
            'isCurrentUserAdmin' => $currentUser->isAdmin()
        ]);
    }

    /**
     * @return bool
     * @throws \Bitrix\Main\LoaderException
     */
    public function deleteUserAction(): bool
    {
        global $APPLICATION;

        if (!$this->canEditProfile())
        {
            return false;
        }

        $user = new CUser;
        $res = $user->Delete($this->userId);

        if (!$res)
        {
            if (!empty($user->LAST_ERROR))
            {
                $error = $user->LAST_ERROR;
            }
            else
            {
                $ex = $APPLICATION->GetException();
                $error = ($ex instanceof CApplicationException)
                    ? $ex->GetString() : GetMessage('INTRANET_USER_PROFILE_DELETE_ERROR');
            }

            $this->addError(new \Bitrix\Main\Error($error));

            return false;
        }

        return true;
    }

    /**
     * @param $departmentId
     * @param false $isEmail
     * @return array|false|int|string|null
     * @throws \Bitrix\Main\LoaderException
     */
    public function moveToIntranetAction($departmentId, $isEmail = false)
    {
        if (!CurrentUser::get()->isAdmin())
        {
            return false;
        }

        if (intval($departmentId) <= 0)
        {
            $this->addError(new \Bitrix\Main\Error(Loc::getMessage("INTRANET_USER_PROFILE_EMPTY_DEPARTMENT_ERROR")));
            return false;
        }

        if ($isEmail == 'Y')
        {
            $ID_TRANSFERRED = CIntranetInviteDialog::TransferEmailUser($this->userId, array(
                'UF_DEPARTMENT' => (int) $departmentId
            ));

            if (!$ID_TRANSFERRED)
            {
                if($e = $GLOBALS["APPLICATION"]->GetException())
                {
                    $strError = $e->GetString();
                    return array($strError);
                }
            }
            else
            {
                return $ID_TRANSFERRED;
            }
        }
        else
        {
            $obUser = new CUser;
            $arGroups = $obUser->GetUserGroup($this->userId);
            $ID = 0;
            if (is_array($arGroups))
            {
                $arGroups = array_diff($arGroups, array(11, 13));
                $arGroups[] = "11";

                $arNewFields = array(
                    "GROUP_ID" => $arGroups,
                    "UF_DEPARTMENT" => array(intval($departmentId))
                );

                $ID = $obUser->Update($this->userId, $arNewFields);
            }
            if(!$ID)
            {
                $this->addError(new \Bitrix\Main\Error(preg_split("/<br>/", $obUser->LAST_ERROR)));
                return false;
            }
            else
            {
                if (Loader::includeModule("im"))
                {
                    $arMessageFields = array(
                        "TO_USER_ID" => $this->userId,
                        "FROM_USER_ID" => 0,
                        "NOTIFY_TYPE" => IM_NOTIFY_SYSTEM,
                        "NOTIFY_MODULE" => "bitrix24",
                        "NOTIFY_MESSAGE" => Loc::getMessage("INTRANET_USER_PROFILE_MOVE_TO_INTRANET_NOTIFY"),
                    );
                    CIMNotify::Add($arMessageFields);
                }

                CIntranetEventHandlers::ClearAllUsersCache($this->userId);

                return Loc::getMessage("INTRANET_USER_PROFILE_MOVE_TO_INTRANET_SUCCESS");
            }
        }
        return [];
    }

    /**
     * @return array|false|mixed
     * @throws \Exception
     */
    public function loadPhotoAction()
    {
        if (!$this->canEditProfile())
        {
            return false;
        }

        $approvedFileId = (int)$this->getRequest()->getPost("newPhotoFromGalleryId");

        if ($approvedFileId > 0)
        {
            PersonalPhoto::getInstance()->approveNewProfilePhoto($this->userId, $approvedFileId, true);
        }
        else
        {
            $newPhotoFile = $this->getRequest()->getFile("newPhoto");

            $userData = UserTable::getList(array(
                "select" => array('ID', 'PERSONAL_PHOTO'),
                "filter" => array(
                    "=ID" => $this->userId
                ),
            ))->fetch();

            if ($userData["PERSONAL_PHOTO"])
            {
                $newPhotoFile["old_file"] = $userData["PERSONAL_PHOTO"];
                $newPhotoFile["del"] = $userData["PERSONAL_PHOTO"];
            }

            $user = new CUser;
            $res = $user->Update($this->userId, [
                "PERSONAL_PHOTO" => $newPhotoFile
            ]);

            if (!$res)
            {
                $this->addError(new \Bitrix\Main\Error($user->LAST_ERROR));
                return false;
            }
        }

        $newUserData = UserTable::getList(array(
            "select" => array('ID', 'PERSONAL_PHOTO'),
            "filter" => array(
                "=ID" => $this->userId
            ),
        ))->fetch();

        if ($newUserData["PERSONAL_PHOTO"] > 0)
        {
            $file = CFile::GetFileArray($newUserData["PERSONAL_PHOTO"]);
            if ($file !== false)
            {
                if (!defined('BX_RESIZE_IMAGE_PROPORTIONAL'))
                {
                    define('BX_RESIZE_IMAGE_PROPORTIONAL', 1);
                }
                $fileTmp = CFile::ResizeImageGet(
                    $file,
                    ["width" => 212, "height" => 212],
                    BX_RESIZE_IMAGE_PROPORTIONAL,
                    false,
                    false,
                    true
                );

                return $fileTmp["src"];
            }
        }

        return [];
    }

    /**
     * @param int|null $fileId
     * @return bool|int[]
     * @throws \Exception
     */
    public function deletePhotoAction(?int $fileId = null)
    {
        if (!$this->canEditProfile())
        {
            return false;
        }

        if ($fileId === null)
        {
            $userData = UserTable::getList(array(
                "select" => array('ID', 'PERSONAL_PHOTO'),
                "filter" => array(
                    "=ID" => $this->userId
                ),
            ))->fetch();

            if (!$userData["PERSONAL_PHOTO"])
            {
                return false;
            }

            $fields = array(
                "DELETE_PERSONAL_PHOTO_ID" => (int)$userData["PERSONAL_PHOTO"],
                "PERSONAL_PHOTO" => array(
                    "old_file" => $userData["PERSONAL_PHOTO"],
                    "del" => $userData["PERSONAL_PHOTO"]
                )
            );

            $user = new CUser;
            $res = $user->Update($this->userId, $fields);

            if (!$res)
            {
                $this->addError(new \Bitrix\Main\Error($user->LAST_ERROR));
                return false;
            }

            $deletedFileId = (int)$userData["PERSONAL_PHOTO"];
        }
        else
        {
            CFile::Delete($fileId);
            PersonalPhoto::getInstance()->deletePhotoFromCollection($this->userId, $fileId);
            $deletedFileId = $fileId;
        }

        return [
            'DELETED_FILE_ID' => $deletedFileId
        ];
    }

    /**
     * @param $employeesGroupId
     * @param $portalAdminGroupId
     */
    protected function getGroupsId(&$employeesGroupId, &$portalAdminGroupId)
    {
        [ $employeesGroupId, $portalAdminGroupId ] = Util::getGroupsId();
    }

    /**
     * @return bool
     */
    public function setAdminRightsAction(): bool
    {
        $currentUser = CurrentUser::get();

        return Util::setAdminRights([
            'userId' => $this->userId,
            'currentUserId' => $currentUser->getId(),
            'isCurrentUserAdmin' => $currentUser->isAdmin()
        ]);
    }

    /**
     * @return bool
     */
    public function removeAdminRightsAction(): bool
    {
        $currentUser = CurrentUser::get();

        return Util::removeAdminRights([
            'userId' => $this->userId,
            'currentUserId' => $currentUser->getId(),
            'isCurrentUserAdmin' => $currentUser->isAdmin()
        ]);
    }

    /**
     * @param string $phone
     * @return bool
     * @throws \Exception
     */
    public function sendSmsForAppAction($phone = ""): bool
    {
        if (!$this->canEditProfile())
        {
            return false;
        }

        if (empty($phone))
        {
            return false;
        }

        if (Loader::includeModule('socialservices'))
        {
            Network::sendMobileApplicationLink($phone, LANGUAGE_ID);
        }

        return true;
    }

    /**
     * @return bool
     * @throws \Exception
     */
    public function setIntegratorRightsAction(): bool
    {
        /*global $USER;

        if (!(Loader::includeModule("bitrix24") && CBitrix24::IsPortalAdmin((int)CurrentUser::get()->getId())))
        {
            return false;
        }

        $userData = UserTable::getList(array(
            "select" => array('ID', 'EMAIL', 'UF_DEPARTMENT', 'ACTIVE'),
            "filter" => array(
                "=ID" => $this->userId
            ),
        ))->fetch();

        if (!check_email($userData["EMAIL"]))
        {
            $this->addError(new \Bitrix\Main\Error(Loc::getMessage("INTRANET_USER_PROFILE_EMAIL_ERROR")));
            return false;
        }

        if (!Integrator::isMoreIntegratorsAvailable())
        {
            $this->addError(new \Bitrix\Main\Error(Loc::getMessage("INTRANET_USER_PROFILE_INTEGRATOR_COUNT_ERROR")));
            return false;
        }

        $error = "";
        if (!Integrator::checkPartnerEmail($userData["EMAIL"], $error))
        {
            $this->addError(new \Bitrix\Main\Error($error));
            return false;
        }

        $fields = array("ACTIVE" => "Y");

        if (empty($userData["UF_DEPARTMENT"]) && Loader::includeModule('iblock'))
        {
            $rsIBlock = CIBlock::GetList(array(), array("CODE" => "departments"));
            $arIBlock = $rsIBlock->Fetch();
            $iblockID = $arIBlock["ID"];

            $db_up_department = CIBlockSection::GetList(
                array(),
                array(
                    "SECTION_ID" => 0,
                    "IBLOCK_ID" => $iblockID
                )
            );
            if ($ar_up_department = $db_up_department->Fetch())
            {
                $fields["UF_DEPARTMENT"][] = $ar_up_department['ID'];
            }
        }

        //prepare groups
        $arGroups = array(1);
        $rsGroups = CGroup::GetList(
            '',
            '',
            array(
                "STRING_ID" => "PORTAL_ADMINISTRATION_".SITE_ID
            )
        );
        while($arGroup = $rsGroups->Fetch())
        {
            $arGroups[] = $arGroup["ID"];
        }

        $integratorGroupId = Integrator::getIntegratorGroupId();
        $arGroups[] = $integratorGroupId;
        $fields["GROUP_ID"] = $arGroups;

        $USER->Update($this->userId, $fields);*/

        return true;
    }

    /**
     * @param array $fieldsView
     * @param array $fieldsEdit
     * @return bool
     * @throws \Exception
     */
    public function fieldsSettingsAction($fieldsView = array(), $fieldsEdit = array()): bool
    {
        if (CurrentUser::get()->isAdmin())
        {
            return false;
        }

        $newFieldsView = array();

        if (is_array($fieldsView))
        {
            foreach ($fieldsView as $field)
            {
                $newFieldsView[] = $field["VALUE"];
            }
        }
        Option::set("intranet", "user_profile_view_fields", implode(",", $newFieldsView), SITE_ID);

        $newFieldsEdit = array();

        if (is_array($fieldsEdit))
        {
            foreach ($fieldsEdit as $field)
            {
                $newFieldsEdit[] = $field["VALUE"];
            }
        }
        Option::set("intranet", "user_profile_edit_fields", implode(",", $newFieldsEdit), SITE_ID);

        return true;
    }

    /**
     * @param string $fieldName
     * @return bool
     * @throws \Exception
     */
    public function onUserFieldAddAction($fieldName = ""): bool
    {
        if (!CurrentUser::get()->isAdmin())
        {
            return false;
        }

        if (empty($fieldName))
        {
            return false;
        }

        $viewFieldsSettings = Option::get("intranet", "user_profile_view_fields", false);
        if (!empty($viewFieldsSettings))
        {
            $viewFieldsSettings = explode(",", $viewFieldsSettings);
            $viewFieldsSettings[] = $fieldName;
            Option::set("intranet", "user_profile_view_fields", implode(",", $viewFieldsSettings), SITE_ID);
        }

        $editFieldsSettings = Option::get("intranet", "user_profile_edit_fields", false);
        if (!empty($editFieldsSettings))
        {
            $editFieldsSettings = explode(",", $editFieldsSettings);
            $editFieldsSettings[] = $fieldName;
            Option::set("intranet", "user_profile_edit_fields", implode(",", $editFieldsSettings), SITE_ID);
        }

        return true;
    }

    public function showWidgetAction(string $targetId, string $siteTemplateId, array $urls): Component
    {
        return new Component(
            'bitrix:intranet.user.profile',
            'widget',
            [
                'ID' => (int)CurrentUser::get()->getId(),
                'TARGET_ID' => $targetId,
                'SITE_TEMPLATE_ID' => $siteTemplateId,
                'PATH_TO_USER_PROFILE' => $urls['PATH_TO_USER_PROFILE'] ?? '',
                'PATH_TO_USER_STRESSLEVEL' => $urls['PATH_TO_USER_STRESSLEVEL'] ?? '',
                'PATH_TO_USER_COMMON_SECURITY' => $urls['PATH_TO_USER_COMMON_SECURITY'] ?? '',
            ]
        );
    }

    /**
     * @return array|false
     * @throws \Exception
     */
    public function loadCvFileAction()
    {
        if (!$this->canEditProfile())
        {
            return false;
        }

        $updateAr = $this->getRequest()->getFileList()->toArray();

        if (!empty($updateAr))
        {
            $user = new CUser;
            $res = $user->Update($this->userId, $updateAr);

            if (!$res)
            {
                $this->addError(new \Bitrix\Main\Error($user->LAST_ERROR));
                return false;
            }
            else
            {
                $result = [];
                $userData = (array)UserTable::query()
                    ->setSelect(array_keys($updateAr))
                    ->setFilter(['ID' => $this->userId])
                    ->fetch();
                foreach ($userData as $key => $fid) {
                    $result[$key] = CFile::GetPath($fid);
                }
                return $result;
            }
        }

        return false;
    }

    /**
     * @param string $newSummaryText
     * @return array|null
     * @throws \Bitrix\Main\LoaderException
     */
    public function updateSummaryFieldAction(string $newSummaryText): ?array
    {
        if (!$this->canEditProfile())
        {
            $this->addError(new \Bitrix\Main\Error('No permissions to edit this profile'));
        }

        $user = new CUser;
        $res = $user->Update($this->userId, [
            'UF_SHORT_SUMMARY' => Modifier::clearStringBeforeSave($newSummaryText)
        ]);

        if (!$res)
        {
            $this->addError(new \Bitrix\Main\Error($user->LAST_ERROR));
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
}
