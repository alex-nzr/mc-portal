<?php
/**
 * ==================================================
 * Developer: Alexey Nazarov
 * E-mail: alsnazarov@1cbit.ru
 * Copyright (c) 2019 - 2022
 * ==================================================
 * mc-portal - Form.php
 * 09.11.2022 17:56
 * ==================================================
 */


namespace Cbit\Mc\Profile\Service\Integration\Intranet\Component\UserProfile;


use Bitrix\Main\Engine\CurrentUser;
use Bitrix\Main\Localization\Loc;
use Bitrix\UI\Form\EntityEditorConfigScope;
use Bitrix\UI\Form\EntityEditorConfiguration;
use Cbit\Mc\Profile\Internals\Control\ServiceManager;
use Cbit\Mc\Core\Service\Integration\Zup\Fields;
use Cbit\Mc\Profile\Service\Access\Permission;

/**
 * Class Form
 * @package Cbit\Mc\Profile\Service\Integration\Intranet\Component\UserProfile
 */
class Form extends \Bitrix\Intranet\Component\UserProfile\Form
{
    private string  $moduleId;
    private ?string $bioTitle;
    private bool    $isOwnProfile;
    private bool    $isAdmin;
    private ?array  $userData = null;
    private array $userPerms = [];

    public function __construct($userId = 0)
    {
        $this->moduleId = ServiceManager::getModuleId();
        $this->bioTitle = Loc::getMessage($this->moduleId.'INTRANET_USER_PROFILE_SECTION_BIO_TITLE');
        parent::__construct($userId);
        $this->isOwnProfile = !empty($GLOBALS['USER']) && ((int)CurrentUser::get()->getId() === $userId);
        $this->isAdmin      = !empty($GLOBALS['USER']) && CurrentUser::get()->isAdmin();
    }

    /**
     * @param array $userData
     * @return void
     */
    public function setUserData(array $userData): void
    {
        $this->userData = $userData;
    }

    /**
     * @param array $perms
     * @return void
     */
    public function setUserPermissions(array $perms): void
    {
        $this->userPerms = $perms;
    }

    /**
     * @param array $editableFields
     * @return array[]
     */
    public function getConfig($editableFields = []): array
    {
        $config = $this->getBaseFormConfig();

        foreach ($editableFields as $field)
        {
            if (in_array($field, static::getConfigContactFields()))
            {
                $config['CONTACT']['elements'][] = ['name' => $field];
            }
            elseif (in_array($field, static::getConfigBioFields($this->isOwnProfile)))
            {
                $config['BIO']['elements'][] = ['name' => $field];
            }
            elseif (in_array($field, static::getConfigAssistantFields()))
            {
                $config['ASSISTANT']['elements'][] = ['name' => $field];
            }
            elseif (in_array($field, static::getConfigExecutiveFields()))
            {
                $config['EXECUTIVE']['elements'][] = ['name' => $field];
            }
            elseif (in_array($field, static::getConfigLeftColumnFields($this->isOwnProfile)))
            {
                $config['LEFT_COLUMN']['elements'][] = ['name' => $field];
            }
        }

        if ($this->userData !== null)
        {
            if (empty($this->userData["UF_ASSISTANT"]) && !$this->userPerms['ea_leader'])
            {
                unset($config['ASSISTANT']);
            }

            if (empty($this->userData["UF_EXECUTIVE"]) || (!$this->isOwnProfile && !$this->isAdmin))
            {
                unset($config['EXECUTIVE']);
            }

            if (!$this->userPerms['staffing']
                && empty($this->userData["UF_STAFFING_MANAGER"])
                && empty($this->userData["UF_DGL"])
                && empty($this->userData["UF_USER_AVAILABLE"])
            ){
                unset($config['LEFT_COLUMN']);
            }
        }

        return $config;
    }

    /**
     * @return string[]
     */
    public static function getConfigExecutiveFields(): array
    {
        return [
            'UF_EXECUTIVE',
        ];
    }

    /**
     * @return string[]
     */
    public static function getConfigAssistantFields(): array
    {
        return [
            'UF_ASSISTANT',
        ];
    }

    /**
     * @return string[]
     */
    public static function getConfigContactFields(): array
    {
        return [
            'EMAIL',
            "PERSONAL_PHONE",

            'PERSONAL_MOBILE',
            'UF_SKYPE',
            'UF_TELEGRAM',
            'UF_EMAIL',
        ];
    }

    /**
     * @param bool $isOwnProfile
     * @return array
     */
    public static function getConfigLeftColumnFields(bool $isOwnProfile = false): array
    {
        $isAdmin = !empty($GLOBALS['USER']) && CurrentUser::get()->isAdmin();

        $fields = [
            'UF_DGL'
        ];

        if ($isOwnProfile || Permission::isUserInPdStaffingGroup() || $isAdmin)
        {
            $fields[] = 'UF_USER_AVAILABLE';
            $fields[] = 'UF_STAFFING_MANAGER';
        }

        /*if (Permission::isUserInPdStaffingGroup() || $isAdmin)
        {

        }*/
        return $fields;
    }

