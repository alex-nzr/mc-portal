<?php

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
    die();
}

/** @var CBitrixComponentTemplate $this */
/** @var array $arParams */
/** @var array $arResult */
/** @global CDatabase $DB */
/** @global CUser $USER */
/** @global CMain $APPLICATION */
/** @var CBitrixComponent $component */
/** @var string $templateFolder */

use Bitrix\Main\Localization\Loc;
use Bitrix\Main\Page;
use Bitrix\Main\UI\Extension;
use Cbit\Mc\Core\Service\Integration\Zup\Fields;

$bodyClass = $APPLICATION->GetPageProperty('BodyClass');
$APPLICATION->SetPageProperty('BodyClass', ($bodyClass ? $bodyClass.' ' : '') . 'no-all-paddings no-background');

try {
    Extension::load([
        'ui.buttons',
        'ui.alerts',
        'ui.tooltip',
        'ui.hint',
        'ui.icons.b24',
        'ui.design-tokens',
        'ui.fonts.opensans',
    ]);
}catch(Exception $e){}

CJSCore::Init("loader");

if (!$arResult['Permissions']['view'])
{
    $APPLICATION->IncludeComponent(
        'bitrix:ui.sidepanel.wrapper',
        '',
        [
            'POPUP_COMPONENT_NAME' => 'bitrix:socialnetwork.entity.error',
            'POPUP_COMPONENT_TEMPLATE_NAME' => '',
            'POPUP_COMPONENT_PARAMS' => [
                'ENTITY' => 'USER',
            ],
        ]
    );

    ?>
    <script>
        const sectionMenu = document.querySelector('.main-buttons-inner-container');
        sectionMenu && sectionMenu.remove();
    </script>
    <?php
    return;
}

Page\Asset::getInstance()->addJs($templateFolder.'/js/utils.js');
Page\Asset::getInstance()->addJs($templateFolder.'/js/stresslevel.js');
Page\Asset::getInstance()->addJs($templateFolder.'/js/grats.js');
Page\Asset::getInstance()->addJs($templateFolder.'/js/profilepost.js');
Page\Asset::getInstance()->addJs($templateFolder.'/js/tags.js');
Page\Asset::getInstance()->addJs($templateFolder.'/js/tags-users-popup.js');
Page\Asset::getInstance()->addJs($templateFolder.'/js/form-entity.js');
Page\Asset::getInstance()->addJs($templateFolder.'/js/slider/simple-adaptive-slider.js');
Page\Asset::getInstance()->addCss('/bitrix/components/bitrix/socialnetwork.blog.blog/templates/.default/style.css');
Page\Asset::getInstance()->addCss('/bitrix/js/crm/entity-editor/css/style.min.css');
Page\Asset::getInstance()->addCss($templateFolder.'/js/slider/simple-adaptive-slider.css');

if (
    $arResult["IsOwnProfile"]
    && ($arResult["User"]["SHOW_SONET_ADMIN"])
    && file_exists($_SERVER["DOCUMENT_ROOT"]."/bitrix/components/bitrix/socialnetwork.admin.set")
)
{
    $APPLICATION->IncludeComponent(
        "bitrix:socialnetwork.admin.set",
        "",
        Array(
            "PROCESS_ONLY" => "Y"
        ),
        $component,
        array("HIDE_ICONS" => "Y")
    );
}
?>

<div id="intranet-user-profile-tab-control">
    <button class="intranet-user-profile-tab-btn ui-btn ui-btn-primary active" data-target="general">
        <?=Loc::getMessage('INTRANET_USER_PROFILE_TAB_GENERAL')?>
    </button>
    <?php if ($arResult['Permissions']['staffing'] || $arResult["IsOwnProfile"]):?>
        <button class="intranet-user-profile-tab-btn ui-btn ui-btn-primary-dark" data-target="staffing">
            <?=Loc::getMessage('INTRANET_USER_PROFILE_TAB_STAFFING')?>
        </button>
    <?php endif;?>
    <button class="intranet-user-profile-tab-btn ui-btn ui-btn-success-dark" data-target="education">
        <?=Loc::getMessage('INTRANET_USER_PROFILE_TAB_EDUCATION')?>
    </button>
</div>

