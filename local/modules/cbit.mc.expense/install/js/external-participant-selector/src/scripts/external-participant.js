

export class ExternalParticipant
{
    constructor(options = {}) {
        this.moduleId           = options.moduleId;
        this.providerEntityId   = options.providerEntityId;
        this.providerEntityType = options.providerEntityType;

        this.submitAddFormBtnId = 'add-ext-participant-submit-btn';
    }

    showSelector(userFieldData = {}){
        const fieldName = userFieldData['FIELD_NAME'];
        const isMultiple = (userFieldData['MULTIPLE'] === 'Y');

        this.tagSelector = new BX.UI.EntitySelector.TagSelector({
            id: `${fieldName}_selector`,
            multiple: isMultiple,
            addButtonCaption: BX.message('SELECT_TEXT'),
            addButtonCaptionMore: BX.message('MORE_TEXT'),
            dialogOptions: {
                context: 'EXTERNAL_PARTICIPANT_SELECTOR_CONTEXT',
                entities: [
                    {
                        id           : this.providerEntityId,
                        options      : {},
                        dynamicLoad  : true,
                        dynamicSearch: true,
                    },
                ],
                hideOnSelect:          false,
                hideByEsc:             true,
                searchOptions: {
                    allowCreateItem: false,
                    footerOptions: {
                        label: ''
                    }
                },
                footer: BX.create('span', {
                    props: {
                        id: `${fieldName}_add`,
                        className: "ui-selector-footer-link ui-selector-footer-link-add"
                    },
                    text: BX.message('ADD_TEXT'),
                    events: {
                        click: () => this.showAddPopup(),
                    }
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
                        text: `${item['NAME']} ${item['SECOND_NAME']} ${item['LAST_NAME']}`,
                    },
                });
            }
        }

        this.tagSelector.renderTo(BX(userFieldData['PLACEMENT_ID']));
    }

    showAddPopup() {
        this.addFormNode  = this.createAddForm();
        this.addFormPopup = BX.PopupWindowManager.create(
            "add-ext-participant-popup",
            null,
            {
                content: this.addFormNode,
                width: 500,
                closeIcon: false,
                titleBar: BX.message('ADD_POPUP_TITLE'),
                closeByEsc: false,
                overlay: {
                    backgroundColor: 'black',
                    opacity: 500
                },
                buttons: [
                    new BX.PopupWindowButton({
                        text: BX.message('SAVE_TEXT'),
                        className: 'ui-btn ui-btn-primary',
                        id: this.submitAddFormBtnId,
                        events: {
                            click: () => {
                                this.submitAddForm();
                            }
                        }
                    }),
                    new BX.PopupWindowButton({
                        text: BX.message('EXIT_TEXT'),
                        className: 'ui-btn ui-btn-default',
                        events: {
                            click: () => {
                                this.addFormPopup.close();
                            }
                        }
                    })
                ],
            }
        );

        this.addFormPopup.show();
    }

    createAddForm() {
        return BX.create('form', {
            children: [
                this.createTextInput(BX.message('FIELD_NAME_LABEL'), `NAME`, true),
                this.createTextInput(BX.message('FIELD_SECOND_NAME_LABEL'), `SECOND_NAME`, false),
                this.createTextInput(BX.message('FIELD_LAST_NAME_LABEL'), `LAST_NAME`, true),
                this.createTextInput(BX.message('FIELD_COMPANY_LABEL'), `COMPANY`, true),
                this.createTextInput(BX.message('FIELD_POSITION_LABEL'), `POSITION`, true),
            ]
        });
    }

    submitAddForm() {
        if (!this.validateForm(this.addFormNode))
        {
            return false;
        }

        const submitBtn =  BX(this.submitAddFormBtnId);
        submitBtn && submitBtn.classList.add("ui-btn-wait");

        const formData = new FormData(this.addFormNode);

        BX.ajax.runAction('cbit.mc:expense.base.addExternalParticipant', {
            sessid: BX.bitrix_sessid(),
            data: formData
        }).then( response => {
            if (response.status === 'success')
            {
                this.addFormPopup.close();
                this.addFormPopup.destroy();

                if (response.data['ID'])
                {
                    const item = this.tagSelector.getDialog().addItem({
                        id: response.data['ID'],
                        entityId: this.providerEntityId,
                        title: `${response.data['NAME']} ${response.data['SECOND_NAME']} ${response.data['LAST_NAME']}`,
                        sort: 1
                    });

                    if (item)
                    {
                        item.select();
                    }
                }
            }
            else
            {
                throw new Error('Something went wrong. Unknown response status - '.response.status);
            }
        }).catch(response => {
            const error = response.errors?.[0]?.message ?? 'Something went wrong. Unknown error';
            BX.Cbit?.Mc?.Core?.MainUI?.showErrorPopup(error);
            submitBtn && submitBtn.classList.remove("ui-btn-wait");
        });
    }

    validateForm(form) {
        let result = true;

        const fields = form.querySelectorAll('input, select, textarea');
        fields.length && fields.forEach(field => {
            const parent = field.closest('.ui-ctl');
            if (field.hasAttribute('required') && !field.value)
            {
                parent && parent.classList.add('ui-ctl-danger');
                result = false;
            }
            else
            {
                parent && parent.classList.remove('ui-ctl-danger');
            }
        })

        return result;
    }

    createTextInput(labelText, name, required, placeholder = '',) {
        return BX.create({
            tag: 'label',
            props: {
                className: 'ui-ctl ui-ctl-textbox'
            },
            children: [
                BX.create({
                    tag: 'span',
                    props: {
                        className: 'ui-ctl-tag'
                    },
                    text: labelText
                }),
                BX.create({
                    tag: 'input',
                    props: {
                        type: "text",
                        name: name,
                        className: 'ui-ctl-element',
                    },
                    attrs: {
                        required: required,
                        placeholder: placeholder
                    }
                }),
            ]
        });
    }
}
