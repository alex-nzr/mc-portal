<?php
/**
 * @var array $arResult
 */
if(!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) die();

use Bitrix\Intranet\UserField\Types\EmployeeType;
use Bitrix\Main\Localization\Loc;
use Bitrix\Main\Text\HtmlFilter;
use Cbit\Mc\Core\Component\CbitRIUserUfComponent;
use Cbit\Mc\Core\Service\Integration\UI\EntitySelector\RIUserProvider;

$component = $this->getComponent();
$selectorName = $arResult['selectorName'];
$fieldName = HtmlFilter::encode($arResult['fieldName']);

if($arResult['userField']['EDIT_IN_LIST'] === 'Y')
{
    ?>

    <div id="cont_<?= $selectorName ?>" data-has-input="no">
        <div id="<?=$fieldName?>"></div>
    </div>

    <script>
        BX.ready(function ()
        {
            const isMultiple = <?=($arResult['isMultiple'] ? 'true' : 'false')?>;
            const tagSelector = new BX.UI.EntitySelector.TagSelector({
                id: '<?=$fieldName?>',
                multiple: isMultiple,
                addButtonCaption: '<?=Loc::getMessage("EMPLOYEE_FIELD_SELECT")?>',
                addButtonCaptionMore: '<?=Loc::getMessage("EMPLOYEE_FIELD_SELECT")?>',
                dialogOptions: {
                    context: '<?=CbitRIUserUfComponent::SELECTOR_CONTEXT?>',
                    /*tabs: [
                        {
                            id: '<?=RIUserProvider::OFFICE_TAB_ID?>',
                            title: '<?=RIUserProvider::OFFICE_TAB_ID?>',
                            itemOrder: {title: 'asc'}
                        },
                        {
                            id: '<?=RIUserProvider::OUTSOURCE_TAB_ID?>',
                            title: '<?=RIUserProvider::OUTSOURCE_TAB_ID?>',
                            itemOrder: {title: 'asc'}
                        },
                    ],*/
                    entities: [
                        {
                            id           : '<?=RIUserProvider::ENTITY_ID?>',
                            options      : {},
                            dynamicLoad  : true,
                            dynamicSearch: true,
                        },
                    ],
                    hideOnSelect:          true,
                    hideByEsc:             true,
                    events: {
                        onSearch: (e) => {
                            const dialog = e.getTarget();
                            const footer = dialog.getFooter();
                            footer && footer.hide();
                        },
                        onShow: (e) => {
                            const dialog = e.getTarget();
                            const footer = dialog.getFooter();
                            footer && footer.hide();
                        },
                        onLoad: (e) => {
                            const dialog = e.getTarget();
                            const footer = dialog.getFooter();
                            footer && footer.hide();
                        }
                    }
                },
                events: {
                    onAfterTagAdd: (event) => {
                        const {tag} = event.getData();
                        const id = tag.id;
                        const container = BX('cont_<?= $selectorName ?>');
                        if (container)
                        {
                            const inputId = isMultiple ? `<?=$fieldName?>_add_${id}` : `<?=$fieldName?>_add`;
                            let input = BX(inputId);
                            if (input)
                            {
                                input.value = id;
                            }
                            else
                            {
                                input = BX.create('input', {
                                    props: {
                                        id: inputId,
                                        value: id,
                                        type: "hidden",
                                        name: isMultiple ? `<?=$fieldName?>[]` : '<?=$fieldName?>',
                                    },
                                });
                                container.append(input);
                            }

                            BX.Crm?.EntityEditor?.getDefault()?.getControlById('<?=$fieldName?>')?.markAsChanged();
                        }
                    },
                    onAfterTagRemove: function(event) {
                        const {tag} = event.getData();
                        const id = tag.id;
                        const container = BX('cont_<?= $selectorName ?>');
                        if (container)
                        {
                            const inputId = isMultiple ? `<?=$fieldName?>_add_${id}` : `<?=$fieldName?>_add`;
                            const input = BX(inputId);
                            input && (input.value = '');
                            BX.Crm?.EntityEditor?.getDefault()?.getControlById('<?=$fieldName?>')?.markAsChanged();
                        }
                    },
                }
            });

            <?php if (is_array($arResult['value']) && !empty($arResult['value'])):?>
                <?php foreach ($arResult['value'] as $item):?>
                    tagSelector.addTag({
                        id: '<?=$item['userId']?>',
                        entityId: "<?=RIUserProvider::ENTITY_ID?>",
                        entityType: "<?=EmployeeType::USER_TYPE_ID?>",
                        link: '<?=$item['href']?>',
                        avatar: '<?=$item['personalPhoto']?>',
                        title: {
                            text: '<?=$item['name']?>',
                        },
                    });
                <?php endforeach;?>
            <?php endif;?>

            tagSelector.renderTo(BX('<?=$fieldName?>'));
        });
    </script>

    <?php
}
elseif($arResult['value'])
{
    foreach($arResult['value'] as $item)
    {
        $style = null;
        if($item['personalPhoto'])
        {
            $style = 'style="background-image:url(\'' . htmlspecialcharsbx($item['personalPhoto']) . '\'); background-size: 30px;"';
        }
        ?>
        <span class="fields employee field-item" data-has-input="no">
			<a
                    class="uf-employee-wrap"
                    href="<?= $item['href'] ?>"
                    target="_blank"
            >
				<span
                        class="uf-employee-image"
					<?= ($style ?? '') ?>
				>
				</span>
				<span class="uf-employee-data">
					<span class="uf-employee-name">
						<?= $item['name'] ?>
					</span>
					<span class="uf-employee-position">
						<?= $item['workPosition'] ?>
					</span>
				</span>
			</a>
		</span>
        <?php
    }
}
else
{
    ?>
    <span class="fields employee field-wrap" data-has-input="no">
	<?php
    if(is_array($arResult['value']))
    {
        foreach($arResult['value'] as $item)
        {
            $style = null;
            if($item['personalPhoto'])
            {
                $style = 'style="background-image:url(' . $item['personalPhoto'] . '); background-size: 30px;"';
            }
            ?>
            <span class="fields employee field-item">
				<a
                        class="uf-employee-wrap"
                        href="<?= $item['href'] ?>"
                        target="_blank"
                >
					<span
                            class="uf-employee-image"
						<?= ($style ?? '') ?>
					>
					</span>
					<span class="uf-employee-data">
						<span class="uf-employee-name">
							<?= $item['name'] ?>
						</span>
						<span class="uf-employee-position">
							<?= $item['workPosition'] ?>
						</span>
					</span>
				</a>
			</span>
            <?php
        }
    }
    else
    {
        print Loc::getMessage('EMPLOYEE_FIELD_EMPTY');
    }
    ?>
	</span>
    <?php
}
