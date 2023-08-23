<?php
/**
 * ==================================================
 * Developer: Alexey Nazarov
 * E-mail: alsnazarov@1cbit.ru
 * Copyright (c) 2019 - 2022
 * ==================================================
 * mc-portal - Configuration.php
 * 25.11.2022 12:00
 * ==================================================
 */
namespace Cbit\Mc\Core\Config;

use Bitrix\Main\Config\Option;
use Bitrix\Main\GroupTable;
use Bitrix\Main\UserTable;
use Bitrix\Main\Web\Json;
use Cbit\Mc\Core\Helper\Iblock\Iblock;
use Cbit\Mc\Core\Internals\Control\ServiceManager;
use CIMSettings;
use CUtil;
use Throwable;

/**
 * Class Configuration
 * @package Cbit\Mc\Core\Config
 */
class Configuration
{
    private static ?Configuration $instance  = null;
    private ?int $activitiesIblockId         = null;
    private ?int $industriesIblockId         = null;
    private ?int $functionsIblockId          = null;
    private ?int $teamCompositionsIblockId   = null;
    private ?int $projectPhasesIblockId      = null;
    private ?int $projectStatesIblockId      = null;
    private ?int $perDiemEditReasonsIblockId = null;

    private array $statusColorsMap = [
        Constants::USER_AVAILABILITY_STATUS_FREE => 'green',
        Constants::USER_AVAILABILITY_STATUS_LEARNING => 'yellow',
        Constants::USER_AVAILABILITY_STATUS_STAFFED => 'orange',
        Constants::USER_AVAILABILITY_STATUS_BEACH => 'lightgreen',
        Constants::USER_AVAILABILITY_STATUS_LOA => 'res',
    ];

    private ?array $groupListToOption = null;

    private function __construct(){}

    public static function getInstance(): Configuration
    {
        if (static::$instance === null)
        {
            static::$instance = new static();
        }
        return static::$instance;
    }

    /**
     * @param string $name
     * @return string
     */
    public function getStatusColorByName(string $name): string
    {
        return (string)$this->statusColorsMap[$name];
    }

    /**
     * @return int
     * @throws \Exception
     */
    public function getIndustriesIblockId(): int
    {
        if (empty($this->industriesIblockId))
        {
            $this->industriesIblockId = Iblock::getIblockIdByCode(Constants::INDUSTRIES_IBLOCK_CODE);
        }
        return (int)$this->industriesIblockId;
    }

    /**
     * @return int
     * @throws \Exception
     */
    public function getActivitiesIBlockId(): int
    {
        if (empty($this->activitiesIblockId))
        {
            $this->activitiesIblockId = Iblock::getIblockIdByCode(Constants::ACTIVITIES_IBLOCK_CODE);
        }
        return (int)$this->activitiesIblockId;
    }

    /**
     * @return int
     * @throws \Exception
     */
    public function getFunctionsIBlockId(): int
    {
        if (empty($this->functionsIblockId))
        {
            $this->functionsIblockId = Iblock::getIblockIdByCode(Constants::FUNCTIONS_IBLOCK_CODE);
        }
        return (int)$this->functionsIblockId;
    }

    /**
     * @return int
     * @throws \Exception
     */
    public function getTeamCompositionsIBlockId(): int
    {
        if (empty($this->teamCompositionsIblockId))
        {
            $this->teamCompositionsIblockId = Iblock::getIblockIdByCode(Constants::TEAM_COMP_IBLOCK_CODE);
        }
        return (int)$this->teamCompositionsIblockId;
    }

    /**
     * @return int
     * @throws \Exception
     */
    public function getProjectPhasesIBlockId(): int
    {
        if (empty($this->projectPhasesIblockId))
        {
            $this->projectPhasesIblockId = Iblock::getIblockIdByCode(Constants::PROJECT_PHASES_IBLOCK_CODE);
        }
        return (int)$this->projectPhasesIblockId;
    }

    /**
     * @return int
     * @throws \Exception
     */
    public function getProjectStatesIBlockId(): int
    {
        if (empty($this->projectStatesIblockId))
        {
            $this->projectStatesIblockId = Iblock::getIblockIdByCode(Constants::PROJECT_STATES_IBLOCK_CODE);
        }
        return (int)$this->projectStatesIblockId;
    }

