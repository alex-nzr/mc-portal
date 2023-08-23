<?php if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die();

use Bitrix\Main\Localization\Loc;
use Bitrix\Main\Type\Date;

/**
 * @var CMain $APPLICATION;
 * @var array $arResult;
 * @var array $arParams;
 * @var \Cbit\Mc\Staffing\Component\UserReport $component
 */

$className = $arResult['HAS_STAFFING_PERMS'] ? '' : 'internal-staff-report';

?>
<div id="user-report-root" class="<?=$className?>">
    <div class="user-report-block">
        <p>
            <?= Loc::getMessage($component->moduleId.'_USER_REPORT_PROJECTS_GENERAL_NETWORK')?>
            <b><?=$arResult["GENERAL_NETWORK"]?></b>
        </p>
    </div>

    <?php if (!empty($arResult['INDUSTRIES_DIAGRAM'])):?>
        <div class="user-report-block">
            <p>
                <b><?=Loc::getMessage($component->moduleId.'_USER_REPORT_PROJECTS_CORE_INDUSTRIES')?></b>
            </p>
            <div class="user-report-block-diagram">
                <div class="user-report-block-diagram-wrapper">
                    <?php foreach ($arResult['INDUSTRIES_DIAGRAM'] as $item):?>
                        <div class="user-report-block-diagram-segment"
                             style="width:<?=$item['PART']?>;background-color: <?=$item['COLOR']?>;">
                            <span><?=$item['PART']?></span>
                        </div>
                    <?php endforeach;?>
                </div>
                <div class="user-report-block-diagram-legend">
                    <?php foreach ($arResult['INDUSTRIES_DIAGRAM'] as $item):?>
                        <div class="user-report-block-diagram-legend-item">
                            <span style="background-color: <?=$item['COLOR']?>;"></span>
                            <p><?=$item['NAME']?></p>
                        </div>
                    <?php endforeach;?>
                </div>
            </div>
        </div>
    <?php endif;?>

    <?php if (!empty($arResult['FUNCTIONS_DIAGRAM'])):?>
        <div class="user-report-block">
            <p>
                <b><?=Loc::getMessage($component->moduleId.'_USER_REPORT_PROJECTS_CORE_FUNCTIONS')?></b>
            </p>
            <div class="user-report-block-diagram">
                <div class="user-report-block-diagram-wrapper">
                    <?php foreach ($arResult['FUNCTIONS_DIAGRAM'] as $item):?>
                        <div class="user-report-block-diagram-segment"
                             style="width:<?=$item['PART']?>;background-color: <?=$item['COLOR']?>;">
                            <span><?=$item['PART']?></span>
                        </div>
                    <?php endforeach;?>
                </div>
                <div class="user-report-block-diagram-legend">
                    <?php foreach ($arResult['FUNCTIONS_DIAGRAM'] as $item):?>
                        <div class="user-report-block-diagram-legend-item">
                            <span style="background-color: <?=$item['COLOR']?>;"></span>
                            <p><?=$item['NAME']?></p>
                        </div>
                    <?php endforeach;?>
                </div>
            </div>
        </div>
    <?php endif;?>

    <?php if (!empty($arResult['PROJECTS'])):?>
        <div class="user-report-block">
            <h3><?=Loc::getMessage($component->moduleId.'_USER_REPORT_ALL_PROJECTS')?></h3>
            <table class="user-report-block-table" border="1">
                <thead>
                <tr>
                    <th class="staffing-only">
                        <?=Loc::getMessage($component->moduleId.'_USER_REPORT_PROJECTS_TABLE_TH_CLIENT')?>
                    </th>
                    <th class="staffing-only">
                        <?=Loc::getMessage($component->moduleId.'_USER_REPORT_PROJECTS_TABLE_TH_TOPIC')?>
                    </th>
                    <th><?=Loc::getMessage($component->moduleId.'_USER_REPORT_PROJECTS_TABLE_TH_CC')?></th>
                    <th><?=Loc::getMessage($component->moduleId.'_USER_REPORT_PROJECTS_TABLE_TH_ROLE')?></th>
                    <th><?=Loc::getMessage($component->moduleId.'_USER_REPORT_PROJECTS_TABLE_TH_INDUSTRY')?></th>
                    <th><?=Loc::getMessage($component->moduleId.'_USER_REPORT_PROJECTS_TABLE_TH_START')?></th>
                    <th><?=Loc::getMessage($component->moduleId.'_USER_REPORT_PROJECTS_TABLE_TH_END')?></th>
                    <th><?=Loc::getMessage($component->moduleId.'_USER_REPORT_PROJECTS_TABLE_TH_EXPERIENCE')?></th>
                    <th><?=Loc::getMessage($component->moduleId.'_USER_REPORT_PROJECTS_TABLE_TH_ED')?></th>
                </tr>
                </thead>
                <tbody id="user-report-block-project-table-body">
                <?php foreach($arResult['PROJECTS'] as $project):?>
                    <tr>
                        <td class="staffing-only"><?=$project['PROJECT_CLIENT']?></td>
                        <td class="staffing-only"><?=$project['PROJECT_DESCRIPTION']?></td>
                        <td><?=$project['PROJECT_NAME']?></td>
                        <td><?=$project['USER_ROLE']?></td>
                        <td><?=$project['INDUSTRY_NAME']?></td>
                        <td><?=$project['WORK_DATE_START']?></td>
                        <td><?=$project['WORK_DATE_FINISH']?></td>
                        <td><?=$project['WEEKS_IN_PROJECT']?></td>
                        <td><?=$project['PROJECT_ED']?></td>
                    </tr>
                <?php endforeach;?>
                </tbody>
            </table>

            <button class="ui-btn ui-btn-primary" id="user-report-block-projects-more">
                <?=Loc::getMessage($component->moduleId.'_USER_REPORT_PROJECTS_SHOW_MORE')?>
            </button>
        </div>
    <?php endif;?>

    <?php if (!empty($arResult['ABSENCES'])):?>
        <div class="user-report-block">
            <h3><?=Loc::getMessage($component->moduleId.'_USER_REPORT_ABSENCES')?></h3>
            <table class="user-report-block-table" border="1">
                <thead>
                <tr>
                    <!--<th>№</th>-->
                    <!--<th><?php /*=Loc::getMessage($component->moduleId.'_USER_REPORT_ABSENCES_TABLE_TH_NAME')*/?></th>-->
                    <th><?=Loc::getMessage($component->moduleId.'_USER_REPORT_ABSENCES_TABLE_TH_TYPE')?></th>
                    <th><?=Loc::getMessage($component->moduleId.'_USER_REPORT_ABSENCES_TABLE_TH_FROM')?></th>
                    <th><?=Loc::getMessage($component->moduleId.'_USER_REPORT_ABSENCES_TABLE_TH_TO')?></th>
                </tr>
                </thead>
                <tbody id="user-report-block-project-table-body">
                <?php foreach($arResult['ABSENCES'] as $key => $value):?>
                    <tr>
                        <!--<td><?php /*=($key+1)*/?></td>-->
                        <!--<td><?php /*=$value['NAME']*/?></td>-->
                        <td><?=$value['PROPERTY_ABSENCE_TYPE_VALUE']?></td>
                        <td><?=$value['DATE_FROM']?></td>
                        <td><?=$value['DATE_TO']?></td>
                    </tr>
                <?php endforeach;?>
                </tbody>
            </table>
        </div>
    <?php endif;?>
</div>

<script>
    BX.ready(function (){
        BX.Cbit.Mc.Staffing.UserReportComponent.init({
            componentName: '<?=$component->getName()?>',
            userId: '<?=$arParams["USER_ID"]?>',
            signedParameters: '<?=$this->getComponent()->getSignedParameters()?>',
        });
    })
</script>
