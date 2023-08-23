<?php
/**
 * @var array $arResult
 * @var array $arParams
 * @var string $templateFolder
 * @var \CMain $APPLICATION
 */
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) die();

use Bitrix\Crm\Service\Container;
use Bitrix\Main\Localization\Loc;
use Bitrix\Main\Page\Asset;
use Bitrix\Main\UI\Extension;
use Bitrix\Main\Web\Json;
use Cbit\Mc\Expense\Entity\Dynamic;

try
{
    $typeId = Dynamic::getInstance()->getTypeId();

    Extension::load(
        [
            'ui.dialogs.messagebox',
            'crm_common',
        ]
    );

    Asset::getInstance()->addJs('/bitrix/js/crm/common.js');
    Asset::getInstance()->addJs('/bitrix/js/crm/progress_control.js');
    Asset::getInstance()->addJs($templateFolder.'/rating.js');

    $bodyClass = $APPLICATION->GetPageProperty("BodyClass");
    $APPLICATION->SetPageProperty("BodyClass", ($bodyClass ? $bodyClass." " : "") . "no-all-paddings no-hidden no-background");
    if($this->getComponent()->getErrors())
    {
        foreach($this->getComponent()->getErrors() as $error)
        {
            /** @var \Bitrix\Main\Error $error */
            ?>
            <div class="ui-alert ui-alert-danger">
                <span class="ui-alert-message"><?=$error->getMessage();?></span>
            </div>
            <?php
        }

        return;
    }
    echo CCrmViewHelper::RenderItemStatusSettings($arParams['entityTypeId'], $arParams['categoryId']);
    /** @see \Bitrix\Crm\Component\Base::addTopPanel() */
    $this->getComponent()->addTopPanel($this);

    /** @see \Bitrix\Crm\Component\Base::addToolbar() */
    $this->getComponent()->addToolbar($this);
    ?>

    <div class="ui-alert ui-alert-danger" style="display: none;">
        <span class="ui-alert-message" id="crm-type-item-list-error-text-container"></span>
        <span class="ui-alert-close-btn" onclick="this.parentNode.style.display = 'none';"></span>
    </div>

    <?php if (array_key_exists('HAS_PERMS_TO_ADD_TYB', $arResult) && $arResult['HAS_PERMS_TO_ADD_TYB'] === false):?>
    <div class="ui-alert">
        <span class="ui-alert-message">
            <?=Loc::getMessage('NO_PERMS_TO_TYB');?>
        </span>
    </div>
    <?php endif;?>

    <div class="crm-type-item-list-wrapper" id="crm-type-item-list-wrapper">
        <div class="crm-type-item-list-container<?php
        if($arResult['grid'])
        {
            echo ' crm-type-item-list-grid';
        }
        ?>" id="crm-type-item-list-container">
            <?php
            if ($arResult['grid'])
            {
                if (!empty($arResult['interfaceToolbar']))
                {
                    $APPLICATION->IncludeComponent(
                        'bitrix:crm.interface.toolbar',
                        '',
                        [
                            'TOOLBAR_ID' => $arResult['interfaceToolbar']['id'],
                            'BUTTONS' => $arResult['interfaceToolbar']['buttons'],
                        ]
                    );
                }

                $APPLICATION->IncludeComponent(
                    "bitrix:main.ui.grid",
                    "",
                    $arResult['grid']
                );
            }
            ?>
        </div>
    </div>

    <?php
    $messages = array_merge(Container::getInstance()->getLocalization()->loadMessages(), Loc::loadLanguageFile(__FILE__));
    ?>

    <script>
        BX.ready(function() {
            BX.message(<?=Json::encode($messages)?>);
            const params = <?=CUtil::PhpToJSObject($arResult['jsParams'], false, false, true);?>;
            params.errorTextContainer = document.getElementById('crm-type-item-list-error-text-container');
            (new BX.Crm.ItemListComponent(params)).init();

            <?php if (isset($arResult['RESTRICTED_VALUE_CLICK_CALLBACK'])):?>
            BX.addCustomEvent(window, 'onCrmRestrictedValueClick', function() {
                <?=$arResult['RESTRICTED_VALUE_CLICK_CALLBACK'];?>
            });
            <?php endif;?>
        });
    </script>

    <script>
        BX.ready(function() {
            checkRowDuplicates();
            BX.Event.EventEmitter.subscribe('Grid::ready', () => checkRowDuplicates());
            BX.Event.EventEmitter.subscribe('Grid::updated', () => checkRowDuplicates());
        });

        function checkRowDuplicates()
        {
            const gridNode = BX('<?=$arResult['grid']['GRID_ID']?>');
            if (gridNode)
            {
                const gridHeadCells = gridNode.querySelectorAll('.main-grid-header .main-grid-cell-head');
                let needleIndex = null;
                gridHeadCells.length && gridHeadCells.forEach((cell, index) => {
                    if (cell.dataset.name === 'UF_CRM_<?=$typeId?>_DUPLICATE_OF')
                    {
                        needleIndex = index;
                    }
                });

                if (needleIndex !== null)
                {
                    const rows = gridNode.querySelectorAll('.main-grid-row.main-grid-row-body');
                    rows.length && rows.forEach(row => {
                        const cells = row.querySelectorAll('.main-grid-cell');
                        const needleCell = cells[needleIndex];
                        if (needleCell)
                        {
                            const notEmpty = needleCell.querySelector('.main-grid-cell-content a');
                            if (notEmpty)
                            {
                                row.classList.add('main-grid-row-duplicate');
                            }
                        }
                    });
                }
            }
        }
    </script>
<?php
}
catch (Throwable $e)
{
    ShowError($e->getMessage());
}