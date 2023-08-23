<?php
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die();
/**
 * @var CMain $APPLICATION;
 * @var array $arResult;
 * @var array $arParams;
 * @var \Cbit\Mc\RI\Component\TeamProfile $component
 */

use Bitrix\Intranet\UserField\Types\EmployeeType;
use Bitrix\Main\Localization\Loc;
use Bitrix\UI\Toolbar\Facade\Toolbar;
use Cbit\Mc\Core\Service\Integration\UI\EntitySelector\RIUserProvider;
use Cbit\Mc\RI\Service\Access\Permission;

$toolbarId = mb_strtolower($arResult['GRID_ID']).'_toolbar';

Toolbar::deleteFavoriteStar();

Toolbar::addFilter([
    'GRID_ID' => $arResult['GRID_ID'],
    'FILTER_ID' => $arResult['FILTER_ID'],
    'FILTER' => $arResult['FILTER'],
    'FILTER_PRESETS' => $arResult['FILTER_PRESETS'],
    'ENABLE_LIVE_SEARCH' => false,
    'DISABLE_SEARCH'     => true,
    'ENABLE_LABEL' => true,
]);

$coordinatorSelectionWrapId  = 'coordinator-selection-wrapper';
$coordinatorCurrentWrapperId = 'coordinator-selection-current-wrapper';
$coordinatorEditWrapperId    = 'coordinator-selection-edit-wrapper';
$coordinatorCurrentBlockId   = 'coordinator-selection-current-block';
$coordinatorEditBtnId        = 'coordinator-selection-edit-btn';
$coordinatorSubmitBtnId      = 'coordinator-selection-submit-btn';
$coordinatorEditBlockId      = 'coordinator-selection-edit-block';

$teamDescWrapId             = 'team-description-wrapper';
$teamDescCurrentWrapperId   = 'team-description-current-wrapper';
$teamDescCurrentBlockId     = 'team-description-current-block';
$teamDescEditBtnId          = 'team-description-edit-btn';
$teamDescEditWrapperId      = 'team-description-edit-wrapper';
$teamDescEditBlockId        = 'team-description-edit-block';
$teamDescSubmitBtnId        = 'team-description-submit-btn';

$teamWorkTimeWrapId             = 'team-work-time-wrapper';
$teamWorkTimeCurrentWrapperId   = 'team-work-time-current-wrapper';
$teamWorkTimeCurrentBlockId     = 'team-work-time-current-block';
$teamWorkTimeEditBtnId          = 'team-work-time-edit-btn';
$teamWorkTimeEditWrapperId      = 'team-work-time-edit-wrapper';
$teamWorkTimeEditFrom           = 'team-work-time-edit-from';
$teamWorkTimeEditTo             = 'team-work-time-edit-to';
$teamWorkTimeSubmitBtnId        = 'team-work-time-submit-btn';
?>

