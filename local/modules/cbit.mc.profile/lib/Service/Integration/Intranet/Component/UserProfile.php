<?php
/**
 * ==================================================
 * Developer: Alexey Nazarov
 * E-mail: alsnazarov@1cbit.ru
 * Copyright (c) 2019 - 2023
 * ==================================================
 * mc-portal - UserProfile.php
 * 24.01.2023 18:57
 * ==================================================
 */


namespace Cbit\Mc\Profile\Service\Integration\Intranet\Component;

use Bitrix\Main\Component\ParameterSigner;
use Bitrix\Main\Engine\CurrentUser;
use Bitrix\Main\Error;
use Bitrix\Main\ErrorCollection;
use Bitrix\Main\Localization\Loc;
use Bitrix\Main\UserTable;
use Cbit\Mc\Core\Service\Integration\Zup\Fields;
use Cbit\Mc\Profile\Internals\Model\User\UserPhotoTable;
use Cbit\Mc\Profile\Service\Access\Permission;
use Cbit\Mc\Profile\Service\Integration\Intranet\Component\UserProfile\Form;
use Cbit\Mc\Profile\Service\Integration\Zup\Education;
use Cbit\Mc\Profile\Service\Integration\Zup\Status;
use CComponentEngine;
use CFile;
use Exception;

/**
 * @class UserProfile
 * @package Cbit\Mc\Profile\Service\Integration\Intranet\Component
 */
class UserProfile extends \Bitrix\Intranet\Component\UserProfile
{
    /**
     * @param \CBitrixComponent | null $component
     */
    public function __construct($component = null)
    {
        parent::__construct($component);
        $this->errorCollection = new ErrorCollection();
    }

    /**
     * @return void
     * @throws \Exception
     */
    protected function initCustomSignedParameters(): void
    {
        $keysToExport = $this->listKeysSignedParameters();
        $params = array_intersect_key(
            $this->arParams,
            array_combine($keysToExport, $keysToExport)
        );
        $this->signedParameters = ParameterSigner::signParameters($this->getName(), $params);
    }

    /**
     * @return bool
     */
    protected function checkRequiredParams(): bool
    {
        if ((int)$this->arParams['ID'] <= 0)
        {
            $this->errorCollection->setError(new Error(Loc::getMessage('INTRANET_USER_PROFILE_NO_USER_ERROR')));
            return false;
        }

        return true;
    }

    /**
     * @return array|null
     */
    protected function listKeysSignedParameters(): ?array
    {
        return parent::listKeysSignedParameters();
    }

    /**
     * @return array|null
     * @throws \Exception
     */
    protected function getUserData(): ?array
    {
        $userData = parent::getUserData();

        $customFields = [
            'UF_STAFFING_CV',
            'UF_RECRUITMENT_CV',
            'UF_SHORT_SUMMARY',

            'PERSONAL_PHOTO',
            Fields::getAbsenceUfCode(),
            Fields::getZupStatusUfCode(),
            Fields::getFioEnUfCode(),
        ];

        $select = array_filter($this->getAvailableFields(), function($item){
            return (str_starts_with($item, 'UF_'));
        });

        $select = array_merge($select, $customFields);

        $additionalUserData = (array)UserTable::query()
            ->setSelect($select)
            ->setFilter(['ID' => $this->arParams["ID"]])
            ->fetch();

        $cvData = [
            'UF_STAFFING_CV_LINK' => !empty($additionalUserData['UF_STAFFING_CV']) ? CFile::GetPath($additionalUserData['UF_STAFFING_CV']) : '',
            'UF_RECRUITMENT_CV_LINK' => !empty($additionalUserData['UF_RECRUITMENT_CV']) ? CFile::GetPath($additionalUserData['UF_RECRUITMENT_CV']) : ''
        ];

        $additionalUserData["PHOTOS_COLLECTION"] = UserPhotoTable::query()
            ->setSelect(['FILE_ID', 'FILE_LINK'])
            ->setFilter(['USER_ID' => $this->arParams["ID"]])
            ->fetchAll();

        foreach ($additionalUserData["PHOTOS_COLLECTION"] as $key => $photo)
        {
            if ((int)$photo['FILE_ID'] === (int)$additionalUserData['PERSONAL_PHOTO'])
            {
                $additionalUserData["PHOTOS_COLLECTION"][$key]['IS_CURRENT_AVATAR'] = "Y";
            }
        }

        /*if(!empty($additionalUserData[Fields::ABSENCE_UF_CODE]))
        {
            $absenceAr = explode(',', $additionalUserData[Fields::ABSENCE_UF_CODE]);
            if (count($absenceAr) >= 2)
            {
                $additionalUserData["ONLINE_STATUS"]["STATUS_TEXT"] = end($absenceAr);
            }
        }*/

        $additionalUserData["ZUP_STATUS"] = $this->getZupStatus($additionalUserData[Fields::getZupStatusUfCode()]);

        $additionalUserData["EDUCATION"] = $this->getUserEducationData();

        if (is_array($userData))
        {
            return array_merge($userData, $additionalUserData, $cvData);
        }
        return null;
    }

