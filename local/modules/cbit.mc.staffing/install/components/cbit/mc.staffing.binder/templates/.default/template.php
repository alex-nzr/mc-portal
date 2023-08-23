<?php
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die();
/**
 * @var CMain $APPLICATION;
 * @var array $arResult;
 * @var array $arParams;
 * @var string $templateFolder
 * @var \Cbit\Mc\Staffing\Component\Binder $component
 */
use Bitrix\Main\Localization\Loc;
use Cbit\Mc\Staffing\Config\Constants;

$APPLICATION->SetTitle(Loc::getMessage($component->moduleId . '_BINDER_PAGE_TITLE'));

?>
<div id="staffing-binder-root">
    <div class="staffing-binder-column">
        <div class="staffing-binder-column-head">
            <h3><?=Loc::getMessage($component->moduleId . '_BINDER_USERS_TITLE')?></h3>
            <button class="ui-btn ui-btn-sm ui-btn-primary ui-btn-icon-page" id="users-export-excel-btn">Excel</button>
        </div>

        <div class="main-ui-filter-block">
            <?php $APPLICATION->IncludeComponent('bitrix:main.ui.filter', '', [
                'FILTER_ID'          => $arResult["USERS_FILTER_ID"],
                'GRID_ID'            => $arResult["USERS_FILTER_ID"],
                'FILTER'             => $arResult["USERS_FILTER"],
                'ENABLE_LIVE_SEARCH' => false,
                'ENABLE_LABEL'       => true,
                'DISABLE_SEARCH'     => true,
            ]); ?>
        </div>

        <div class="binder-items-table-wrapper">
            <table class="binder-items-table" id="binder_users_table">
                <thead>
                <tr>
                    <?php foreach($arResult["USER_DISPLAY_FIELDS"] as $cellTitle):?>
                        <th>
                            <span><?=$cellTitle?></span>
                        </th>
                    <?php endforeach;?>
                </tr>
                </thead>
                <tbody id="<?=$arResult['USERS_FILTER_ID']?>"></tbody>
            </table>
        </div>

        <div class="staffing-binder-column-more-btn-wrapper">
            <button class="ui-btn ui-btn-primary" id="staffing-binder-users-more-btn">
                <?=Loc::getMessage($component->moduleId . '_BINDER_SHOW_MORE')?>
            </button>
            <p id="staffing-binder-users-count-text"></p>
        </div>
    </div>

    <div class="staffing-binder-column">
        <div class="staffing-binder-column-head">
            <h3><?=Loc::getMessage($component->moduleId . '_BINDER_PROJECTS_TITLE')?></h3>
            <button class="ui-btn ui-btn-sm ui-btn-primary ui-btn-icon-page" id="projects-export-excel-btn">Excel</button>
        </div>

        <div class="main-ui-filter-block">
            <?php $APPLICATION->IncludeComponent('bitrix:main.ui.filter', '', [
                'FILTER_ID'          => $arResult["PROJECTS_FILTER_ID"],
                'GRID_ID'            => $arResult["PROJECTS_FILTER_ID"],
                'FILTER'             => $arResult["PROJECTS_FILTER"],
                'ENABLE_LIVE_SEARCH' => false,
                'ENABLE_LABEL'       => true,
                'DISABLE_SEARCH'     => true,
            ]); ?>
        </div>

        <div class="binder-items-table-wrapper">
            <table class="binder-items-table" id="binder_users_table">
                <thead>
                <tr>
                    <?php foreach($arResult["PROJECT_DISPLAY_FIELDS"] as $cellTitle):?>
                        <?php if (in_array($cellTitle, $component->getProjectDisplayFields())):?>
                            <th>
                                <span><?=$cellTitle?></span>
                            </th>
                        <?php endif;?>
                    <?php endforeach;?>
                </tr>
                </thead>
                <tbody id="<?=$arResult['PROJECTS_FILTER_ID']?>"></tbody>
            </table>
        </div>

        <div class="staffing-binder-column-more-btn-wrapper">
            <button class="ui-btn ui-btn-primary" id="staffing-binder-projects-more-btn">
                <?=Loc::getMessage($component->moduleId . '_BINDER_SHOW_MORE')?>
            </button>
            <p id="staffing-binder-projects-count-text"></p>
        </div>
    </div>
