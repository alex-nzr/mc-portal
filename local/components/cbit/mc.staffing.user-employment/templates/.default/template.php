<?php use Bitrix\Main\Localization\Loc;

if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die();
/**
 * @var CMain $APPLICATION;
 * @var array $arResult;
 * @var array $arParams;
 * @var \Cbit\Mc\Staffing\Component\UserEmployment $component
 */
?>
<div id="user-employment-list-root">
    <h3 class="intranet-user-profile-tab-title">
        <?= Loc::getMessage($component->moduleId . '_USER_EMPLOYMENT_ACTIVE_PROJECTS')?>
    </h3>

    <?php if (empty($arResult['ITEMS'])):?>
        <p><?=Loc::getMessage($component->moduleId . '_USER_EMPLOYMENT_NO_ITEMS')?></p>
    <?php else:?>
        <table border="1">
            <thead>
                <tr>
                    <?if($arResult['HAS_STAFFING_PERMS']):?>
                        <th><?=Loc::getMessage($component->moduleId.'_USER_EMPLOYMENT_TABLE_PROJECT_CLIENT')?></th>
                    <?endif;?>
                    <th><?=Loc::getMessage($component->moduleId.'_USER_EMPLOYMENT_TABLE_PROJECT_DESC')?></th>
                    <th><?=Loc::getMessage($component->moduleId.'_USER_EMPLOYMENT_TABLE_PROJECT')?></th>

                    <th><?=Loc::getMessage($component->moduleId.'_USER_EMPLOYMENT_TABLE_ROLE')?></th>
                    <th><?=Loc::getMessage($component->moduleId.'_USER_EMPLOYMENT_TABLE_PROJECT_INDUSTRY')?></th>
                    <th><?=Loc::getMessage($component->moduleId.'_USER_EMPLOYMENT_TABLE_FROM')?></th>
                    <th><?=Loc::getMessage($component->moduleId.'_USER_EMPLOYMENT_TABLE_TO')?></th>
                    <th><?=Loc::getMessage($component->moduleId.'_USER_EMPLOYMENT_TABLE_WEEKS_IN_PROJECT')?></th>
                    <th><?=Loc::getMessage($component->moduleId.'_USER_EMPLOYMENT_TABLE_PROJECT_ED')?></th>
                </tr>
            </thead>
            <tbody>
            <?php foreach($arResult['ITEMS'] as $item):?>
                <tr class="user-employment-list-item">
                    <?if($arResult['HAS_STAFFING_PERMS']):?>
                        <td>
                            <span><?=$item['PROJECT_CLIENT']?></span>
                        </td>
                    <?endif;?>

                    <td>
                        <span><?=$item['PROJECT_DESCRIPTION']?></span>
                    </td>

                    <td>
                        <?if($arResult['HAS_STAFFING_PERMS']):?>
                            <a href="<?=$item['PROJECT_LINK']?>"><?=$item['PROJECT_TITLE']?></a>
                        <?else:?>
                            <span><?=$item['PROJECT_TITLE']?></span>
                        <?endif;?>
                    </td>

                    <td>
                        <span><?=$item['USER_ROLE']?></span>
                    </td>
                    <td>
                        <span><?=$item['PROJECT_INDUSTRY']?></span>
                    </td>
                    <td>
                        <span><?=$item['STAFFING_DATE_FROM']?></span>
                    </td>
                    <td>
                        <span><?=$item['STAFFING_DATE_TO']?></span>
                    </td>
                    <td>
                        <span><?=$item['WEEKS_ON_PROJECT']?></span>
                    </td>
                    <td>
                        <span><?=$item['PROJECT_ED']?></span>
                    </td>
                </tr>
            <?php endforeach;?>
            </tbody>
        </table>
    <?php endif;?>
</div>

<script>
    BX.ready(function (){
        BX.Cbit.Mc.Staffing.UserEmploymentComponent.init({
            componentName: '<?=$component->getName()?>'
        });
    })
</script>