<div class="intranet-user-profile" id="intranet-user-profile-wrap">
    <div class="intranet-user-profile-column-left">
        <div class="intranet-user-profile-column-block" id="intranet-user-profile-main-block">
            <div class="intranet-user-profile-rank">
                <?php if(!empty($arResult["User"]["ZUP_STATUS"])):?>
                    <div class="intranet-user-profile-rank-item"
                         style="background-color:<?=$arResult["User"]["ZUP_STATUS"]['BG_COLOR']?>;">
                        <span class="intranet-user-profile-rank-item-zup-status"
                              style="color:<?=$arResult["User"]["ZUP_STATUS"]['COLOR']?>;">
                            <?=$arResult["User"]["ZUP_STATUS"]['NAME']?>
                        </span>
                    </div>
                <?php endif;?>
            </div>

            <div class="intranet-user-profile-status-info">
                <div class="intranet-user-profile-status intranet-user-profile-status-<?= $arResult["User"]["ONLINE_STATUS"]["STATUS"] ?>">
                    <?=ToUpper($arResult["User"]["ONLINE_STATUS"]["STATUS_TEXT"])?>
                </div>
                <div class="intranet-user-profile-last-time">
                    <?php
                    if ($arResult["User"]["ONLINE_STATUS"]['STATUS'] === 'idle')
                    {
                        ?>
                        <?= (
                        $arResult["User"]["ONLINE_STATUS"]['LAST_SEEN_TEXT']
                        ? Loc::getMessage(
                        'INTRANET_USER_PROFILE_LAST_SEEN_IDLE_'.($arResult["User"]["PERSONAL_GENDER"] === 'F'? 'F': 'M'),
                        [
                            '#LAST_SEEN#' => $arResult["User"]["ONLINE_STATUS"]['LAST_SEEN_TEXT'],
                        ]
                    )
                        : ''
                    ) ?>
                        <?php
                    }
                    else
                    {
                        ?>
                        <?= (
                    $arResult["User"]["ONLINE_STATUS"]['LAST_SEEN_TEXT']
                        ? Loc::getMessage(
                        'INTRANET_USER_PROFILE_LAST_SEEN_'.($arResult["User"]["PERSONAL_GENDER"] === 'F'? 'F': 'M'),
                        [
                            '#LAST_SEEN#' => $arResult["User"]["ONLINE_STATUS"]['LAST_SEEN_TEXT'],
                        ]
                    )
                        : ''
                    ) ?>
                        <?php
                    }
                    ?>
                </div>
            </div><?php

            $classList = [
                'intranet-user-profile-userpic',
                'ui-icon',
                'ui-icon-common-user',
            ];
            if ($arResult["IsOwnProfile"])
            {
                $classList[] = 'intranet-user-profile-userpic-edit';
            }
            ?><div class="<?= implode(' ', $classList) ?>">
                <?php
                $style = (
                isset($arResult["User"]["PHOTO"]) && !empty($arResult["User"]["PHOTO"])
                    ? 'style="background-image: url(\'' . CHTTP::urnEncode($arResult["User"]["PHOTO"]) . '\'); background-size: cover"'
                    : ''
                );
                ?>
                <i id="intranet-user-profile-photo" <?= $style ?>></i>
                <?php
                if ($arResult["IsOwnProfile"])
                {
                    ?>
                    <div class="intranet-user-profile-userpic-load">
                        <!--<div class="intranet-user-profile-userpic-create" id="intranet-user-profile-photo-camera"><?/*=Loc::getMessage("INTRANET_USER_PROFILE_AVATAR_CAMERA")*/?></div>-->
                        <?php if (!empty($arResult["User"]["PHOTO"])):?>
                            <div class="intranet-user-profile-userpic-download" id="intranet-user-profile-photo-download">
                                <a href="<?=$arResult["User"]["PHOTO"]?>" download>
                                    <?=Loc::getMessage("INTRANET_USER_PROFILE_AVATAR_DOWNLOAD")?>
                                </a>
                            </div>
                        <?php endif;?>

                        <div class="intranet-user-profile-userpic-upload" id="intranet-user-profile-photo-file">
                            <?=Loc::getMessage("INTRANET_USER_PROFILE_AVATAR_LOAD")?>
                        </div>

                        <?php if (count($arResult["User"]["PHOTOS_COLLECTION"]) > 0):?>
                            <div class="intranet-user-profile-userpic-slider-show" id="intranet-user-profile-photo-slider-show">
                                <?=Loc::getMessage("INTRANET_USER_PROFILE_AVATAR_VIEW_ALL")?>
                            </div>
                        <?php endif;?>
                    </div>
                    <div class="intranet-user-profile-userpic-remove" id="intranet-user-profile-photo-remove"></div>
                    <?php
                }
                ?>
            </div>
            <div class="intranet-user-profile-absense">
                <?php
                $APPLICATION->IncludeComponent(
                    "bitrix:intranet.absence.user",
                    "profile",
                    array(
                        "ID" => $arResult["User"]['ID'],
                    ),
                    false,
                    array("HIDE_ICONS"=>"Y")
                );
                ?>
            </div>
        </div>

        <?
        $canViewCvBlock = ($arResult["IsOwnProfile"] || $arResult['Permissions']['staffing']);
        $canEditCvBlock = ($arResult["IsOwnProfile"] || $arResult['Permissions']['staffing']);
        ?>
        <div class="intranet-user-profile-column-block block-with-large-margin"
             style="<?=(!$canViewCvBlock ? 'padding: 0; opacity: 0' : '')?>"
        >
            <?php if ($canViewCvBlock):?>
                <div class="intranet-user-profile-files">
                    <div class="intranet-user-profile-files-block">
                        <a href="<?=$arResult['User']['UF_STAFFING_CV_LINK']?>"
                           id="UF_STAFFING_CV_DOWNLOAD_LINK"
                           download
                            <?php if (empty($arResult['User']['UF_STAFFING_CV_LINK'])):?>
                                onclick="return false"
                                style="pointer-events: none;"
                            <?php endif;?>
                        >
                            <?=Loc::getMessage('INTRANET_USER_PROFILE_STAFFING_CV_TITLE')?>
                            <?php if (empty($arResult['User']['UF_STAFFING_CV_LINK'])):?>
                                <i><?=Loc::getMessage('INTRANET_USER_PROFILE_FILE_NOT_UPLOADED')?></i>
                            <?php endif;?>
                        </a><br>
                        <?php if ($canEditCvBlock):?>
                            <label>
                                <?=Loc::getMessage('INTRANET_USER_PROFILE_UPLOAD_NEW_FILE')?>
                                <input type="file" id="staffing_cv_file_input">
                            </label>
                        <?php endif;?>
                    </div>

                    <div class="intranet-user-profile-files-block">
                        <a href="<?=$arResult['User']['UF_RECRUITMENT_CV_LINK']?>"
                           id="UF_RECRUITMENT_CV_DOWNLOAD_LINK"
                           download
                            <?php if (empty($arResult['User']['UF_RECRUITMENT_CV_LINK'])):?>
                                onclick="return false"
                                style="pointer-events: none;"
                            <?php endif;?>
                        >
                            <?=Loc::getMessage('INTRANET_USER_PROFILE_RECRUITMENT_CV_TITLE')?>
                            <?php if (empty($arResult['User']['UF_RECRUITMENT_CV_LINK'])):?>
                                <i><?=Loc::getMessage('INTRANET_USER_PROFILE_FILE_NOT_UPLOADED')?></i>
                            <?php endif;?>
                        </a><br>
                        <?php if ($canEditCvBlock):?>
                            <label>
                                <?=Loc::getMessage('INTRANET_USER_PROFILE_UPLOAD_NEW_FILE')?>
                                <input type="file" id="recruitment_cv_file_input">
                            </label>
                        <?php endif;?>
                    </div>
                </div>
            <?php endif;?>
        </div>

        <!--<div class="intranet-user-profile-column-block">
            <div class="intranet-user-profile-current-time">
                <p>
                    <?php /*=Loc::getMessage('INTRANET_USER_PROFILE_CURRENT_TIME')*/?>
                    <span id="intranet-user-profile-current-time-block"></span>
                </p>
            </div>
        </div>-->
    </div>

    <div class="intranet-user-profile-column-right">
        <div id="intranet-user-profile-tab-general" class="active">
            <?php
            $uiRes = $APPLICATION->IncludeComponent(
                "bitrix:ui.form",
                "",
                array(
                    "GUID" => $arResult["FormId"],
                    "INITIAL_MODE" => "view",
                    "ENTITY_TYPE_NAME" => "USER",
                    "ENTITY_ID" => $arResult["User"]["ID"],
                    "ENTITY_FIELDS" => $arResult["FormFields"],
                    "ENTITY_CONFIG" => $arResult["FormConfig"],
                    "ENTITY_DATA" => $arResult["FormData"],
                    "ENABLE_SECTION_EDIT" => false,
                    "ENABLE_SECTION_CREATION" => false,
                    "ENABLE_SECTION_DRAG_DROP" => $arResult["EnablePersonalConfigurationUpdate"],
                    "FORCE_DEFAULT_SECTION_NAME" => true,
                    "ENABLE_PERSONAL_CONFIGURATION_UPDATE" => $arResult["EnablePersonalConfigurationUpdate"],
                    "ENABLE_COMMON_CONFIGURATION_UPDATE" => $arResult["EnableCommonConfigurationUpdate"],
                    "ENABLE_SETTINGS_FOR_ALL" => $arResult["EnableSettingsForAll"],
                    "READ_ONLY" => !$arResult["Permissions"]['edit'],
                    "ENABLE_USER_FIELD_CREATION" => $arResult["EnableUserFieldCreation"],
                    "ENABLE_USER_FIELD_MANDATORY_CONTROL" => $arResult["EnableUserFieldMandatoryControl"],
                    "USER_FIELD_ENTITY_ID" => $arResult["UserFieldEntityId"],
                    "USER_FIELD_PREFIX" => $arResult["UserFieldPrefix"],
                    "ENABLE_FIELD_DRAG_DROP" => $arResult["EnablePersonalConfigurationUpdate"],
                    "USER_FIELD_CREATE_SIGNATURE" => $arResult["UserFieldCreateSignature"],
                    "SERVICE_URL" => POST_FORM_ACTION_URI.'&'.bitrix_sessid_get(),
                    "COMPONENT_AJAX_DATA" => array(
                        "COMPONENT_NAME" => $this->getComponent()->getName(),
                        "ACTION_NAME" => "save",
                        "SIGNED_PARAMETERS" => $this->getComponent()->getSignedParameters()
                    )
                )
            );


            if (
                $arResult["CurrentUser"]["STATUS"] !== 'extranet'
                && !empty($arResult["Tags"])
                && (
                    !empty($arResult["Tags"]["COUNT"])
                    || $arResult["Permissions"]['edit']
                    || (int)$USER->getId() === (int)$arResult["User"]["ID"]
                )
                && !in_array($arResult["User"]["STATUS"], [ 'extranet', 'email', 'invited' ])
            )
            {
                ?><div id="intranet-user-profile-tags-container" class="intranet-user-profile-container<?=(empty($arResult["Tags"]["COUNT"]) ? ' intranet-user-profile-container-empty' : '')?>">
                <div class="intranet-user-profile-container-header">
                    <div class="intranet-user-profile-container-title">
                        <?=Loc::getMessage('INTRANET_USER_PROFILE_TAGS_TITLE')?>
                    </div>
                    <?php

                    if ($arResult["IsOwnProfile"])
                    {
                        ?><div id="intranet-user-profile-add-tags" class="intranet-user-profile-container-edit"><?=Loc::getMessage('INTRANET_USER_PROFILE_TAGS_MODIFY')?></div><?php
                    }

                    ?></div>
                <div class="intranet-user-profile-container-body intranet-user-profile-tags-wrapper"><?php

                    if ($arResult["IsOwnProfile"])
                    {
                        ?><div id="intranet-user-profile-tags-input" class="intranet-user-profile-tags-area"></div><?php
                    }

                    ?><div class="intranet-user-profile-tags" id="intranet-user-profile-tags"></div>

                    <div id="intranet-user-profile-interests-stub" class="intranet-user-profile-about intranet-user-profile-empty-stub intranet-user-profile-about-interests">
                        <div class="intranet-user-profile-post-edit-stub-default"><?=Loc::getMessage('INTRANET_USER_PROFILE_INTERESTS_STUB_TEXT')?></div>
                        <?php if ($arResult["IsOwnProfile"]):?>
                            <a id="intranet-user-profile-interests-stub-button" class="ui-btn ui-btn-sm ui-btn-light-border ui-btn-round"><?=Loc::getMessage('INTRANET_USER_PROFILE_INTERESTS_STUB_BUTTON_2')?></a>
                        <?php endif;?>
                    </div>

                    <div class="intranet-user-profile-thanks-users-loader" id="intranet-user-profile-tags-loader"></div>
                </div>
                </div><?php
            }
            ?>
        </div>

        <?php if ($arResult['Permissions']['staffing'] || $arResult["IsOwnProfile"]):?>
            <div id="intranet-user-profile-tab-staffing" class="intranet-user-profile-tab">
                <div class="intranet-user-profile-column-block">
                    <table class="additional-user-data-for-staffing">
                        <tbody>
                        <tr>
                            <td><?=Loc::getMessage('INTRANET_USER_PROFILE_STAFFING_NAME')?></td>
                            <td><p><?=$arResult['User'][Fields::getFioEnUfCode()]?></p></td>
                        </tr>

                        <?php if ($arResult['Permissions']['staffing']):?>
                            <tr>
                                <td><?=Loc::getMessage('INTRANET_USER_PROFILE_STAFFING_ROLE')?></td>
                                <td><p><?=$arResult['User'][Fields::getPositionEnUfCode()]?></p></td>
                            </tr>
                            <tr>
                                <td><?=Loc::getMessage('INTRANET_USER_PROFILE_STAFFING_TRACK')?></td>
                                <td><p><?=$arResult['User'][Fields::getTrackUfCode()]?></p></td>
                            </tr>
                        <?php endif;?>

                        <tr>
                            <td><?=Loc::getMessage('INTRANET_USER_PROFILE_STAFFING_TENURE_C')?></td>
                            <td><p><?=$arResult['User'][Fields::getTenureCompanyUfCode()]?></p></td>
                        </tr>
                        <tr>
                            <td><?=Loc::getMessage('INTRANET_USER_PROFILE_STAFFING_TENURE_R')?></td>
                            <td><p><?=$arResult['User'][Fields::getTenurePositionUfCode()]?></p></td>
                        </tr>

                        <?php if ($arResult['Permissions']['staffing']):?>
                            <tr>
                                <td><?=Loc::getMessage('INTRANET_USER_PROFILE_STAFFING_PER_DIEM')?></td>
                                <td><p><?=$arResult['User'][Fields::getBasePerDiemUfCode()]?></p></td>
                            </tr>
                        <?php endif;?>

                        <tr>
                            <td><?=Loc::getMessage('INTRANET_USER_PROFILE_STAFFING_PHONE')?></td>
                            <td><p><?=$arResult['User']['PERSONAL_MOBILE']?></p></td>
                        </tr>
                        </tbody>
                    </table>
                </div>

                <?php if ($arResult['Permissions']['staffing']):?>
                <div class="intranet-user-profile-column-block">
                    <h3 class="intranet-user-profile-summary-title">
                        <span><?=Loc::getMessage('INTRANET_USER_PROFILE_STAFFING_SUMMARY')?></span>
                        <button class="ui-btn ui-btn-xs ui-btn-icon-edit" id="intranet-user-profile-summary-edit-btn">
                            <?=Loc::getMessage('INTRANET_USER_PROFILE_EDIT')?>
                        </button>
                    </h3>
                    <div id="intranet-user-profile-summary-edit-block">
                        <p id="intranet-user-profile-summary-value">
                            <?=$arResult['User']['UF_SHORT_SUMMARY']?>
                        </p>
                        <div id="intranet-user-profile-summary-edit-form">
                            <div class="ui-ctl ui-ctl-textarea ui-ctl-no-resize">
                                <textarea class="ui-ctl-element"
                                          id="intranet-user-profile-summary-textarea"
                                ><?=$arResult['User']['UF_SHORT_SUMMARY']?></textarea>
                            </div>
                            <button class="ui-btn ui-btn-primary" id="intranet-user-profile-summary-submit-btn">
                                <?=Loc::getMessage('INTRANET_USER_PROFILE_SUBMIT')?>
                            </button>
                        </div>
                    </div>
                </div>
                <?php endif;?>

                <?php $APPLICATION->IncludeComponent('cbit:mc.staffing.user-employment', '', [
                    'USER_ID' => $arParams["ID"]
                ]);?>

                <?php $APPLICATION->IncludeComponent('cbit:mc.staffing.user-report', '', [
                    'USER_ID' => $arParams["ID"]
                ]);?>
            </div>
        <?php endif;?>

        <div id="intranet-user-profile-tab-education" class="intranet-user-profile-tab">
            <?php include_once __DIR__."/include/education.php"?>
        </div>
    </div>
