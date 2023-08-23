<?php

use Bitrix\Main\Localization\Loc;

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
?>
<h3 class="intranet-user-profile-tab-title">
    <?= Loc::getMessage('INTRANET_USER_PROFILE_EDU_TITLE')?>
</h3>

<?php if(!empty($arResult['User']['EDUCATION']['APPROVED'])):?>
<div class="intranet-user-profile-column-block">
    <p class="intranet-user-profile-tab-text intranet-user-profile-tab-block-title">
        <b><?=Loc::getMessage('INTRANET_USER_PROFILE_EDU_APPROVED_TITLE')?></b>
    </p>

    <div class="intranet-user-profile-edu-items">
        <?php foreach($arResult['User']['EDUCATION']['APPROVED'] as $item):?>
            <?php getEducationHtml($item);?>
        <?php endforeach;?>
    </div>
</div>
<?php endif;?>

<?php if(!empty($arResult['User']['EDUCATION']['NOT_APPROVED'])):?>
<div class="intranet-user-profile-column-block">
    <p class="intranet-user-profile-tab-text intranet-user-profile-tab-block-title">
        <b><?=Loc::getMessage('INTRANET_USER_PROFILE_EDU_NOT_APPROVED_TITLE')?></b>
    </p>

    <div class="intranet-user-profile-edu-items">
        <?php foreach($arResult['User']['EDUCATION']['NOT_APPROVED'] as $item):?>
            <?php getEducationHtml($item);?>
        <?php endforeach;?>
    </div>
</div>
<?php endif;?>

<?php if ($arResult["IsOwnProfile"]):?>
    <div class="intranet-user-profile-column-block">
        <p class="intranet-user-profile-tab-text intranet-user-profile-edu-add-text">
            <b><?=Loc::getMessage('INTRANET_USER_PROFILE_EDU_POPUP_TITLE')?></b>
        </p>

        <button class="ui-btn ui-btn-success" id="intranet-user-profile-edu-add-btn">
            <?=Loc::getMessage('INTRANET_USER_PROFILE_EDU_ADD')?>
        </button>
    </div>
<?php endif;?>


<?php
function getEducationHtml($item)
{
?>
    <div class="intranet-user-profile-edu-item">
        <p>
            <b style="font-size: 12px;">
                <?=$item['DATE_BEGIN_STUDYING']->format('d.m.Y')?> - <?=$item['DATE_END_STUDYING']->format('d.m.Y')?>
            </b>
            <b>&nbsp;&nbsp;&nbsp;&nbsp;<?=$item['EDUCATION_TYPE']?></b>
        </p>
        <p>
            <span><?=Loc::getMessage('INTRANET_USER_PROFILE_EDU_INSTITUTION_RU')?></span>
            <span>(<?=Loc::getMessage('INTRANET_USER_PROFILE_EDU_INSTITUTION_EN')?>):</span>
            <b><?=$item['INSTITUTION_RU']?> (<?=$item['INSTITUTION_EN']?>)</b>
        </p>
        <p>
            <span><?=Loc::getMessage('INTRANET_USER_PROFILE_EDU_SPECIALTY_RU')?></span>
            <span>(<?=Loc::getMessage('INTRANET_USER_PROFILE_EDU_SPECIALTY_EN')?>):</span>
            <b><?=$item['SPECIALTY_RU']?> (<?=$item['SPECIALTY_EN']?>)</b>
        </p>
        <p>
            <span><?=Loc::getMessage('INTRANET_USER_PROFILE_EDU_QUALIFICATION_RU')?></span>
            <span>(<?=Loc::getMessage('INTRANET_USER_PROFILE_EDU_QUALIFICATION_EN')?>):</span>
            <b><?=$item['QUALIFICATION_RU']?> (<?=$item['QUALIFICATION_EN']?>)</b>
        </p>
        <p>
            <b><?=($item['OUTSIDE_RUSSIA'] === 'Y' ? Loc::getMessage('INTRANET_USER_PROFILE_EDU_OUTSIDE_RUSSIA') : '')?></b>
        </p>
    </div>
<?php
}