    /**
     * @return int
     * @throws \Exception
     */
    public function getPerDiemEditReasonsIblockId(): int
    {
        if (empty($this->perDiemEditReasonsIblockId))
        {
            $this->perDiemEditReasonsIblockId = Iblock::getIblockIdByCode(Constants::PER_DIEM_EDIT_REASONS_IBLOCK_CODE);
        }
        return (int)$this->perDiemEditReasonsIblockId;
    }

    /**
     * @return array
     * @throws \Exception
     */
    public function getIndustriesList(): array
    {
        $result = [];
        $iblock = \Bitrix\Iblock\Iblock::wakeUp($this->getIndustriesIblockId());
        $elements = $iblock->getEntityDataClass()::query()
            ->setSelect(['ID', 'NAME', 'INDUSTRY_COLOR'])
            ->setOrder(['ID' => 'ASC'])
            ->fetchCollection();

        foreach ($elements as $element)
        {
            $result[$element->getId()] = [
                'NAME'       => $element->getName(),
                'COLOR'      => $element->getIndustryColor()->getValue(),
            ];
        }
        return $result;
    }

    /**
     * @return array
     * @throws \Exception
     */
    public function getFunctionsList(): array
    {
        $result = [];
        $iblock = \Bitrix\Iblock\Iblock::wakeUp($this->getFunctionsIBlockId());
        $elements = $iblock->getEntityDataClass()::query()
            ->setSelect(['ID', 'NAME', 'FUNCTION_COLOR'])
            ->setOrder(['ID' => 'ASC'])
            ->fetchCollection();

        foreach ($elements as $element)
        {
            $result[$element->getId()] = [
                'NAME'       => $element->getName(),
                'COLOR'      => $element->getFunctionColor()->getValue(),
            ];
        }
        return $result;
    }

    /**
     * @return int[]
     * @throws \Bitrix\Main\ArgumentException
     */
    public function getHrTeamGroupIds(): array
    {
        $val = Json::decode(Option::get(ServiceManager::getModuleId(), Constants::OPTION_KEY_HR_TEAM_ROLE, '[1]'));
        return (is_array($val) && !empty($val)) ? $val : [1];
    }

    /**
     * @return array
     * @throws \Bitrix\Main\ArgumentException
     */
    public function getPdStaffingGroupIds(): array
    {
        $val = Json::decode(Option::get(ServiceManager::getModuleId(), Constants::OPTION_KEY_PD_STAFFING_ROLE, '[1]'));
        return (is_array($val) && !empty($val)) ? $val : [1];
    }

    /**
     * @return array
     * @throws \Bitrix\Main\ArgumentException
     */
    public function getEaLeadersGroupIds(): array
    {
        $val = Json::decode(Option::get(ServiceManager::getModuleId(), Constants::OPTION_KEY_EA_LEADERS_ROLE, '[1]'));
        return (is_array($val) && !empty($val)) ? $val : [1];
    }

    /**
     * @return array
     * @throws \Bitrix\Main\ArgumentException
     */
    public function getVgLeadersGroupIds(): array
    {
        $val = Json::decode(Option::get(ServiceManager::getModuleId(), Constants::OPTION_KEY_VG_LEADERS_ROLE, '[1]'));
        return (is_array($val) && !empty($val)) ? $val : [1];
    }

    /**
     * @return array
     * @throws \Bitrix\Main\ArgumentException
     */
    public function getRiAnalystsGroupIds(): array
    {
        $val = Json::decode(Option::get(ServiceManager::getModuleId(), Constants::OPTION_KEY_RI_ANALYSTS_ROLE, '[1]'));
        return (is_array($val) && !empty($val)) ? $val : [1];
    }

    /**
     * @return array
     * @throws \Bitrix\Main\ArgumentException
     */
    public function getRiManagersGroupIds(): array
    {
        $val = Json::decode(Option::get(ServiceManager::getModuleId(), Constants::OPTION_KEY_RI_MANAGERS_ROLE, '[1]'));
        return (is_array($val) && !empty($val)) ? $val : [1];
    }