</div>
<?php
$moveRightsConfirmText = '';
?>
<?php if (count($arResult["User"]["PHOTOS_COLLECTION"]) > 0):?>
    <div id="intranet-user-profile-photo-slider-overlay">
        <div id="intranet-user-profile-photo-slider">
            <div class="intranet-user-profile-photo-slider__wrapper">
                <div class="intranet-user-profile-photo-slider__items">
                    <?php foreach ($arResult["User"]["PHOTOS_COLLECTION"] as $photo):?>
                        <div class="intranet-user-profile-photo-slider__item"
                             data-id="<?=$photo['FILE_ID']?>"
                            <?php if ($photo['IS_CURRENT_AVATAR'] === "Y"):?>
                                data-avatar="Y"
                            <?php endif;?>
                        >
                            <img src="<?=$photo['FILE_LINK']?>">
                            <div class="intranet-user-profile-photo-slider__item-actions">
                                <button data-action="set_as_avatar" class="ui-btn ui-btn-xs ui-btn-primary">
                                    <?=Loc::getMessage('INTRANET_USER_PROFILE_SLIDE_SET_AS_AVATAR')?>
                                </button>
                                <button data-action="delete_file" class="ui-btn ui-btn-xs ui-btn-danger">
                                    <?=Loc::getMessage('INTRANET_USER_PROFILE_SLIDE_DELETE')?>
                                </button>
                                <button data-action="download" class="ui-btn ui-btn-xs ui-btn-success">
                                    <a href="<?=$photo['FILE_LINK']?>" download>
                                        <?=Loc::getMessage('INTRANET_USER_PROFILE_SLIDE_DOWNLOAD')?>
                                    </a>
                                </button>
                            </div>
                        </div>
                    <?php endforeach;?>
                </div>
            </div>
            <a class="intranet-user-profile-userpic-slider-btn-prev intranet-user-profile-photo-slider__control intranet-user-profile-photo-slider__control_prev" href="#" role="button" data-slide="prev"></a>
            <a class="intranet-user-profile-userpic-slider-btn-next intranet-user-profile-photo-slider__control intranet-user-profile-photo-slider__control_next" href="#" role="button" data-slide="next"></a>
        </div>
    </div>