<div class="ri-team-profile-wrapper">
    <div class="ri-team-profile-head">
        <div id="<?=$coordinatorSelectionWrapId?>">
            <h3 class="coordinator-selection-title">
                <?=Loc::getMessage($component->moduleId . "_COORDINATOR_TITLE")?>
            </h3>

            <div id="<?=$coordinatorCurrentWrapperId?>">
                <div id="<?=$coordinatorCurrentBlockId?>"></div>
                <?php if ($arResult['IS_COORDINATOR_EDITABLE']):?>
                    <button class="ui-btn ui-btn-primary" id="<?=$coordinatorEditBtnId?>">
                        <?=Loc::getMessage($component->moduleId . "_CHANGE_TEXT")?>
                    </button>
                <?php endif;?>
            </div>

            <?php if ($arResult['IS_COORDINATOR_EDITABLE']):?>
                <div id="<?=$coordinatorEditWrapperId?>">
                    <div id="<?=$coordinatorEditBlockId?>"></div>
                    <button class="ui-btn ui-btn-primary" id="<?=$coordinatorSubmitBtnId?>">
                        <?=Loc::getMessage($component->moduleId . "_SUBMIT_TEXT")?>
                    </button>
                </div>
            <?php endif;?>
        </div>

        <div class="ri-team-profile-info">
            <div id="<?=$teamDescWrapId?>">
                <h3 class="coordinator-selection-title">
                    <?=Loc::getMessage($component->moduleId . "_TEAM_DESCRIPTION_TITLE")?>
                </h3>

                <div id="<?=$teamDescCurrentWrapperId?>">
                    <div id="<?=$teamDescCurrentBlockId?>"><?=$arResult['TEAM_DESCRIPTION']?></div>
                    <?php if ($arResult['IS_TEAM_DESC_EDITABLE']):?>
                        <button class="ui-btn ui-btn-primary ui-btn-xs" id="<?=$teamDescEditBtnId?>">
                            <?=Loc::getMessage($component->moduleId . "_CHANGE_TEXT")?>
                        </button>
                    <?php endif;?>
                </div>

                <?php if ($arResult['IS_TEAM_DESC_EDITABLE']):?>
                    <div id="<?=$teamDescEditWrapperId?>">
                        <label class="ui-ctl ui-ctl-textarea ui-ctl-no-resize">
                            <textarea id="<?=$teamDescEditBlockId?>" class="ui-ctl-element">
                                <?=$arResult['TEAM_DESCRIPTION']?>
                            </textarea>
                        </label>
                        <button class="ui-btn ui-btn-primary ui-btn-xs" id="<?=$teamDescSubmitBtnId?>">
                            <?=Loc::getMessage($component->moduleId . "_SUBMIT_TEXT")?>
                        </button>
                    </div>
                <?php endif;?>
            </div>

            <div id="<?=$teamWorkTimeWrapId?>">
                <h3 class="coordinator-selection-title">
                    <?=Loc::getMessage($component->moduleId . "_TEAM_WORK_TIME_TITLE")?>
                </h3>

                <div id="<?=$teamWorkTimeCurrentWrapperId?>">
                    <div id="<?=$teamWorkTimeCurrentBlockId?>">
                        <?=$arResult['TEAM_WORK_TIME']['FROM']?> - <?=$arResult['TEAM_WORK_TIME']['TO']?>
                    </div>
                    <?php if ($arResult['IS_WORK_TIME_EDITABLE']):?>
                        <button class="ui-btn ui-btn-primary ui-btn-xs" id="<?=$teamWorkTimeEditBtnId?>">
                            <?=Loc::getMessage($component->moduleId . "_CHANGE_TEXT")?>
                        </button>
                    <?php endif;?>
                </div>

                <?php if ($arResult['IS_WORK_TIME_EDITABLE']):?>
                    <div id="<?=$teamWorkTimeEditWrapperId?>">
                        <div class="ri-team-profile-info-work-time-fields">
                            <label class="ui-ctl ui-ctl-textbox ri-team-profile-info-work-time-control-wrapper">
                                <input type="text" id="<?=$teamWorkTimeEditFrom?>" class="ui-ctl-element" value="<?=$arResult['TEAM_WORK_TIME']['FROM']?>" maxlength="5"/>
                            </label>

                            <label class="ui-ctl ui-ctl-textbox ri-team-profile-info-work-time-control-wrapper">
                                <input type="text" id="<?=$teamWorkTimeEditTo?>" class="ui-ctl-element" value="<?=$arResult['TEAM_WORK_TIME']['TO']?>" maxlength="5"/>
                            </label>
                        </div>

                        <button class="ui-btn ui-btn-primary ui-btn-xs" id="<?=$teamWorkTimeSubmitBtnId?>">
                            <?=Loc::getMessage($component->moduleId . "_SUBMIT_TEXT")?>
                        </button>
                    </div>
                <?php endif;?>
            </div>
        </div>
    </div>

    <div class="team-profile-grid-wrapper">
        <?php $APPLICATION->IncludeComponent(
            'bitrix:main.ui.grid',
            '.default',
            [
                'GRID_ID' => $arResult['GRID_ID'],
                'COLUMNS' => $arResult['COLUMNS'],
                'ROWS' => $arResult["ROWS"],
                'TOTAL_ROWS_COUNT' => $arResult['TOTAL_ROWS_COUNT'],
                'NAV_OBJECT' => $arResult["NAV"],
                'AJAX_MODE' => 'Y',
                'AJAX_ID' => CAjax::getComponentID('bitrix:main.ui.grid', '.default', ''),
                'PAGE_SIZES' => [
                    ['NAME' => "5", 'VALUE' => '5'],
                    ['NAME' => '10', 'VALUE' => '10'],
                    ['NAME' => '20', 'VALUE' => '20'],
                    ['NAME' => '50', 'VALUE' => '50'],
                    ['NAME' => '100', 'VALUE' => '100']
                ],
                'AJAX_OPTION_JUMP'          => 'N',
                'SHOW_CHECK_ALL_CHECKBOXES' => false,
                'SHOW_ROW_ACTIONS_MENU'     => false,
                'SHOW_ROW_CHECKBOXES'       => false,
                'SHOW_GRID_SETTINGS_MENU'   => true,
                'SHOW_NAVIGATION_PANEL'     => true,
                'SHOW_PAGINATION'           => true,
                'SHOW_SELECTED_COUNTER'     => true,
                'SHOW_TOTAL_COUNTER'        => true,
                'SHOW_PAGESIZE'             => true,
                'SHOW_ACTION_PANEL'         => false,
                'ALLOW_COLUMNS_SORT'        => true,
                'ALLOW_COLUMNS_RESIZE'      => true,
                'ALLOW_HORIZONTAL_SCROLL'   => true,
                'ALLOW_SORT'                => true,
                'ALLOW_PIN_HEADER'          => true,
                'AJAX_OPTION_HISTORY'       => 'N'
            ],
            $component,
            ["HIDE_ICONS" => "Y"]
        );?>
    </div>