    /**
     * @return array
     * @throws \Bitrix\Main\ArgumentException
     */
    public function getExpensesITGroupIds(): array
    {
        $val = Json::decode(Option::get(ServiceManager::getModuleId(), Constants::OPTION_KEY_EXPENSES_IT_ROLE, '[1]'));
        return (is_array($val) && !empty($val)) ? $val : [1];
    }

    /**
     * @return array
     * @throws \Bitrix\Main\ArgumentException
     */
    public function getExpensesTravelGroupIds(): array
    {
        $val = Json::decode(Option::get(ServiceManager::getModuleId(), Constants::OPTION_KEY_EXPENSES_TRAVEL_ROLE, '[1]'));
        return (is_array($val) && !empty($val)) ? $val : [1];
    }

    /**
     * @return array
     * @throws \Bitrix\Main\ArgumentException
     */
    public function getExpensesFinanceGroupIds(): array
    {
        $val = Json::decode(Option::get(ServiceManager::getModuleId(), Constants::OPTION_KEY_EXPENSES_FINANCE_ROLE, '[1]'));
        return (is_array($val) && !empty($val)) ? $val : [1];
    }

    /**
     * @return array
     * @throws \Bitrix\Main\ArgumentException
     */
    public function getExpensesPayrollGroupIds(): array
    {
        $val = Json::decode(Option::get(ServiceManager::getModuleId(), Constants::OPTION_KEY_EXPENSES_PAYROLL_ROLE, '[1]'));
        return (is_array($val) && !empty($val)) ? $val : [1];
    }

    /**
     * @return array
     * @throws \Exception
     */
    public function getUserGroupsToOption(): array
    {
        if ($this->groupListToOption === null)
        {
            $excludeGroupCodes = [
                'PORTAL_ADMINISTRATION',
                'ADMIN_SECTION',
                'EXTRANET_CREATE_WG',
                'CREATE_GROUPS',
                'CRM_SHOP_ADMIN',
                'CRM_SHOP_BUYER',
                'RATING_VOTE',
                'MAIL_INVITED',
                'EXTRANET',
                'SUPPORT',
                'EXTRANET_ADMIN',
                'CRM_SHOP_MANAGER',
                'RATING_VOTE_AUTHORITY',
            ];
            $excludeGroupIds = [
                1,//admins
                2,//all users
            ];

            $this->groupListToOption = [];

            $groups = GroupTable::query()
                ->setSelect(['ID', 'NAME'])
                ->setFilter([
                    '!=ID'        => $excludeGroupIds,
                    '=ACTIVE'     => true,
                    '!%STRING_ID' => $excludeGroupCodes
                ])
                ->fetchAll();

            foreach ($groups as $group)
            {
                $this->groupListToOption[$group['ID']] = $group['NAME'];
            }
        }

        return $this->groupListToOption;
    }

    /**
     * @return array
     */
    public function getUserPositionsEnAvailableInStaffing(): array
    {
        return [
            Constants::USER_POSITION_EM,
            Constants::USER_POSITION_JEM,
            Constants::USER_POSITION_ASC,
            Constants::USER_POSITION_FASC,
            Constants::USER_POSITION_SBA,
            Constants::USER_POSITION_BA,
            Constants::USER_POSITION_BAI,
            Constants::USER_POSITION_PTI_2,
            Constants::USER_POSITION_PTI_1,
            Constants::USER_POSITION_SENIOR,
            Constants::USER_POSITION_EXPERT,
            Constants::USER_POSITION_JEX,
        ];
    }