    /**
     * @return \Cbit\Mc\Profile\Service\Integration\Intranet\Component\UserProfile\Form
     * @throws \Exception
     */
    protected function getFormInstance(): Form
    {
        if (empty($this->arResult["User"]))
        {
            $this->arResult["User"] = $this->getUserData();
        }

        if ($this->form === null)
        {
            $this->form = new Form($this->getUserId());
            $userData   = $this->arResult["User"] ?? $this->getUserData();
            $userPerms  = $this->arResult["Permissions"] ?? $this->getPermissions();

            $this->form->setUserData($userData);
            $this->form->setUserPermissions($userPerms);
        }
        return $this->form;
    }

    /**
     * @return void
     */
    protected function filterHiddenFields(): void
    {
        if ($this->arResult["Permissions"]['edit'])
        {
            return;
        }

        if (empty($this->arResult['SettingsFieldsView']))
        {
            return;
        }

        if (empty($this->arResult['SettingsFieldsAll']))
        {
            return;
        }

        $filterFields = array_diff(
            array_column($this->arResult['SettingsFieldsAll'], 'VALUE'),
            array_column($this->arResult['SettingsFieldsView'], 'VALUE')
        );
        $user = $this->arResult["User"];
        foreach ($user as $key => $value)
        {
            if (in_array($key, $filterFields))
            {
                if (is_array($value) && !is_array_assoc($value))
                {
                    $value = [];
                }
                else
                {
                    $value = '';
                }

                $user[$key] = $value;
            }
        }
        $this->arResult["User"] = $user;
    }

    /**
     * @return array
     */
    protected function getUrls(): array
    {
        $urls = parent::getUrls();
        $urls['StressLevelHow'] = CComponentEngine::MakePathFromTemplate($this->arParams["PATH_TO_USER_STRESSLEVEL"], [
            'user_id' => $this->arParams['ID']
        ]);
        return $urls;
    }