</div>

<script>
    BX.ready(function (){
        BX.message({
            SELECT_TEXT: '<?=Loc::getMessage($component->moduleId . "_SELECT_TEXT")?>',
        });

        BX.Cbit.Mc.RI.TeamProfileComponent.init({
            componentName: '<?=$component->getName()?>',
            signedParameters: '<?=$component->getSignedParameters()?>',

            coordinatorSelectionWrapId: '<?=$coordinatorSelectionWrapId?>',
            currentCoordinator: <?=CUtil::PhpToJSObject($arResult['CURRENT_COORDINATOR'])?>,
            coordinatorEditBlockId: '<?=$coordinatorEditBlockId?>',
            coordinatorCurrentBlockId: '<?=$coordinatorCurrentBlockId?>',
            coordinatorEntityId: '<?=RIUserProvider::ENTITY_ID?>',
            coordinatorEntityType:'<?=EmployeeType::USER_TYPE_ID?>',
            coordinatorCurrentWrapperId: '<?=$coordinatorCurrentWrapperId?>',
            coordinatorEditWrapperId: '<?=$coordinatorEditWrapperId?>',
            coordinatorEditBtnId: '<?=$coordinatorEditBtnId?>',
            coordinatorSubmitBtnId: '<?=$coordinatorSubmitBtnId?>',

            teamDescription: '<?=$arResult["TEAM_DESCRIPTION"]?>',
            teamDescWrapId: '<?=$teamDescWrapId?>',
            teamDescCurrentWrapperId: '<?=$teamDescCurrentWrapperId?>',
            teamDescCurrentBlockId: '<?=$teamDescCurrentBlockId?>',
            teamDescEditBtnId: '<?=$teamDescEditBtnId?>',
            teamDescEditWrapperId: '<?=$teamDescEditWrapperId?>',
            teamDescEditBlockId: '<?=$teamDescEditBlockId?>',
            teamDescSubmitBtnId: '<?=$teamDescSubmitBtnId?>',

            teamWorkTime: '<?=$arResult["TEAM_WORK_TIME"]?>',
            teamWorkTimeWrapId: '<?=$teamWorkTimeWrapId?>',
            teamWorkTimeCurrentWrapperId: '<?=$teamWorkTimeCurrentWrapperId?>',
            teamWorkTimeCurrentBlockId: '<?=$teamWorkTimeCurrentBlockId?>',
            teamWorkTimeEditBtnId: '<?=$teamWorkTimeEditBtnId?>',
            teamWorkTimeEditWrapperId: '<?=$teamWorkTimeEditWrapperId?>',
            teamWorkTimeEditFrom: '<?=$teamWorkTimeEditFrom?>',
            teamWorkTimeEditTo: '<?=$teamWorkTimeEditTo?>',
            teamWorkTimeSubmitBtnId: '<?=$teamWorkTimeSubmitBtnId?>',
        });
    })
</script>
