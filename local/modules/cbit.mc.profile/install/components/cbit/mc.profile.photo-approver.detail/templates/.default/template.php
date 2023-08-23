<?php use Bitrix\Main\Localization\Loc;

if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die();
/**
 * @var CMain $APPLICATION;
 * @var array $arResult;
 * @var array $arParams;
 * @var \Cbit\Mc\Profile\Component\PhotoApproverDetail $component
 */
$APPLICATION->SetTitle(Loc::getMessage($component->moduleId . '_PHOTO_APPROVER_DETAIL_PAGE_TITLE'));

?>

<h3 class="photo-approver-detail-page-subtitle"><?=Loc::getMessage($component->moduleId . '_PHOTO_APPROVER_USER_LINK_TEXT', [
        "#FULL_NAME#" => $arResult['USER_FULL_NAME'],
        "#LINK#"      => $arResult['USER_PROFILE_LINK']
    ])?></h3>

<div id="photo-approver-detail-root">
    <div class="photo-approver-detail-img-block">
        <h3><?=Loc::getMessage($component->moduleId."_PHOTO_APPROVER_OLD_PHOTO_TITLE")?></h3>
        <div>
            <?php
                if (!empty($arParams['OLD_FILE_ID']))
                {
                    $html = CFile::ShowImage($arParams['OLD_FILE_ID']);
                    echo !empty($html) ? $html : "<p>".Loc::getMessage($component->moduleId."_PHOTO_APPROVER_PHOTO_EMPTY")."</p>";
                }
                else
                {
                    echo "<p>".Loc::getMessage($component->moduleId."_PHOTO_APPROVER_PHOTO_EMPTY")."</p>";
                }
            ?>
        </div>
    </div>

    <div class="photo-approver-detail-img-block">
        <h3><?=Loc::getMessage($component->moduleId."_PHOTO_APPROVER_NEW_PHOTO_TITLE")?></h3>
        <div>
            <?php echo CFile::ShowImage($arParams['NEW_FILE_ID']); ?>
        </div>
    </div>
</div>

<div class="ui-btn-container ui-btn-container-center">
    <button class="ui-btn ui-btn-success" id="photo-approver-approve-btn">
        <?=Loc::getMessage($component->moduleId."_PHOTO_APPROVER_APPROVE_BTN_TEXT")?>
    </button>
    <button class="ui-btn ui-btn-danger" id="photo-approver-decline-btn">
        <?=Loc::getMessage($component->moduleId."_PHOTO_APPROVER_DECLINE_BTN_TEXT")?>
    </button>
</div>

<script>
    BX.ready(function (){
        BX.message({
            WRITE_REASON: '<?=Loc::getMessage($component->moduleId."_PHOTO_APPROVER_WRITE_REASON")?>',
            REASON_NOT_WROTE: '<?=Loc::getMessage($component->moduleId."_PHOTO_APPROVER_EMPTY_REASON")?>',
            OPERATION_SUCCESSFUL: '<?=Loc::getMessage($component->moduleId."_PHOTO_APPROVER_OPERATION_SUCCESS")?>',
        });

        BX.Cbit.Mc.Profile.PhotoApproveComponent.init(<?=CUtil::PhpToJSObject($arParams)?>);
    })
</script>
