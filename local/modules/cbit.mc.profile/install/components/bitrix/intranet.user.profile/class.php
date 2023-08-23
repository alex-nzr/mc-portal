<?php
namespace Cbit\Mc\Profile\Component;

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED!==true)die();

use Bitrix\Main\Config\Option;
use Bitrix\Main\Engine\CurrentUser;
use Bitrix\Main\Localization\Loc;
use Bitrix\Main\Loader;
use Bitrix\Main\UserField\Dispatcher;
use Bitrix\Security\Mfa\Otp;
use Cbit\Mc\Profile\Service\Integration\Intranet\Component\UserProfile;
use CExtranet;
use CSite;
use CUser;
use Exception;
use function ShowError;

Loc::loadMessages(__FILE__);

/**
 * @class CbitUserProfileComponent
 * @package Cbit\Mc\Profile\Component
 */
class CbitUserProfileComponent extends UserProfile
{
    /**
     * @param $params
     * @return array
     * @throws \Exception
     */
	public function onPrepareComponentParams($params): array
    {
        if (!is_array($params))
        {
            $params = [];
        }

        if (empty($params['LIST_URL']))
        {
            $params['LIST_URL'] = Option::get('intranet', 'list_user_url', (Loader::includeModule('extranet') && CExtranet::isExtranetSite() ? SITE_DIR.'contacts/' : SITE_DIR.'company/'), SITE_ID);
        }

        $parentParams = parent::onPrepareComponentParams($params);
        unset(
            $parentParams['ALLOWALL_USER_PROFILE_FIELDS'],
            $parentParams['USER_FIELDS_MAIN'],
            $parentParams['USER_PROPERTY_MAIN'],
            $parentParams['USER_FIELDS_CONTACT'],
            $parentParams['USER_PROPERTY_CONTACT'],
            $parentParams['USER_FIELDS_PERSONAL'],
            $parentParams['USER_PROPERTY_PERSONAL'],
            $parentParams['EDITABLE_FIELDS']
        );

        $this->arParams = array_merge($params, $this->getComponentParams((int)$parentParams['ID']), $parentParams);

        $this->initCustomSignedParameters();

        return $this->arParams;
    }

    public function executeComponent()
	{
	    try
        {
            global $APPLICATION, $USER;

            if (!$this->checkRequiredParams())
            {
                $this->printErrors();
                return;
            }

            $this->init();

            $this->arResult["Urls"] = $this->getUrls();
            $this->arResult["EducationTypes"] = $this->getEducationTypes();
            $this->arResult["User"] = $this->getUserData();
            $this->arResult["CurrentUser"] = [
                'STATUS' => $this->getCurrentUserStatus()
            ];

            $this->arResult["Permissions"] = $this->getPermissions();

            $this->arResult["UserFieldEntityId"] = "USER";
            $this->arResult["UserFieldPrefix"]   = "USR";

            $formattedName = CUser::FormatName(CSite::GetNameFormat(), $this->arResult["User"], true);
            $title = !empty($formattedName) ? $formattedName : $this->getEnglishName();
            $this->getFormInstance()->setMainBlockTitle($title);

            $this->arResult["EnablePersonalConfigurationUpdate"] = false;
            $this->arResult["EnableCommonConfigurationUpdate"] = false;
            $this->arResult["EnableUserFieldCreation"] = $this->arResult["EnableCommonConfigurationUpdate"];

            $userFieldDispatcher = Dispatcher::instance();
            $this->arResult["UserFieldCreateSignature"] = $this->arResult["EnableCommonConfigurationUpdate"]
                ? $userFieldDispatcher->getCreateSignature(array("ENTITY_ID" => $this->arResult["UserFieldEntityId"]))
                : '';

            $this->arResult["EnableSettingsForAll"] = CurrentUser::get()->canDoOperation('edit_other_settings');
            $this->arResult["AllowAllUserProfileFields"] = ($this->arParams["ALLOWALL_USER_PROFILE_FIELDS"] === 'Y');
            $this->arResult["UserFieldsAvailable"] = $this->getAvailableFields();
            $this->arResult["EnableUserFieldMandatoryControl"] = false;

            if ($this->arResult["User"]["STATUS"] === "email")
            {
                $this->arResult["FormFields"] = $this->getFormInstance()->getFieldInfoForEmailUser();
            }
            else
            {
                $this->arResult["FormFields"] = $this->getFormInstance()->getFieldInfo($this->arResult["User"], [], $this->arParams);
                $this->getFormInstance()->prepareSettingsFields($this->arResult, $this->arParams);
            }

            $this->arResult["FormConfig"] = $this->getFormInstance()->getConfig($this->arResult["SettingsFieldsForConfig"]);
            $this->arResult["FormData"] = $this->getFormInstance()->getData($this->arResult);

            $this->arResult["Gratitudes"] = $this->getGratsInstance()->getStub();
            $this->arResult["ProfileBlogPost"] = $this->getProfilePostInstance()->getStub();
            $this->arResult["Tags"] = $this->getTagsInstance()->getStub();
            $this->arResult["FormId"] = $this->getFormInstance()->getFormId();
            $this->arResult["IsOwnProfile"] = (int)$USER->GetID() === (int)$this->arParams["ID"];
            $this->arResult["StressLevel"] = $this->getStressLevelInstance()->getStub();

            $this->filterHiddenFields();
            $this->checkNumAdminRestrictions();

            if (Loader::includeModule("security") && Otp::isOtpEnabled())
            {
                $this->arResult["OTP_IS_ENABLED"] = "Y";
            }
            else
            {
                $this->arResult["OTP_IS_ENABLED"] = "N";
            }

            $this->arResult["isExtranetSite"] = (Loader::includeModule("extranet") && CExtranet::isExtranetSite());

            $this->arResult["IS_CURRENT_USER_INTEGRATOR"] = false;
            $this->arResult["isFireUserEnabled"] = true;

            $this->processShowYear();

            $this->arResult["DISK_INFO"] = $this->getDiskInfo();

            $this->arResult["selectorsToDelete"] = $this->getSelectorsToDelete();

            $APPLICATION->SetTitle($title);

            $this->includeComponentTemplate();
        }
        catch(Exception $e)
        {
            ShowError($e->getMessage());
        }
	}

    /**
     * @return array
     */
    protected function getSelectorsToDelete(): array
    {
        $selectorsToDelete = [
            '[data-cid="executive"] .ui-entity-editor-header-actions',
        ];

        if (!($this->arResult['Permissions']['ri_manager'] || $this->arResult['IsOwnProfile']))
        {
            $selectorsToDelete[] = '[data-cid="bio"] .ui-entity-editor-header-actions';
        }

        if (!($this->arResult['IsOwnProfile'] || $this->arResult['Permissions']['hr_team']))
        {
            $selectorsToDelete[] = '[data-cid="contact"] .ui-entity-editor-header-actions';
        }

        if (!$this->arResult['Permissions']['ea_leader'])
        {
            $selectorsToDelete[] = '[data-cid="assistant"] .ui-entity-editor-header-actions';
            $selectorsToDelete[] = '[data-cid="assistant"] .crm-widget-employee-change';
        }

        if (!$this->arResult['Permissions']['staffing'])
        {
            $selectorsToDelete[] = '[data-cid="left_column"] .ui-entity-editor-section-header';
            $selectorsToDelete[] = '[data-cid="UF_STAFFING_MANAGER"] .crm-widget-employee-change';
            $selectorsToDelete[] = '[data-cid="UF_DGL"] .crm-widget-employee-change';
        }

        return $selectorsToDelete;
    }
}