</div>

<script>
    BX.ready(function (){
        BX.message({
            SAVE_BTN_TEXT: '<?=Loc::getMessage($component->moduleId.'_BINDER_SAVE_BTN_TEXT')?>',
            CANCEL_BTN_TEXT: '<?=Loc::getMessage($component->moduleId.'_BINDER_CANCEL_BTN_TEXT')?>',
            EDIT_BTN_TEXT: '<?=Loc::getMessage($component->moduleId.'_BINDER_EDIT_BTN_TEXT')?>',
            STAFFING_TYPE: '<?=Loc::getMessage($component->moduleId.'_BINDER_STAFFING_TYPE')?>',
            STAFFING_USER_ROLE: '<?=Loc::getMessage($component->moduleId.'_BINDER_STAFFING_USER_ROLE')?>',
            STAFFING_DATE_FROM: '<?=Loc::getMessage($component->moduleId.'_BINDER_STAFFING_DURATION_FROM')?>',
            STAFFING_DATE_TO: '<?=Loc::getMessage($component->moduleId.'_BINDER_STAFFING_DURATION_TO')?>',
            STAFFING_EMPLOYMENT: '<?=Loc::getMessage($component->moduleId.'_BINDER_STAFFING_EMPLOYMENT')?>',
            CURRENT_USER_EMPLOYMENT: "<?=Loc::getMessage($component->moduleId.'_BINDER_CURRENT_USER_EMPLOYMENT')?>",
            CURRENT_USER_EMPLOYMENT_MORE: '<?=Loc::getMessage($component->moduleId.'_BINDER_CURRENT_USER_EMPLOYMENT_MORE')?>',
            STAFFING_USER_PER_DIEM: '<?=Loc::getMessage($component->moduleId.'_BINDER_STAFFING_USER_PER_DIEM')?>',
            PER_DIEM_EDIT_PDO: '<?=Loc::getMessage($component->moduleId.'_BINDER_STAFFING_PER_DIEM_EDIT_PDO')?>',
            PER_DIEM_EDIT_FROM: '<?=Loc::getMessage($component->moduleId.'_BINDER_STAFFING_PER_DIEM_EDIT_FROM')?>',
            PER_DIEM_EDIT_TO: '<?=Loc::getMessage($component->moduleId.'_BINDER_STAFFING_PER_DIEM_EDIT_TO')?>',
            PER_DIEM_EDIT_REASON: '<?=Loc::getMessage($component->moduleId.'_BINDER_STAFFING_PER_DIEM_EDIT_REASON')?>',
        });

        BX.Cbit.Mc.Staffing.BindingComponent.init({
            backUrl: '<?=$APPLICATION->GetCurPage()?>',
            componentName: '<?=$component->getName()?>',
            usersFilterId: '<?=$arResult["USERS_FILTER_ID"]?>',
            projectsFilterId: '<?=$arResult["PROJECTS_FILTER_ID"]?>',
            templateFolder: '<?=$templateFolder?>',
            userDisplayFields: <?= CUtil::PhpToJSObject($arResult["USER_DISPLAY_FIELDS"]) ?>,
            projectDisplayFields: <?= CUtil::PhpToJSObject($arResult["PROJECT_DISPLAY_FIELDS"]) ?>,
            staffingUserRoles: <?= CUtil::PhpToJSObject($arResult["STAFFING_USER_ROLES"]) ?>,
            staffingEmploymentTypes: <?= CUtil::PhpToJSObject($arResult["STAFFING_EMPLOYMENT_TYPES"]) ?>,
            staffingEmploymentTypeStaffed: '<?=Constants::STAFFING_EMPLOYMENT_TYPE_STAFF ?>',
            staffingEmploymentTypeBeach: '<?=Constants::STAFFING_EMPLOYMENT_TYPE_BEACH ?>',
            projectTypeFieldCode: '<?=$component::PROJECT_FIELD_EMP_TYPE;?>',
            perDiemEditReasons: <?= CUtil::PhpToJSObject($arResult["PER_DIEM_EDIT_REASONS"]) ?>,
        });
    });
</script>