    /**
     * @param int $userId
     * @return array
     */
    protected function getComponentParams(int $userId): array
    {
        $currentUserId = (int)CurrentUser::get()->getId();
        if (0 >= $userId)
        {
            $userId = $currentUserId;
        }
        $isOwnProfile = ($userId === $currentUserId);

        return [
            "ID" => $userId,
            "PATH_TO_USER" => '/company/personal/user/#user_id#/',
            "PATH_TO_USER_EDIT" => '/company/personal/user/#user_id#/edit/',
            "PATH_TO_USER_FRIENDS" => '/company/personal/user/#user_id#/friends/',
            "PATH_TO_USER_GROUPS" => '/company/personal/user/#user_id#/groups/',
            "PATH_TO_USER_FRIENDS_ADD" => '/company/personal/user/#user_id#/friends/add/',
            "PATH_TO_USER_FRIENDS_DELETE" => '/company/personal/user/#user_id#/friends/delete/',
            "PATH_TO_MESSAGE_FORM" => '/company/personal/messages/form/#user_id#/',
            "PATH_TO_MESSAGES_CHAT" => '/company/personal/messages/chat/#user_id#/',
            "PATH_TO_MESSAGES_USERS_MESSAGES" => '/company/personal/messages/#user_id#/',
            "PATH_TO_USER_SETTINGS_EDIT" => '/company/personal/user/#user_id#/settings/',
            "PATH_TO_GROUP" => '/workgroups/group/#group_id#/',
            "PATH_TO_GROUP_CREATE" => '/company/personal/user/#user_id#/groups/create/',
            "PATH_TO_USER_FEATURES" => '/company/personal/user/#user_id#/features/',
            "PATH_TO_USER_REQUESTS" => '/company/personal/user/#user_id#/requests/',
            "PATH_TO_SEARCH" => '/company/index.php',
            "PATH_TO_SEARCH_INNER" => '/company/structure.php',
            "SET_NAV_CHAIN" => 'Y',
            "SET_TITLE" => 'Y',

            "ALLOWALL_USER_PROFILE_FIELDS" => 'N',

            "USER_FIELDS_MAIN" => Form::getConfigBioFields($isOwnProfile),
            "USER_PROPERTY_MAIN" => array_merge(
                Form::getConfigExecutiveFields(),
                Form::getConfigAssistantFields(),
                Form::getConfigLeftColumnFields($isOwnProfile)
            ),

            "USER_FIELDS_CONTACT" => Form::getConfigContactFields(),
            "USER_PROPERTY_CONTACT" => [],

            "USER_FIELDS_PERSONAL" => [],
            "USER_PROPERTY_PERSONAL" => [],

            "EDITABLE_FIELDS" => Form::getConfigEditableFields($isOwnProfile),

            "DATE_TIME_FORMAT" => 'd.m.Y H:i:s',
            "SHORT_FORM" => 'N',
            "ITEMS_COUNT" => 6,
            "PATH_TO_BLOG" => '/company/personal/user/#user_id#/blog/',
            "PATH_TO_POST" => '/company/personal/user/#user_id#/blog/#post_id#/',
            "PATH_TO_POST_EDIT" => '/company/personal/user/#user_id#/blog/edit/#post_id#/',
            "BLOG_GROUP_ID" => 1,
            "PATH_TO_GROUP_REQUEST_GROUP_SEARCH" => '/company/personal/group/#user_id#/group_search/',
            "PATH_TO_CONPANY_DEPARTMENT" => '/company/structure.php?set_filter_structure=Y&structure_UF_DEPARTMENT=#ID#',

            "PATH_TO_USER_FORUM" => '/company/personal/user/#user_id#/forum/',

            "SHOW_YEAR" => 'M',
            "PATH_TO_USER_SUBSCRIBE" => '/company/personal/user/#user_id#/subscribe/',
            "PATH_TO_LOG" => '/company/personal/log/',
            "PATH_TO_ACTIVITY" => '/company/personal/user/#user_id#/activity/',
            "PATH_TO_SUBSCRIBE" => '/company/personal/subscribe/',
            "PATH_TO_GROUP_SEARCH" => '/workgroups/group/search/',

            "CALENDAR_USER_IBLOCK_ID" => 0,

            "PATH_TO_GROUP_TASKS" => '/workgroups/group/#group_id#/tasks/',
            "PATH_TO_GROUP_TASKS_TASK" => '/workgroups/group/#group_id#/tasks/task/#action#/#task_id#/',
            "PATH_TO_GROUP_TASKS_VIEW" => '/workgroups/group/#group_id#/tasks/view/#action#/#view_id#/',
            "PATH_TO_USER_TASKS" => '/company/personal/user/#user_id#/tasks/',
            "PATH_TO_USER_TASKS_TASK" => '/company/personal/user/#user_id#/tasks/task/#action#/#task_id#/',
            "PATH_TO_USER_TASKS_VIEW" => '/company/personal/user/#user_id#/tasks/view/#action#/#view_id#/',
            "TASK_FORUM_ID" => 8,
            "PATH_TO_VIDEO_CALL" => '/company/personal/video/#user_id#/',
            "PATH_TO_USER_CONTENT_SEARCH" => '/company/personal/user/#user_id#/search/',
            "THUMBNAIL_LIST_SIZE" => 30,
            "NAME_TEMPLATE" => '#NAME# #LAST_NAME#',
            "SHOW_LOGIN" => 'Y',
            "CAN_OWNER_EDIT_DESKTOP" => 'N',
            "CACHE_TYPE" => 'A',
            "CACHE_TIME" => 3600,
            "SHOW_RATING" => 'Y',
            "RATING_ID" => 0,
            "RATING_TYPE" => 'like',
            "BLOG_ALLOW_POST_CODE" => 'Y',
            "PATH_TO_USER_SECURITY" => '/company/personal/user/#user_id#/security/',
            "PATH_TO_USER_COMMON_SECURITY" => '/company/personal/user/#user_id#/common_security/',
            "PATH_TO_USER_PASSWORDS" => '/company/personal/user/#user_id#/passwords/',
            "PATH_TO_USER_SYNCHRONIZE" => '/company/personal/user/#user_id#/synchronize/',
            "PATH_TO_USER_CODES" => '/company/personal/user/#user_id#/codes/',
            "PATH_TO_POST_EDIT_PROFILE" => '/company/personal/user/#user_id#/blog/edit/profile/#post_id#/',
            "PATH_TO_POST_EDIT_GRAT" => '/company/personal/user/#user_id#/blog/edit/grat/#post_id#/',
            "PATH_TO_USER_GRAT" => '/company/personal/user/#user_id#/grat/',
            "PATH_TO_USER_STRESSLEVEL" => '/company/personal/user/#user_id#/stresslevel/',
            "IFRAME" => 0,
            "GRAT_POST_LIST_PAGE_SIZE" => 5,
            "LIST_URL" => '/company/',
        ];
    }