    /**
     * Вызывается из php-консоли сайта один раз для применения одинаковых настроек по уведомлениям ко всем пользователям
     * @return void
     */
    public function saveCommonImSettingForAllUsers(): void
    {
        try
        {
            $users = UserTable::query()->setSelect(['ID'])->fetchAll();

            foreach ($users as $user)
            {
                $userId = $user['ID'];
                $jsonSettings = '{"notify":{"site|forum|comment":true,"email|forum|comment":false,"xmpp|forum|comment":true,"site|vote|voting":true,"email|vote|voting":false,"xmpp|vote|voting":true,"site|wiki|comment":true,"email|wiki|comment":false,"xmpp|wiki|comment":true,"site|crm|incoming_email":false,"email|crm|incoming_email":false,"xmpp|crm|incoming_email":true,"site|crm|post":true,"email|crm|post":false,"xmpp|crm|post":true,"site|crm|mention":true,"email|crm|mention":false,"xmpp|crm|mention":true,"site|crm|webform":false,"email|crm|webform":false,"xmpp|crm|webform":true,"site|crm|callback":false,"email|crm|callback":false,"xmpp|crm|callback":true,"site|crm|changeAssignedBy":false,"email|crm|changeAssignedBy":false,"xmpp|crm|changeAssignedBy":true,"site|crm|changeStage":false,"email|crm|changeStage":false,"xmpp|crm|changeStage":true,"site|crm|merge":false,"email|crm|merge":false,"xmpp|crm|merge":true,"site|crm|other":false,"email|crm|other":false,"xmpp|crm|other":true,"email|im|message":false,"push|im|message":false,"push|im|chat":false,"push|im|openChat":false,"site|im|like":true,"email|im|like":false,"xmpp|im|like":true,"site|im|mention":true,"email|im|mention":false,"xmpp|im|mention":true,"push|im|mention":false,"site|im|default":true,"email|im|default":false,"xmpp|im|default":true,"push|im|default":false,"site|main|rating_vote":false,"email|main|rating_vote":false,"xmpp|main|rating_vote":true,"site|main|rating_vote_mentioned":false,"email|main|rating_vote_mentioned":false,"xmpp|main|rating_vote_mentioned":true,"site|sender|group_prepared":false,"site|mail|new_message":false,"xmpp|mail|new_message":true,"site|photogallery|comment":false,"email|photogallery|comment":false,"xmpp|photogallery|comment":true,"site|imopenlines|rating_client":false,"email|imopenlines|rating_client":false,"xmpp|imopenlines|rating_client":true,"site|imopenlines|rating_head":false,"email|imopenlines|rating_head":false,"xmpp|imopenlines|rating_head":true,"site|bizproc|activity":true,"email|bizproc|activity":false,"xmpp|bizproc|activity":false,"push|bizproc|activity":false,"site|blog|post":false,"email|blog|post":false,"xmpp|blog|post":true,"push|blog|post":false,"site|blog|post_mail":false,"email|blog|post_mail":false,"xmpp|blog|post_mail":true,"push|blog|post_mail":false,"site|blog|comment":false,"email|blog|comment":false,"xmpp|blog|comment":true,"push|blog|comment":false,"site|blog|mention":false,"email|blog|mention":false,"xmpp|blog|mention":true,"push|blog|mention":false,"site|blog|mention_comment":false,"email|blog|mention_comment":false,"xmpp|blog|mention_comment":true,"push|blog|mention_comment":false,"site|blog|share":false,"email|blog|share":false,"xmpp|blog|share":true,"push|blog|share":false,"site|blog|share2users":false,"email|blog|share2users":false,"xmpp|blog|share2users":true,"push|blog|share2users":false,"email|blog|broadcast_post":false,"push|blog|broadcast_post":false,"site|blog|grat":false,"email|blog|grat":false,"xmpp|blog|grat":true,"push|blog|grat":false,"site|blog|moderate_post":false,"email|blog|moderate_post":false,"site|blog|moderate_comment":false,"email|blog|moderate_comment":false,"site|blog|published_post":false,"email|blog|published_post":false,"site|blog|published_comment":false,"email|blog|published_comment":false,"email|socialnetwork|invite_group":false,"xmpp|socialnetwork|invite_group":true,"site|socialnetwork|inout_group":false,"email|socialnetwork|inout_group":false,"xmpp|socialnetwork|inout_group":true,"site|socialnetwork|moderators_group":false,"email|socialnetwork|moderators_group":false,"xmpp|socialnetwork|moderators_group":true,"site|socialnetwork|owner_group":false,"email|socialnetwork|owner_group":false,"xmpp|socialnetwork|owner_group":true,"site|socialnetwork|sonet_group_event":false,"email|socialnetwork|sonet_group_event":false,"xmpp|socialnetwork|sonet_group_event":true,"push|socialnetwork|sonet_group_event":false,"email|calendar|invite":false,"xmpp|calendar|invite":true,"push|calendar|invite":false,"site|calendar|reminder":true,"email|calendar|reminder":false,"xmpp|calendar|reminder":true,"push|calendar|reminder":false,"site|calendar|change":true,"email|calendar|change":false,"xmpp|calendar|change":true,"site|calendar|info":true,"email|calendar|info":false,"xmpp|calendar|info":true,"site|calendar|event_comment":true,"email|calendar|event_comment":false,"xmpp|calendar|event_comment":true,"site|calendar|delete_location":true,"email|calendar|delete_location":false,"xmpp|calendar|delete_location":true,"site|tasks|comment":true,"email|tasks|comment":false,"push|tasks|comment":false,"site|tasks|reminder":true,"email|tasks|reminder":false,"xmpp|tasks|reminder":true,"push|tasks|reminder":false,"site|tasks|manage":true,"email|tasks|manage":false,"xmpp|tasks|manage":true,"push|tasks|manage":false,"site|tasks|task_assigned":true,"email|tasks|task_assigned":false,"xmpp|tasks|task_assigned":true,"push|tasks|task_assigned":false,"site|tasks|task_expired_soon":true,"push|tasks|task_expired_soon":false,"site|disk|files":true,"site|disk|deletion":true,"site|intranet|security_otp":false,"push|intranet|security_otp":false,"site|timeman|entry":false,"email|timeman|entry":false,"xmpp|timeman|entry":true,"site|timeman|entry_comment":false,"email|timeman|entry_comment":false,"xmpp|timeman|entry_comment":true,"site|timeman|entry_approve":false,"email|timeman|entry_approve":false,"xmpp|timeman|entry_approve":true,"site|timeman|report":false,"email|timeman|report":false,"xmpp|timeman|report":true,"site|timeman|report_comment":false,"email|timeman|report_comment":false,"xmpp|timeman|report_comment":true,"site|timeman|report_approve":false,"email|timeman|report_approve":false,"xmpp|timeman|report_approve":true},"status":"online","backgroundImage":"","bxdNotify":true,"sshNotify":true,"generalNotify":true,"trackStatus":"","nativeNotify":true,"openDesktopFromPanel":true,"viewOffline":true,"viewGroup":true,"viewLastMessage":true,"viewBirthday":true,"viewCommonUsers":true,"enableSound":true,"enableBigSmile":true,"enableDarkTheme":"auto","isCurrentThemeDark":false,"enableRichLink":true,"linesTabEnable":true,"linesNewGroupEnable":false,"sendByEnter":true,"correctText":false,"panelPositionHorizontal":"right","panelPositionVertical":"bottom","loadLastMessage":true,"loadLastNotify":true,"notifyAutoRead":true,"notifyScheme":"expert","notifySchemeLevel":"important","notifySchemeSendSite":true,"notifySchemeSendEmail":false,"notifySchemeSendXmpp":true,"notifySchemeSendPush":false,"privacyMessage":"all","privacyChat":"all","privacyCall":"all","privacySearch":"all","privacyProfile":"all","callAcceptIncomingVideo":"AllowAll","next":false}';

                $arSettings = CUtil::JsObjectToPhp($jsonSettings);

                $oldSettings = CIMSettings::Get($userId)[CIMSettings::SETTINGS];
                if ($oldSettings['notifyScheme'] == 'expert' && $arSettings['notifyScheme'] == 'simple')
                {
                    $arNotifyValues = CIMSettings::GetSimpleNotifyBlocked();
                    $arSettings['notify'] = Array();
                    foreach ($arNotifyValues as $settingName => $value)
                    {
                        $arSettings['notify'][CIMSettings::CLIENT_SITE.'|'.$settingName] = false;
                        $arSettings['notify'][CIMSettings::CLIENT_XMPP.'|'.$settingName] = false;
                        $arSettings['notify'][CIMSettings::CLIENT_MAIL.'|'.$settingName] = false;
                    }
                }

                if (array_key_exists('notify', $arSettings))
                {
                    CIMSettings::Set(CIMSettings::NOTIFY, $arSettings['notify'], $userId);
                    unset($arSettings['notify']);
                }
                CIMSettings::Set(CIMSettings::SETTINGS, $arSettings, $userId);
            }
        }
        catch(Throwable $e)
        {
            echo $e->getMessage();
        }
    }

    private function __clone(){}
    public function __wakeup(){}
}