    /**
     * @param bool $isOwnProfile
     * @return string[]
     */
    public static function getConfigBioFields(bool $isOwnProfile = false): array
    {
        $isAdmin = !empty($GLOBALS['USER']) && CurrentUser::get()->isAdmin();

        $fields = [
            10 => 'WORK_POSITION',
            30 => 'UF_DEPARTMENT',
            50 => 'UF_WORK_FORMAT',
            70 => 'UF_UPLOADED_DOCS',
            80 => 'UF_COVERED_INDUSTRIES',
            90 => "TIME_ZONE",
            100 => 'LAST_LOGIN',
        ];

        if ($isOwnProfile || $isAdmin)
        {
            $fields[20] = Fields::getFmnoUfCode();
        }

        if ($isOwnProfile || $isAdmin || Permission::isUserInPdStaffingGroup())
        {
            $fields[40] = Fields::getTenureCompanyUfCode();
            $fields[60] = Fields::getTenurePositionUfCode();
        }

        ksort($fields, SORT_NUMERIC);

        return $fields;
    }

    /**
     * @return string[]
     */
    public static function getConfigEditableFields(bool $isOwnProfile = false): array
    {
        $isAdmin = !empty($GLOBALS['USER']) && CurrentUser::get()->isAdmin();

        $fields = [];

        if ($isOwnProfile || $isAdmin)
        {
            $fields[] = 'UF_WORK_FORMAT';
            $fields[] = 'TIME_ZONE';
        }

        if ($isOwnProfile || Permission::isUserInHrTeamGroup() || $isAdmin)
        {
            $fields[] = 'PERSONAL_PHONE';
            $fields[] = 'UF_SKYPE';
            $fields[] = 'UF_TELEGRAM';
            $fields[] = 'UF_EMAIL';
        }

        if (Permission::isUserInEaLeadersGroup() || $isAdmin)
        {
            $fields[] = 'UF_ASSISTANT';
        }

        if (Permission::isUserInPdStaffingGroup() || $isAdmin)
        {
            $fields[] = 'UF_DGL';
            $fields[] = 'UF_STAFFING_MANAGER';
        }

        if (!$isOwnProfile && (Permission::isUserInRiManagersGroup() || $isAdmin))
        {
            $fields[] = 'UF_COVERED_INDUSTRIES';
        }

        return $fields;
    }

    /**
     * @return array[]
     */
    private function getBaseFormConfig(): array
    {
        return [
            'BIO' => [
                'name' => 'bio',
                'title' => $this->getBioTitle(),
                'type' => 'section',
                'elements' => [],
                'data' => ['isChangeable' => true, 'isRemovable' => false]
            ],
            'ASSISTANT' => [
                'name' => 'assistant',
                'title' => Loc::getMessage($this->moduleId.'INTRANET_USER_PROFILE_SECTION_ASSISTANT_TITLE'),
                'type' => 'section',
                'elements' => [],
                'data' => ['isChangeable' => true, 'isRemovable' => false]
            ],
            'EXECUTIVE' => [
                'name' => 'executive',
                'title' => Loc::getMessage($this->moduleId.'INTRANET_USER_PROFILE_SECTION_EXECUTIVE_TITLE'),
                'type' => 'section',
                'elements' => [],
                'data' => ['isChangeable' => false, 'isRemovable' => false]
            ],
            'LEFT_COLUMN' => [
                'name' => 'left_column',
                'title' => '',
                'type' => 'section',
                'elements' => [],
                'data' => ['isChangeable' => false, 'isRemovable' => false]
            ],
            'CONTACT' => [
                'name' => 'contact',
                'title' => Loc::getMessage($this->moduleId.'INTRANET_USER_PROFILE_SECTION_CONTACT_TITLE'),
                'type' => 'section',
                'elements' => [],
                'data' => ['isChangeable' => true, 'isRemovable' => false]
            ]
        ];
    }

    /**
     * @return string
     */
    public function getFormId(): string
    {
        return "intranet-user-profile";
    }

    /**
     * @return string
     */
    public function getConfigurationCategory(): string
    {
        return "ui.form.editor";
    }

    /**
     * @return void
     */
    public function resetEditorConfiguration(): void
    {
        (new EntityEditorConfiguration($this->getConfigurationCategory()))->reset($this->getFormId(), [
            'scope' => EntityEditorConfigScope::COMMON
        ]);
    }

    public function setMainBlockTitle(string $title)
    {
        $this->bioTitle = $title;
    }

    /**
     * @return string
     */
    public function getBioTitle(): string
    {
        return $this->bioTitle;
    }

    /**
     * @return array
     */
    public function getUserFieldInfos(): array
    {
        $fields = parent::getUserFieldInfos();
        foreach ($fields as $code => $field)
        {
            $fields[$code]['editable'] = in_array($code, static::getConfigEditableFields($this->isOwnProfile));
        }

        return $fields;
    }
}