    /**
     * @return string
     * @throws \Exception
     */
    protected function getEnglishName(): string
    {
        $user = UserTable::query()
            ->where('ID', '=', $this->arParams['ID'])
            ->setSelect([Fields::getFioEnUfCode()])
            ->fetch();
        return is_array($user) ? (string)$user[Fields::getFioEnUfCode()] : '';
    }

    /**
     * @param string|null $userZupStatus
     * @return array
     */
    protected function getZupStatus(?string $userZupStatus): array
    {
        $status = [];
        if (!empty($userZupStatus))
        {
            switch (strtoupper(trim($userZupStatus)))
            {
                case Status::ZUP_STATUS_EXTERNAL:
                    $status = [
                        'COLOR'     => 'lightgreen',
                        'NAME'      => Status::ZUP_STATUS_EXTERNAL,
                        'BG_COLOR'  => 'darkmagenta',
                    ];
                    break;
                case Status::ZUP_STATUS_ALUMNI:
                    $status = [
                        'COLOR'     => 'red',
                        'NAME'      => Status::ZUP_STATUS_ALUMNI,
                        'BG_COLOR'  => 'rgba(55, 55, 255, .2)',
                    ];
                    break;
            }

        }
        return $status;
    }

    /**
     * @return array
     * @throws \Exception
     */
    protected function getUserEducationData(): array
    {
        $result = Education::getUserEducationData((int)$this->arParams['ID']);
        if (!$result->isSuccess())
        {
            throw new Exception(implode(';', $result->getErrorMessages()));
        }
        else
        {
            return $result->getData();
        }
    }

    /**
     * @return array
     * @throws \Exception
     */
    protected function getEducationTypes(): array
    {
        $result = Education::getEducationTypes();
        if (!$result->isSuccess())
        {
            throw new Exception(implode(';', $result->getErrorMessages()));
        }
        else
        {
            return $result->getData();
        }
    }

    /**
     * @return array
     * @throws \Exception
     */
    protected function getPermissions(): array
    {
        //$perms = parent::getPermissions();
        $isAdmin = !empty($GLOBALS['USER']) && CurrentUser::get()->isAdmin();

        return [
            'admin'          => $isAdmin,
            'view'           => $isAdmin || Permission::canUserViewProfile((int)$this->arParams['ID']),
            'edit'           => $isAdmin || Permission::canUserEditProfile((int)$this->arParams['ID']),
            'staffing'       => $isAdmin || Permission::isUserInPdStaffingGroup(),
            'ri_manager'     => $isAdmin || Permission::isUserInRiManagersGroup(),
            'ea_leader'      => $isAdmin || Permission::isUserInEaLeadersGroup(),
            'hr_team'        => $isAdmin || Permission::isUserInHrTeamGroup()
        ];
    }

    /**
     * @param array $data
     * @return array|null
     */
    public function saveAction(array $data): ?array
    {
        return parent::saveAction($data);
    }
}