

export class ChargeCodeSelector
{
    constructor(options = {}) {
        this.moduleId           = options.moduleId;
        this.providerEntityId   = options.providerEntityId;
        this.providerEntityType = options.providerEntityType;
    }

    showSelector(userFieldData = {}){
        const fieldName = userFieldData['FIELD_NAME'];
        const isMultiple = (userFieldData['MULTIPLE'] === 'Y');

        this.tagSelector = new BX.UI.EntitySelector.TagSelector({
            id: `${fieldName}_selector`,
            multiple: isMultiple,
            addButtonCaption: BX.message('SELECT_TEXT'),
            addButtonCaptionMore: isMultiple ? BX.message('MORE_TEXT') : BX.message('CHANGE_TEXT'),
            dialogOptions: {
                context: 'CHARGE_CODE_SELECTOR_CONTEXT',
                entities: [
                    {
                        id           : this.providerEntityId,
                        options      : {},
                        dynamicLoad  : true,
                        dynamicSearch: true,
                    },
                ],
                hideOnSelect:          !isMultiple,
                hideByEsc:             true,
                searchOptions: {
                    allowCreateItem: false,
                    footerOptions: {
                        label: ''
                    }
                },
                footer: BX.create('span', {
                    text: '',
                }),
                events: {}
            },
            events: {
                onAfterTagAdd: (event) => {
                    const {tag} = event.getData();
                    const id = tag.id;
                    const container = BX(userFieldData['CONTAINER_ID']);
                    if (container)
                    {
                        const inputId = isMultiple ? `${fieldName}_add_${id}` : `${fieldName}_add`;
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
                                    name: isMultiple ? `${fieldName}[]` : fieldName,
                                },
                            });
                            container.append(input);
                        }

                        BX.Crm?.EntityEditor?.getDefault()?.getControlById(fieldName)?.markAsChanged();
                    }
                },
                onAfterTagRemove: function(event) {
                    const {tag} = event.getData();
                    const id = tag.id;
                    const container = BX(userFieldData['CONTAINER_ID']);
                    if (container)
                    {
                        const inputId = isMultiple ? `${fieldName}_add_${id}` : `${fieldName}_add`;
                        const input = BX(inputId);
                        input && (input.value = '');
                        BX.Crm?.EntityEditor?.getDefault()?.getControlById(fieldName)?.markAsChanged();
                    }
                },
            }
        });

        if(typeof userFieldData['VALUE_ITEMS'] === 'object')
        {
            for(let key in userFieldData['VALUE_ITEMS'])
            {
                const item = userFieldData['VALUE_ITEMS'][key];
                this.tagSelector.addTag({
                    id: item['ID'],
                    entityId: this.providerEntityId,
                    entityType: this.providerEntityType,
                    title: {
                        text: `${item['TITLE']}`,
                    },
                });
            }
        }

        this.tagSelector.renderTo(BX(userFieldData['PLACEMENT_ID']));
    }
}