<?php endif;?>
<script>
    BX.message({
        "INTRANET_USER_PROFILE_REINVITE" : "<?= CUtil::JSEscape(Loc::getMessage("INTRANET_USER_PROFILE_REINVITE")) ?>",
        "INTRANET_USER_PROFILE_DELETE" : "<?= CUtil::JSEscape(Loc::getMessage("INTRANET_USER_PROFILE_DELETE")) ?>",
        "INTRANET_USER_PROFILE_FIRE" : "<?= CUtil::JSEscape(Loc::getMessage("INTRANET_USER_PROFILE_FIRE")) ?>",
        "INTRANET_USER_PROFILE_MOVE_TO_INTRANET" : "<?= CUtil::JSEscape(Loc::getMessage("INTRANET_USER_PROFILE_MOVE_TO_INTRANET")) ?>",
        "INTRANET_USER_PROFILE_HIRE" : "<?= CUtil::JSEscape(Loc::getMessage("INTRANET_USER_PROFILE_HIRE")) ?>",
        "INTRANET_USER_PROFILE_ADMIN_MODE" : "<?= CUtil::JSEscape(Loc::getMessage("INTRANET_USER_PROFILE_ADMIN_MODE")) ?>",
        "INTRANET_USER_PROFILE_QUIT_ADMIN_MODE" : "<?= CUtil::JSEscape(Loc::getMessage("INTRANET_USER_PROFILE_QUIT_ADMIN_MODE")) ?>",
        "INTRANET_USER_PROFILE_SET_ADMIN_RIGHTS" : "<?= CUtil::JSEscape(Loc::getMessage("INTRANET_USER_PROFILE_SET_ADMIN_RIGHTS")) ?>",
        "INTRANET_USER_PROFILE_REMOVE_ADMIN_RIGHTS" : "<?= CUtil::JSEscape(Loc::getMessage("INTRANET_USER_PROFILE_REMOVE_ADMIN_RIGHTS")) ?>",
        "INTRANET_USER_PROFILE_SYNCHRONIZE" : "<?= CUtil::JSEscape(Loc::getMessage("INTRANET_USER_PROFILE_SYNCHRONIZE")) ?>",
        "INTRANET_USER_PROFILE_FIRE_CONFIRM" : "<?= CUtil::JSEscape(Loc::getMessage("INTRANET_USER_PROFILE_FIRE_CONFIRM")) ?>",
        "INTRANET_USER_PROFILE_DELETE_CONFIRM" : "<?= CUtil::JSEscape(Loc::getMessage("INTRANET_USER_PROFILE_DELETE_CONFIRM")) ?>",
        "INTRANET_USER_PROFILE_HIRE_CONFIRM" : "<?= CUtil::JSEscape(Loc::getMessage("INTRANET_USER_PROFILE_HIRE_CONFIRM")) ?>",
        "INTRANET_USER_PROFILE_YES" : "<?= CUtil::JSEscape(Loc::getMessage("INTRANET_USER_PROFILE_YES")) ?>",
        "INTRANET_USER_PROFILE_NO" : "<?= CUtil::JSEscape(Loc::getMessage("INTRANET_USER_PROFILE_NO")) ?>",
        "INTRANET_USER_PROFILE_MOVE" : "<?= CUtil::JSEscape(Loc::getMessage("INTRANET_USER_PROFILE_MOVE")) ?>",
        "INTRANET_USER_PROFILE_CLOSE" : "<?= CUtil::JSEscape(Loc::getMessage("INTRANET_USER_PROFILE_CLOSE")) ?>",
        "INTRANET_USER_PROFILE_SAVE" : "<?= CUtil::JSEscape(Loc::getMessage("INTRANET_USER_PROFILE_SAVE")) ?>",
        "INTRANET_USER_PROFILE_MOVE_TO_INTRANET_TITLE" : "<?= CUtil::JSEscape(Loc::getMessage("INTRANET_USER_PROFILE_MOVE_TO_INTRANET_TITLE")) ?>",
        "INTRANET_USER_PROFILE_REINVITE_SUCCESS" : "<?= CUtil::JSEscape(Loc::getMessage("INTRANET_USER_PROFILE_REINVITE_SUCCESS")) ?>",
        "INTRANET_USER_PROFILE_PHOTO_DELETE_CONFIRM" : "<?= CUtil::JSEscape(Loc::getMessage("INTRANET_USER_PROFILE_PHOTO_DELETE_CONFIRM")) ?>",
        "INTRANET_USER_PROFILE_MOVE_ADMIN_RIGHTS_CONFIRM" : "<?= CUtil::JSEscape($moveRightsConfirmText) ?>",
        "INTRANET_USER_PROFILE_FIELD_NAME" : "<?= CUtil::JSEscape(Loc::getMessage("INTRANET_USER_PROFILE_FIELD_NAME")) ?>",
        "INTRANET_USER_PROFILE_FIELD_LAST_NAME" : "<?= CUtil::JSEscape(Loc::getMessage("INTRANET_USER_PROFILE_FIELD_LAST_NAME")) ?>",
        "INTRANET_USER_PROFILE_FIELD_SECOND_NAME" : "<?= CUtil::JSEscape(Loc::getMessage("INTRANET_USER_PROFILE_FIELD_SECOND_NAME")) ?>",
        "INTRANET_USER_PROFILE_TAGS_POPUP_ADD" : "<?= CUtil::JSEscape(Loc::getMessage("INTRANET_USER_PROFILE_TAGS_POPUP_ADD")) ?>",
        "INTRANET_USER_PROFILE_CONFIRM_PASSWORD" : "<?= CUtil::JSEscape(Loc::getMessage("INTRANET_USER_PROFILE_CONFIRM_PASSWORD")) ?>",
        "INTRANET_USER_PROFILE_TAGS_POPUP_TITLE" : "<?= CUtil::JSEscape(Loc::getMessage("INTRANET_USER_PROFILE_TAGS_POPUP_TITLE")) ?>",
        "INTRANET_USER_PROFILE_TAGS_POPUP_SEARCH_TITLE" : "<?= CUtil::JSEscape(Loc::getMessage("INTRANET_USER_PROFILE_TAGS_POPUP_SEARCH_TITLE")) ?>",
        "INTRANET_USER_PROFILE_TAGS_POPUP_HINT_3" : "<?= CUtil::JSEscape(Loc::getMessage("INTRANET_USER_PROFILE_TAGS_POPUP_HINT_3")) ?>",
        "INTRANET_USER_PROFILE_TAGS_POPUP_HINT_2" : "<?= CUtil::JSEscape(Loc::getMessage("INTRANET_USER_PROFILE_TAGS_POPUP_HINT_2")) ?>",
        "INTRANET_USER_FIILDS_SETTINGS" : "<?= CUtil::JSEscape(Loc::getMessage("INTRANET_USER_FIILDS_SETTINGS")) ?>",
        "INTRANET_USER_PROFILE_SET_INEGRATOR_RIGHTS" : "<?= CUtil::JSEscape(Loc::getMessage("INTRANET_USER_PROFILE_SET_INEGRATOR_RIGHTS")) ?>",
        "INTRANET_USER_PROFILE_FIRE_INVITED_USER" : "<?= CUtil::JSEscape(Loc::getMessage("INTRANET_USER_PROFILE_FIRE_INVITED_USER")) ?>",
        "INTRANET_USER_PROFILE_SET_INTEGRATOR_RIGHTS_CONFIRM" : "<?= CUtil::JSEscape(
            Loc::getMessage("INTRANET_USER_PROFILE_SET_INTEGRATOR_RIGHTS_CONFIRM", array(
                "#NAME#" => "<b>".$arResult["User"]["FULL_NAME"]."</b>",
                "#LINK_START#" => "<a href=\"javascript:void(0)\" onclick='top.BX.Helper.show(\"redirect=detail&code=7725333\");'>",
                "#LINK_END#" => "</a>"
            ))
        ) ?>",
        "INTRANET_USER_PROFILE_STRESSLEVEL_NORESULT_INDICATOR_TEXT" : "<?= CUtil::JSEscape(Loc::getMessage("INTRANET_USER_PROFILE_STRESSLEVEL_NORESULT_INDICATOR_TEXT")) ?>",
        "OPERATION_SUCCESSFUL": '<?=Loc::getMessage("INTRANET_USER_PROFILE_OPERATION_SUCCESSFUL")?>',
        "FILE_NOT_UPLOADED": '<?=Loc::getMessage('INTRANET_USER_PROFILE_FILE_NOT_UPLOADED')?>',
        "SLIDE_DELETE_SUCCESSFUL": '<?=Loc::getMessage('INTRANET_USER_PROFILE_SLIDE_DELETE_SUCCESSFUL')?>',
        "INTRANET_USER_PROFILE_EDU_POPUP_TITLE": '<?=Loc::getMessage('INTRANET_USER_PROFILE_EDU_POPUP_TITLE')?>',

        "EDUCATION_TYPE": '<?=Loc::getMessage('INTRANET_USER_PROFILE_EDU_EDUCATION_TYPE')?>',
        "INSTITUTION_RU": '<?=Loc::getMessage('INTRANET_USER_PROFILE_EDU_INSTITUTION_RU')?>',
        "INSTITUTION_EN": '<?=Loc::getMessage('INTRANET_USER_PROFILE_EDU_INSTITUTION_EN')?>',
        "SPECIALTY_RU": '<?=Loc::getMessage('INTRANET_USER_PROFILE_EDU_SPECIALTY_RU')?>',
        "SPECIALTY_EN": '<?=Loc::getMessage('INTRANET_USER_PROFILE_EDU_SPECIALTY_EN')?>',
        "QUALIFICATION_RU": '<?=Loc::getMessage('INTRANET_USER_PROFILE_EDU_QUALIFICATION_RU')?>',
        "QUALIFICATION_EN": '<?=Loc::getMessage('INTRANET_USER_PROFILE_EDU_QUALIFICATION_EN')?>',
        "DATE_BEGIN_STUDYING": '<?=Loc::getMessage('INTRANET_USER_PROFILE_EDU_DATE_BEGIN_STUDYING')?>',
        "DATE_END_STUDYING": '<?=Loc::getMessage('INTRANET_USER_PROFILE_EDU_DATE_END_STUDYING')?>',
        "OUTSIDE_RUSSIA": '<?=Loc::getMessage('INTRANET_USER_PROFILE_EDU_OUTSIDE_RUSSIA')?>',
    });

    new BX.Intranet.UserProfile.Manager({
        educationTypes: <?= CUtil::PhpToJSObject($arResult["EducationTypes"]) ?>,
        cvFileInputIds: {
            'staffing': 'staffing_cv_file_input',
            'recruitment': 'recruitment_cv_file_input'
        },
        currentTime: '<?=date("Y-m-d\TH:i:s", (time()+CTimeZone::GetOffset($arResult["User"]["ID"])))?>',
        selectorsToDelete: <?= CUtil::PhpToJSObject($arResult['selectorsToDelete']) ?>,
        signedParameters: '<?=$this->getComponent()->getSignedParameters()?>',
        componentName: '<?=$this->getComponent()->getName() ?>',
        canEditProfile: '<?=$arResult["Permissions"]['edit'] ? "Y" : "N"?>',
        badgesData: <?= (CUtil::phpToJSObject(!empty($arResult['Gratitudes']['BADGES']) ? $arResult['Gratitudes']['BADGES'] : [])) ?>,
        gratPostListPageSize: <?= (int)$arParams['GRAT_POST_LIST_PAGE_SIZE'] ?>,
        userId: <?= (int)$arResult["User"]["ID"] ?>,
        userStatus: <?= CUtil::PhpToJSObject($arResult["User"]["STATUS"]) ?>,
        isOwnProfile: '<?=$arResult["IsOwnProfile"] ? "Y" : "N"?>',
        urls: <?= CUtil::PhpToJSObject($arResult["Urls"]) ?>,
        isSessionAdmin: "<?=$arResult["User"]["IS_SESSION_ADMIN"] ? "Y" : "N"?>",
        showSonetAdmin: "<?=$arResult["User"]["SHOW_SONET_ADMIN"] ? "Y" : "N"?>",
        isExtranetUser: "<?=$arResult["User"]["IS_EXTRANET"] ? "Y" : "N"?>",
        isCurrentUserIntegrator: "<?=$arResult["IS_CURRENT_USER_INTEGRATOR"] ? "Y" : "N"?>",
        languageId: "<?=LANGUAGE_ID?>",
        siteId: "<?=SITE_ID?>",
        isCloud: "<?=$arResult["isCloud"] ? "Y" : "N"?>",
        isRusCloud: "<?=$arResult["isRusCloud"] ? "Y" : "N"?>",
        adminRightsRestricted: "<?=$arResult["adminRightsRestricted"] ? "Y" : "N"?>",
        delegateAdminRightsRestricted: "<?=$arResult["delegateAdminRightsRestricted"] ? "Y" : "N"?>",
        isFireUserEnabled: "<?=$arResult["isFireUserEnabled"] ? "Y" : "N"?>",
        profilePostData: {
            formId: '<?= (!empty($arResult["ProfileBlogPost"]["formParams"]) ? CUtil::JSEscape($arResult["ProfileBlogPost"]["formParams"]["FORM_ID"]) : '') ?>',
            lheId: '<?= (!empty($arResult["ProfileBlogPost"]["formParams"]) ? CUtil::JSEscape($arResult["ProfileBlogPost"]["formParams"]["LHE"]["id"]) : '') ?>'
        },
        initialFields: <?=CUtil::PhpToJSObject($arResult["User"])?>,
        gridId: '<?='INTRANET_USER_GRID_'.SITE_ID?>',
        isCurrentUserAdmin: '<?=($arResult['Permissions']['admin'] ? "Y" : "N")?>',
        voximplantEnablePhones: <?=CUtil::PhpToJSObject($arResult["User"]["VOXIMPLANT_ENABLE_PHONES"])?>
    });
</script>
<script>
    (function() {
        var likes = document.querySelectorAll('[data-role="intranet-user-profile-column-block-title-like"]');

        for (var i = 0; i < likes.length; i++)
        {
            likes[i].addEventListener('click', function() {
                this.classList.add('intranet-user-profile-column-block-title-like-animate');

                this.addEventListener('animationend', function() {
                    this.classList.remove('intranet-user-profile-column-block-title-like-animate');
                })
            }, false);
        }
    })();
</script>