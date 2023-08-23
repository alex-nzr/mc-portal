<?php use Bitrix\Main\Localization\Loc;
use Cbit\Mc\Staffing\Service\Access\Permission;
use Cbit\Mc\Staffing\Service\Container;

if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die();
/**
 * @var CMain $APPLICATION;
 * @var array $arResult;
 * @var array $arParams;
 * @var \Cbit\Mc\Staffing\Component\ProjectTeam $component
 */
?>

<div class="staffing-project-team-block">
    <h3 id="needle-employees-title"></h3>
    <table>
        <thead>
            <tr>
                <!--<th>№</th>-->
                <th><?=Loc::getMessage($component->moduleId . '_NEEDLE_USER_ROLE')?></th>
                <th><?=Loc::getMessage($component->moduleId . '_NEEDLE_EMPLOYMENT_PERCENT')?></th>
                <th><?=Loc::getMessage($component->moduleId . '_NEEDLE_DATE_FROM')?></th>
                <th><?=Loc::getMessage($component->moduleId . '_NEEDLE_DATE_TO')?></th>
            </tr>
        </thead>
        <tbody id="needle-employees-table-body"></tbody>
    </table>

    <?php if (Container::getInstance()->getUserPermissions()->hasPdStaffingPermissions()):?>
        <div class="staffing-project-team-btn-block">
            <button class="ui-btn ui-btn-success ui-btn-icon-add" id="staffing-project-team-needle-add-btn">
                <?=Loc::getMessage($component->moduleId . '_ADD_BTN_TEXT')?>
            </button>
        </div>
    <?php endif;?>
</div>

<div class="staffing-project-team-block">
    <h3 id="project-team-title"></h3>
    <table>
        <thead id="project-team-table-head"></thead>
        <tbody id="project-team-table-body"></tbody>
    </table>
</div>


<script>
    BX.ready(function (){
        BX.message({
            NEEDLE_BLOCK_TITLE: '<?=Loc::getMessage($component->moduleId.'_NEEDLE_BLOCK_TITLE')?>',
            NEEDLE_BLOCK_EMPTY: '<?=Loc::getMessage($component->moduleId.'_NEEDLE_BLOCK_EMPTY')?>',
            TEAM_BLOCK_TITLE: '<?=Loc::getMessage($component->moduleId.'_TEAM_BLOCK_TITLE')?>',
            TEAM_BLOCK_EMPTY: '<?=Loc::getMessage($component->moduleId.'_TEAM_BLOCK_EMPTY')?>',

            NEEDLE_ADD_POPUP_TITLE: '<?=Loc::getMessage($component->moduleId.'_NEEDLE_ADD_POPUP_TITLE')?>',
            NEEDLE_UPDATE_POPUP_TITLE: '<?=Loc::getMessage($component->moduleId.'_NEEDLE_UPD_POPUP_TITLE')?>',
            NEEDLE_USER_ROLE: '<?=Loc::getMessage($component->moduleId.'_NEEDLE_USER_ROLE')?>',
            NEEDLE_EMPLOYMENT_PERCENT: '<?=Loc::getMessage($component->moduleId.'_NEEDLE_EMPLOYMENT_PERCENT')?>',
            DATE_FROM: '<?=Loc::getMessage($component->moduleId.'_NEEDLE_DATE_FROM')?>',
            DATE_TO: '<?=Loc::getMessage($component->moduleId.'_NEEDLE_DATE_TO')?>',
            SAVE_TEXT: '<?=Loc::getMessage($component->moduleId.'_NEEDLE_ADD_POPUP_SAVE')?>',
            EXIT_TEXT: '<?=Loc::getMessage($component->moduleId.'_NEEDLE_ADD_POPUP_EXIT')?>',
            REMOVE_TEXT: '<?=Loc::getMessage($component->moduleId.'_REMOVE_BTN_TEXT')?>',
            EDIT_TEXT: '<?=Loc::getMessage($component->moduleId.'_EDIT_BTN_TEXT')?>',
            CONFIRM_DELETION: '<?=Loc::getMessage($component->moduleId.'_NEEDLE_ITEM_CONFIRM_DELETION')?>',
            PERIOD_EDIT_POPUP_TITLE: '<?=Loc::getMessage($component->moduleId.'_PERIOD_EDIT_POPUP_TITLE')?>',
        });

        BX.Cbit.Mc.Staffing.ProjectTeamComponent.init({
            componentName: '<?=$component->getName()?>',
            signedParameters: '<?=$component->getSignedParameters()?>',
            needleEmployees: <?= CUtil::PhpToJSObject($arResult["NEEDLE"]) ?>,
            staffingUserRoles: <?= CUtil::PhpToJSObject($arResult["STAFFING_USER_ROLES"]) ?>,
            projectTeam: <?= CUtil::PhpToJSObject($arResult["TEAM"]) ?>,
            projectData: <?= CUtil::PhpToJSObject($arResult["PROJECT_DATA"]) ?>,
        });
    })
</script>
