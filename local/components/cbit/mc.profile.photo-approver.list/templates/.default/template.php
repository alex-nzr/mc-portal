<?php use Bitrix\Main\Localization\Loc;

if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die();
/**
 * @var CMain $APPLICATION;
 * @var array $arResult;
 * @var array $arParams;
 * @var \Cbit\Mc\Profile\Component\PhotoApproverDetail $component
 */
$APPLICATION->SetTitle(Loc::getMessage($component->moduleId . '_PHOTO_APPROVER_LIST_PAGE_TITLE'));

?>
<div id="photo-approver-list-root">
    <?php if (empty($arResult['ITEMS'])):?>
        <p><?=Loc::getMessage($component->moduleId . '_PHOTO_APPROVER_LIST_NO_ITEMS')?></p>
    <?php else:?>
        <?php foreach($arResult['ITEMS'] as $item):?>
            <div class="photo-approver-list-item">
                <p>
                    <b><?=$item['NUMBER']?>.</b>
                    <a href="<?=$item['USER_PROFILE_LINK']?>"><?=$item['USER_FULL_NAME']?>.</a>
                    &nbsp;
                    <a href="<?=$item['DETAIL_PAGE']?>"><?=Loc::getMessage($component->moduleId . '_PHOTO_APPROVER_LIST_PAGE_DETAIL_LINK')?></a>
                </p>
            </div>
        <?php endforeach;?>
    <?php endif;?>
</div>
<?
$APPLICATION->IncludeComponent(
    "bitrix:main.pagenavigation",
    "grid",
    array(
        "NAV_OBJECT" => $arResult['NAV_OBJECT'],
        "SEF_MODE" => "N",
    ),
    false
);
?>
<script>
    BX.ready(function (){
        BX.Cbit.Mc.Profile.PhotoApproveListComponent.init({
            backUrl: '<?=$APPLICATION->GetCurPage()?>'
        });
    })
</script>
