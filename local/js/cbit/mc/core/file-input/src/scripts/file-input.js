
export class FileInput
{
    constructor(options = {}) {

    }

    init(userFieldData = {})
    {
        this.allowedExt  = [];
        this.elementId    = userFieldData['ENTITY_VALUE_ID'];
        this.maxFileSize = this.getMaxFileSizeFromSettings(userFieldData);
        this.fieldId     = userFieldData['ID'];
        this.fieldName   = userFieldData['FIELD_NAME'];
        this.isMultiple  = (userFieldData['MULTIPLE'] === 'Y');
        this.container   = BX(userFieldData['CONTAINER_ID']);
        this.valuesBlock = BX(userFieldData['VALUES_BLOCK_ID']);
        this.fileInput   = BX(userFieldData['FILE_INPUT_ID']);

        this.fillAllowedExtensions(userFieldData['SETTINGS']);
        this.renderCurrentValue(userFieldData['VALUE_ITEMS']);

        this.initEvents();

        /*this.tagSelector = new BX.UI.EntitySelector.TagSelector({
            events: {
                onAfterTagAdd: (event) => {

                },
                onAfterTagRemove: function(event) {
                    const {tag} = event.getData();
                    const id = tag.id;

                    if (this.container)
                    {
                        const inputId = this.isMultiple ? `${this.fieldName}_add_${id}` : `${this.fieldName}_add`;
                        const input = BX(inputId);
                        input && (input.value = '');
                        BX.Crm?.EntityEditor?.getDefault()?.getControlById(this.fieldName)?.markAsChanged();
                    }
                },
            }
        });*/
    }

    renderCurrentValue(files) {
        if(typeof files === 'object')
        {
            for(let key in files)
            {
                this.appendFileToCurrentValue(files[key]);
            }
        }
    }

    fillAllowedExtensions(userFieldSettings) {
        if (typeof userFieldSettings?.['EXTENSIONS'] === 'object')
        {
            for(let key in userFieldSettings['EXTENSIONS'])
            {
                if (userFieldSettings['EXTENSIONS'][key] === true)
                {
                    this.allowedExt.push(key);
                }
            }
        }
    }

    initEvents() {
        this.fileInput && this.fileInput.addEventListener('change', () => this.onFileAdd());
    }

    onFileAdd(){
        if (this.checkFiles(this.fileInput.files))
        {
            const formData = new FormData();
            formData.append('USER_FIELD_ID', this.fieldId);
            formData.append('FILE', this.fileInput.files[0]);

            const label = this.fileInput.closest('label');
            label && label.classList.add('loading');

            BX.ajax.runAction('cbit.mc:core.base.uploadFile', {
                sessid: BX.bitrix_sessid(),
                data: formData
            }).then( response => {
                if (response.status === 'success')
                {
                    if (!response.data.ID)
                    {
                        throw new Error('File id not found in response');
                    }

                    this.appendFileToCurrentValue(response.data);

                    BX.Crm?.EntityEditor?.getDefault()?.getControlById(this.fieldName)?.markAsChanged();
                    //BX.Cbit?.Mc?.Core?.MainUI?.showSuccessPopup();
                    label && label.classList.remove('loading');
                    this.fileInput.value = '';
                }
                else
                {
                    throw new Error('Something went wrong. Unknown response status - '.response.status);
                }
            }).catch(response => {
                const error = response.errors?.[0]?.message ?? 'Something went wrong. Unknown error';
                BX.Cbit?.Mc?.Core?.MainUI?.showErrorPopup(error);
                label && label.classList.remove('loading');
            });
        }
    }

    onFileDelete(fileId){
        if(!Number(fileId) > 0)
        {
            BX.Cbit?.Mc?.Core?.MainUI?.showErrorPopup('File id can not be empty');
        }
        else
        {
            const inputId  = this.isMultiple ? `${this.fieldName}_add_${fileId}` : `${this.fieldName}_add`;
            let input = BX(inputId);
            if (input)
            {
                const wrapper = input.closest('.webform-field-item-wrap');
                wrapper ? wrapper.remove() : input.remove();
                BX.Crm?.EntityEditor?.getDefault()?.getControlById(this.fieldName)?.markAsChanged();
                this.fileInput.value = '';
            }
        }
    }

    checkFiles(files) {
        let res = true;

        for (let i = 0; i < files.length; i++)
        {
            if(files[i].size > this.maxFileSize){
                BX.Cbit.Mc.Core.MainUI.showErrorPopup(BX.message('MAX_FILE_SIZE_ERROR'));
                res = false;
            }

            const ext = this.getExtFromFileName(files[i].name);
            
            if(!this.allowedExt.includes(ext)){
                BX.Cbit.Mc.Core.MainUI.showErrorPopup(BX.message('FILE_EXT_ERROR'));
                res = false;
            }
        }

        return res;
    }

    getMaxFileSizeFromSettings(userFieldData) {
        let maxFileSize = 10*1024*1024;
        if(userFieldData['SETTINGS']?.['MAX_ALLOWED_SIZE'] && !isNaN(userFieldData['SETTINGS']['MAX_ALLOWED_SIZE']))
        {
            maxFileSize = Number(userFieldData['SETTINGS']['MAX_ALLOWED_SIZE']);
        }
        return maxFileSize;
    }

    getExtFromFileName(fileName) {
        fileName = String(fileName);
        let ext = '';
        const arr = fileName.split(".");
        if(arr.length > 1)
        {
            ext = arr[arr.length - 1];
        }
        return ext;
    }

    appendFileToCurrentValue(item) {
        const fractions = Number(item['FILE_SIZE']) >= 1024 ? 0 : 2;
        const fileSize = (Number(item['FILE_SIZE'])/1024).toFixed(fractions);
        const inputId  = this.isMultiple ? `${this.fieldName}_add_${item['ID']}` : `${this.fieldName}_add`;
        let input = BX(inputId);
        if (input)
        {
            input.value = item['ID'];
        }
        else
        {
            const html = BX.create('div', {
                props: {
                    className:"webform-field-item-wrap"
                },
                children: [
                    BX.create('a', {
                        props: {
                            className:"upload-file-name"
                        },
                        attrs: {
                            href: item['SRC'],
                            target: '_blank'
                        },
                        text: item['ORIGINAL_NAME']
                    }),
                    BX.create('span', {
                        props: {
                            className: 'upload-file-size'
                        },
                        text: `${fileSize}kb`
                    }),
                    BX.create('span', {
                        props: {
                            className: 'file-delete'
                        },
                        dataset:{
                            id: item['ID']
                        },
                        text: 'delete',
                        events: {
                            click: () => this.onFileDelete(item['ID'])
                        }
                    }),
                    BX.create('input', {
                        attrs: {
                            id: inputId,
                            type: 'hidden',
                            name: this.isMultiple ? `${this.fieldName}[]` : this.fieldName,
                        },
                        props: {
                            value: item['ID']
                        }
                    })
                ]
            });

            this.valuesBlock && this.valuesBlock.append(html);
        }
    }
}
