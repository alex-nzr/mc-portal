BX.ready(function (){
    BX.namespace("Cbit.Mc.Staffing.BindingComponent");

    BX.Cbit.Mc.Staffing.BindingComponent = {
        init(params = {}) {
            this.componentName                  = params.componentName;
            this.templateFolder                 = params.templateFolder;
            this.staffingUserRoles              = params.staffingUserRoles;
            this.staffingEmploymentTypes        = params.staffingEmploymentTypes;
            this.staffingEmploymentTypeStaffed  = params.staffingEmploymentTypeStaffed;
            this.staffingEmploymentTypeBeach    = params.staffingEmploymentTypeBeach;
            this.projectTypeFieldCode           = params.projectTypeFieldCode;
            this.perDiemEditReasons             = params.perDiemEditReasons;

            this.maxPerDiemEditRowsCount     = 10;
            this.currentPerDiemEditRowsCount = 0;

            this.typeSelectId = this.typeSelectName = 'USER_EMPLOYMENT_TYPE';

            this.bindingFormSubmitBtnId = 'binding-form-submit-button';

            this.basicPerDiemFormValueNodeId            = 'basic-per-diem-value';
            this.basicPerDiemEditBtnId                  = 'per-diem-edit-btn';
            this.perDiemEditWrapperId                   = 'per-diem-edit-wrapper';
            this.perDiemEditTableId                     = 'per-diem-edit-table';
            this.perDiemEditRowPdoValueClassName        = 'staffing-popup-per-diem-edit-pdo-value';
            this.perDiemEditRowDateFromValueClassName   = 'staffing-popup-per-diem-edit-from-value';
            this.perDiemEditRowDateToValueClassName     = 'staffing-popup-per-diem-edit-to-value';
            this.perDiemEditRowReasonValueClassName     = 'staffing-popup-per-diem-edit-reason-value';

            this.usersLimitCount    = 1;
            this.usersFilterId      = params.usersFilterId;
            //this.usersFilter        = {};
            this.userDisplayFields = params.userDisplayFields;

            this.projectsLimitCount    = 1;
            this.projectsFilterId      = params.projectsFilterId;
            //this.projectsFilter        = {};
            this.projectDisplayFields  = params.projectDisplayFields;

            this.initFilters();
            this.initUsersMoreBtn();
            this.initProjectsMoreBtn();
            this.initBasicEvents();

            this.getUsers();
            this.getProjects();

            this.initExportButtons();
        },

        initFilters(){
            //const usersFilter = BX.Main.filterManager.getById(this.usersFilterId);
            //this.usersFilter  = usersFilter ? usersFilter.getFilterFieldsValues() : {};

            //const projectsFilter = BX.Main.filterManager.getById(this.projectsFilterId);
            //this.projectsFilter  = projectsFilter ? projectsFilter.getFilterFieldsValues() : {};
        },

        initUsersMoreBtn() {
            this.usersMoreBtn = BX('staffing-binder-users-more-btn');
            this.usersCountText = BX('staffing-binder-users-count-text');
            this.usersMoreBtn.addEventListener('click', () => {
                this.usersLimitCount++;
                this.getUsers();
            });
        },

        initProjectsMoreBtn() {
            this.projectsMoreBtn = BX('staffing-binder-projects-more-btn');
            this.projectsCountText = BX('staffing-binder-projects-count-text');
            this.projectsMoreBtn.addEventListener('click', () => {
                this.projectsLimitCount++;
                this.getProjects();
            });
        },

        initBasicEvents() {
            BX.addCustomEvent('BX.Main.Filter:apply', (filterId) => {
                //let filter = BX.Main.filterManager.getById(filterId);
                //let values = filter.getFilterFieldsValues();

                if (filterId === this.usersFilterId)
                {
                    this.usersMoreBtn.classList.remove("ui-btn-wait");
                    this.usersMoreBtn.removeAttribute('disabled');

                    this.usersLimitCount = 1;
                    //this.usersFilter = values;
                    this.getUsers();
                }

                if (filterId === this.projectsFilterId)
                {
                    this.projectsMoreBtn.classList.remove("ui-btn-wait");
                    this.projectsMoreBtn.removeAttribute('disabled');

                    this.projectsLimitCount = 1;
                    //this.projectsFilter = values;
                    this.getProjects();
                }
            });
        },

        getUsers() {
            this.usersMoreBtn.classList.add("ui-btn-wait");

            BX.ajax.runComponentAction(this.componentName, "getFilteredUsers", {
                mode: 'ajax',
                data: {
                    limitCount: this.usersLimitCount,
                }
            }).then(response => {
                if (response.status === 'success')
                {
                    const tableBody = BX(this.usersFilterId);
                    tableBody.innerHTML = '';
                    if (typeof response?.data?.users === 'object')
                    {
                        for (const id in response.data.users)
                        {
                            if (tableBody && response.data.users.hasOwnProperty(id))
                            {
                                const row = BX.create({
                                    tag: 'tr',
                                    attrs: {
                                        className: 'staffing-binder-list-item',
                                        draggable: 'true',
                                    },
                                    dataset: {
                                        item: JSON.stringify(response.data.users[id])
                                    },
                                    children: this.userDisplayFields.map(
                                        (field) => {
                                            return BX.create({
                                                tag:'td',
                                                html: response.data.users[id][field]
                                            })
                                        }
                                    ),
                                });
                                this.configureUserRowEvents(row);
                                tableBody.append(row);
                            }
                        }
                    }

                    if(response?.data?.count >= response?.data?.total)
                    {
                        this.usersMoreBtn.setAttribute('disabled', 'true');
                    }
                    this.usersMoreBtn.classList.remove("ui-btn-wait");

                    this.usersCountText.textContent = `Shown ${response?.data?.count} of ${response?.data?.total}`
                }
                else
                {
                    throw new Error('Something went wrong. Unknown response status - '.response.status);
                }
            }).catch(response => {
                const error = response.errors?.[0]?.message ?? 'Something went wrong in getUsers. Unknown error';
                BX.Cbit?.Mc?.Core?.MainUI?.showErrorPopup(error);
                this.usersMoreBtn.classList.remove("ui-btn-wait");
            });
        },

        getProjects() {
            this.projectsMoreBtn.classList.add("ui-btn-wait");

            BX.ajax.runComponentAction(this.componentName, "getFilteredProjects", {
                mode: 'ajax',
                data: {
                    limitCount: this.projectsLimitCount,
                }
            }).then(response => {
                if (response.status === 'success')
                {
                    const tableBody = BX(this.projectsFilterId);
                    tableBody.innerHTML = '';
                    if (typeof response?.data?.projects === 'object')
                    {
                        for (const id in response.data.projects)
                        {
                            if (tableBody && response.data.projects.hasOwnProperty(id))
                            {
                                const row = BX.create({
                                    tag: 'tr',
                                    attrs: {
                                        className: 'staffing-binder-list-item',
                                    },
                                    dataset: {
                                        item: JSON.stringify(response.data.projects[id])
                                    },
                                    children: this.projectDisplayFields.map(
                                        (field) => {
                                            return BX.create({
                                                tag:'td',
                                                html: response.data.projects[id][field]
                                            })
                                        }
                                    ),
                                });
                                this.configureProjectRowEvents(row);
                                tableBody.append(row);
                            }
                        }
                    }

                    if(response?.data?.count >= response?.data?.total)
                    {
                        this.projectsMoreBtn.setAttribute('disabled', 'true');
                    }
                    this.projectsMoreBtn.classList.remove("ui-btn-wait");

                    this.projectsCountText.textContent = `Shown ${response?.data?.count} of ${response?.data?.total}`
                }
                else
                {
                    throw new Error('Something went wrong. Unknown response status - '.response.status);
                }
            }).catch(response => {
                const error = response.errors?.[0]?.message ?? 'Something went wrong in getProjects. Unknown error';
                BX.Cbit?.Mc?.Core?.MainUI?.showErrorPopup(error);
                this.usersMoreBtn.classList.remove("ui-btn-wait");
            });
        },

        configureProjectRowEvents(row) {
            const activateEvents = ['dragenter', 'dragover'];
            activateEvents.forEach(eventName => {
                row.addEventListener(eventName, (e) => {
                    e.preventDefault();
                    row.classList.add('dragenter');
                });
            });

            const deactivateEvents = ['dragleave', 'drop', 'dragend', 'dragexit'];
            deactivateEvents.forEach(eventName => {
                row.addEventListener(eventName, (e) => {
                    e.preventDefault();
                    row.classList.remove('dragenter');

                    switch (eventName)
                    {
                        case 'drop':
                            const userData = JSON.parse(e.dataTransfer.getData("userData"));
                            const projectData = JSON.parse(row.dataset.item);
                            this.showBindingPopup(userData, projectData);
                            break;
                    }
                });
            });
        },

        configureUserRowEvents(row) {
            let img = new Image();
            img.src = `${this.templateFolder}/img/user-icon.png`;

            const activateEvents = ['dragstart', 'drag'];
            activateEvents.forEach(eventName => {
                row.addEventListener(eventName, (e) => {
                    row.classList.add('dragging');

                    if (eventName === 'dragstart')
                    {
                        e.dataTransfer.dropEffect = "link";
                        e.dataTransfer.setDragImage(img, 25, 25);
                        e.dataTransfer.setData('userData', row.dataset.item);
                    }
                });
            });

            const deactivateEvents = ['drop', 'dragend', 'dragexit'];
            deactivateEvents.forEach(eventName => {
                row.addEventListener(eventName, (e) => {
                    e.preventDefault();
                    row.classList.remove('dragging');
                });
            });
        },

        showBindingPopup(userData, projectData) {
            const popupId = "staffing-user-to-project-add-popup-" + Math.round(Math.random() * (99999 - 10000) + 10000);
            const bindingForm = this.createBindingForm(userData.ID, projectData.ID, projectData[this.projectTypeFieldCode]);

            if (!userData.NameText){
                userData.NameText = 'Name';
            }

            if (!projectData.TitleText){
                projectData.TitleText = 'Project';
            }

            const popup = BX.PopupWindowManager.create(
                popupId,
                null,
                {
                    content: bindingForm,
                    width: 540,
                    closeIcon: false,
                    titleBar: `Staffing ${userData.NameText} to "${projectData.TitleText}"`,
                    closeByEsc: false,
                    overlay: {
                        backgroundColor: 'black',
                        opacity: 500
                    },
                    buttons: [
                        new BX.PopupWindowButton({
                            text: BX.message('SAVE_BTN_TEXT'),
                            className: 'ui-btn ui-btn-primary',
                            id: this.bindingFormSubmitBtnId,
                            events: {
                                click: () => {
                                    this.submitBindingForm(bindingForm, popup);
                                }
                            }
                        }),
                        new BX.PopupWindowButton({
                            text: BX.message('CANCEL_BTN_TEXT'),
                            className: 'ui-btn ui-btn-default',
                            events: {
                                click: () => {
                                    popup.close();
                                    popup.destroy();
                                }
                            }
                        })
                    ],
                }
            );

            popup.show();
            this.initBindingFormCalendarSelectors();
            this.initBindingFormSelectActions();
        },

        createBindingForm(userId, projectId, projectType) {
            const form =  BX.create({
                tag: 'form',
                children: [
                    BX.create({
                        tag: 'span',
                        props: {
                            id: 'staffing-popup-user-info-placeholder'
                        },
                        text: 'Loading...'
                    }),

                    //staffing type(readonly)
                    BX.create({
                        tag: 'label',
                        props: {
                            className: 'ui-ctl ui-ctl-after-icon ui-ctl-dropdown'
                        },
                        children: [
                            BX.create({
                                tag: 'span',
                                props: {
                                    className: 'ui-ctl-after ui-ctl-icon-angle'
                                },
                            }),
                            BX.create({
                                tag: 'span',
                                props: {
                                    className: 'ui-ctl-tag'
                                },
                                text: BX.message('STAFFING_TYPE')
                            }),
                            BX.create({
                                tag: 'select',
                                props: {
                                    id: this.typeSelectId,
                                    className: 'ui-ctl-element',
                                    name: this.typeSelectName,
                                    required: 'true',
                                    disabled: 'true'
                                },
                                children: this.staffingEmploymentTypes.map(
                                    (type) => {
                                        return BX.create({
                                            tag: 'option',
                                            props: {
                                                value: type,
                                                selected: (type === projectType),
                                                disabled: 'true'
                                            },
                                            html: type.charAt(0).toUpperCase() + type.slice(1)
                                        });
                                    }
                                ),
                            }),
                        ]
                    }),

                    //user role
                    BX.create({
                        tag: 'label',
                        props: {
                            className: 'ui-ctl ui-ctl-after-icon ui-ctl-dropdown'
                        },
                        children: [
                            BX.create({
                                tag: 'span',
                                props: {
                                    className: 'ui-ctl-after ui-ctl-icon-angle'
                                },
                            }),
                            BX.create({
                                tag: 'span',
                                props: {
                                    className: 'ui-ctl-tag'
                                },
                                text: BX.message('STAFFING_USER_ROLE')
                            }),
                            BX.create({
                                tag: 'select',
                                props: {
                                    id: 'STAFFING_USER_ROLE',
                                    className: 'ui-ctl-element',
                                    name: 'USER_ROLE',
                                    required: 'true',
                                },
                                children: this.staffingUserRoles.map(
                                    (role) => {
                                        return BX.create({
                                            tag: 'option',
                                            props: {
                                                value: role,
                                            },
                                            html: role
                                        });
                                    }
                                ),
                            }),
                        ]
                    }),

                    //employment percent
                    BX.create({
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
                                text: BX.message('STAFFING_EMPLOYMENT')
                            }),
                            BX.create({
                                tag: 'input',
                                props: {
                                    id: 'STAFFING_EMPLOYMENT',
                                    type: 'number',
                                    className: 'ui-ctl-element',
                                    name: 'USER_EMPLOYMENT_PERCENT',
                                    required: 'true',
                                },
                                events: {
                                    input: (event) => {
                                        if (Number(event.target.value) > 100){
                                            event.target.value = 100;
                                        }
                                        if (Number(event.target.value) < 0){
                                            event.target.value = 0;
                                        }
                                    }
                                }
                            }),
                        ]
                    }),

                    //date from/to
                    BX.create({
                        tag: 'div',
                        props: {
                            className: 'staffing-popup-calendar-fields-wrapper'
                        },
                        children: [
                            BX.create({
                                tag: 'label',
                                props: {
                                    className: 'ui-ctl ui-ctl-after-icon ui-ctl-date ui-ctl-inline'
                                },
                                children: [
                                    BX.create({
                                        tag: 'span',
                                        props: {
                                            className: 'ui-ctl-tag',
                                        },
                                        text: BX.message('STAFFING_DATE_FROM')
                                    }),
                                    BX.create({
                                        tag: 'span',
                                        props: {
                                            className: 'ui-ctl-after ui-ctl-icon-calendar'
                                        }
                                    }),
                                    BX.create({
                                        tag: 'input',
                                        props: {
                                            id: 'STAFFING_DATE_FROM',
                                            type: 'text',
                                            className: 'ui-ctl-element',
                                            name: 'STAFFING_DATE_FROM',
                                            required: 'true',
                                        }
                                    }),
                                ]
                            }),

                            BX.create({
                                tag: 'label',
                                props: {
                                    className: 'ui-ctl ui-ctl-after-icon ui-ctl-date ui-ctl-inline'
                                },
                                children: [
                                    BX.create({
                                        tag: 'span',
                                        props: {
                                            className: 'ui-ctl-tag'
                                        },
                                        text: BX.message('STAFFING_DATE_TO')
                                    }),
                                    BX.create({
                                        tag: 'span',
                                        props: {
                                            className: 'ui-ctl-after ui-ctl-icon-calendar'
                                        }
                                    }),
                                    BX.create({
                                        tag: 'input',
                                        props: {
                                            id: 'STAFFING_DATE_TO',
                                            type: 'text',
                                            className: 'ui-ctl-element',
                                            name: 'STAFFING_DATE_TO',
                                            required: 'true',
                                        }
                                    }),
                                ]
                            }),
                        ]
                    }),

                    //per diem block
                    BX.create('div', {
                        props: {
                            className: 'staffing-popup-per-diem-wrapper'
                        },
                        children: [
                            BX.create({
                                tag: 'div',
                                props: {
                                    className: 'staffing-popup-per-diem-basic'
                                },
                                children: [
                                    BX.create({
                                        tag: 'span',
                                        props: {
                                            className: 'staffing-popup-per-diem-basic-title'
                                        },
                                        text: BX.message('STAFFING_USER_PER_DIEM')
                                    }),

                                    BX.create({
                                        tag: 'span',
                                        props: {
                                            className: 'staffing-popup-per-diem-basic-value',
                                            id: this.basicPerDiemFormValueNodeId,
                                        },
                                        text: 'loading...'
                                    }),

                                    BX.create({
                                        tag: 'button',
                                        props: {
                                            className: 'staffing-popup-per-diem-basic-edit-btn ui-btn ui-btn-xs ui-btn-icon-edit',
                                            id: this.basicPerDiemEditBtnId,
                                        },
                                        attrs: {
                                            type: 'button'
                                        },
                                        text: BX.message('EDIT_BTN_TEXT'),
                                        events: {
                                            click: (e) => this.togglePerDiemEditBlock(e.currentTarget),
                                        }
                                    }),
                                ]
                            }),
                            BX.create({
                                tag: 'div',
                                props: {
                                    className: 'staffing-popup-per-diem-edit',
                                    id: this.perDiemEditWrapperId,
                                },
                                children: [
                                    BX.create({
                                        tag: 'table',
                                        props: {
                                            className: 'staffing-popup-per-diem-edit-table',
                                            id: this.perDiemEditTableId,
                                        },
                                        children: [
                                            BX.create('thead', {
                                                html:  `<tr>
                                                            <th></th>
                                                            <th>${BX.message('PER_DIEM_EDIT_PDO')}</th>
                                                            <th>${BX.message('PER_DIEM_EDIT_FROM')}</th>
                                                            <th>${BX.message('PER_DIEM_EDIT_TO')}</th>
                                                            <th>${BX.message('PER_DIEM_EDIT_REASON')}</th>
                                                        </tr>`
                                            }),
                                            BX.create('tbody',{}),
                                        ]
                                    }),

                                    BX.create({
                                        tag: 'button',
                                        props: {
                                            className: 'staffing-popup-per-diem-edit-add-btn ui-btn ui-btn-xs ui-btn-icon-add',
                                        },
                                        attrs: {
                                            type: 'button'
                                        },
                                        events: {
                                            click: () => this.addPerDiemEditRow(),
                                        }
                                    }),
                                ]
                            }),
                        ]
                    }),

                    BX.create({
                        tag: 'input',
                        props: {
                            type: 'hidden',
                            name: 'PROJECT_ID',
                            value: projectId
                        }
                    }),
                    BX.create({
                        tag: 'input',
                        props: {
                            type: 'hidden',
                            name: 'USER_ID',
                            value: userId
                        }
                    }),
                ]
            });
            this.insertUserInfo(userId, form);
            return form;
        },

        insertUserInfo(userId, node){
            if (isNaN(userId) || !BX.type.isDomNode(node))
            {
                BX.Cbit?.Mc?.Core?.MainUI?.showErrorPopup('insertUserInfo error. "userId" must be a number, "node" must be a DOM node');
                return;
            }
            BX.ajax.runComponentAction(this.componentName, "getUserInfo", {
                mode: 'ajax',
                data: {
                    userId: userId
                }
            }).then( response => {
                if (response.status === 'success')
                {
                    const basicPerDiemNode = BX(this.basicPerDiemFormValueNodeId);
                    if (basicPerDiemNode)
                    {
                        basicPerDiemNode.textContent = response.data['perDiem'];
                    }

                    const placeholder = BX('staffing-popup-user-info-placeholder');
                    placeholder && placeholder.remove();
                    node.prepend(BX.create({
                        tag: 'div',
                        props: {
                            className: 'staffing-popup-user-info'
                        },
                        children: [
                            BX.create({
                                tag: 'div',
                                props: {
                                    className: 'staffing-popup-user-info-main',
                                },
                                children: [
                                    BX.create({
                                        tag: 'div',
                                        props: {
                                            className: 'staffing-popup-user-info-img',
                                        },
                                        children: [
                                            response.data.photo ? BX.create({tag: 'img', props: { src: response.data.photo}}) : ''
                                        ]
                                    }),
                                    BX.create({
                                        tag: 'div',
                                        props: {
                                            className: 'staffing-popup-user-info-text',
                                        },
                                        children: [
                                            BX.create({
                                                tag: 'span',
                                                props: {
                                                    className: 'staffing-popup-user-info-name',
                                                },
                                                html: response.data.name
                                            }),
                                            BX.create({
                                                tag: 'span',
                                                props: {
                                                    className: 'staffing-popup-user-info-position',
                                                },
                                                text: response.data.position
                                            }),
                                        ]
                                    })
                                ]
                            }),
                            BX.create({
                                tag: 'div',
                                props: {
                                    className: 'staffing-popup-user-info-employment',
                                },
                                html: `${BX.message('CURRENT_USER_EMPLOYMENT')} <b>${response.data['employment']}%</b><br>
                                        ${BX.message('CURRENT_USER_EMPLOYMENT_MORE')}`
                            }),
                        ]
                    }));
                }
                else
                {
                    throw new Error('Something went wrong. Unknown response status - '.response.status);
                }
            }).catch(response => {
                const error = response.errors?.[0]?.message ?? 'Something went wrong in insertUserInfo. Unknown error. Response data - ' + JSON.stringify(response);
                BX.Cbit?.Mc?.Core?.MainUI?.showErrorPopup(error);
            });
        },

        initBindingFormCalendarSelectors() {
            const nodes = [
                BX('STAFFING_DATE_FROM'), BX('STAFFING_DATE_TO')
            ];

            nodes.forEach(node => {
                if (node)
                {
                    node.addEventListener('click', () => {
                        BX.calendar({
                            node: node,
                            field: node,
                            bTime: false
                        });
                    });

                    node.setAttribute('readonly', 'true');
                }
            });
        },

        initBindingFormSelectActions() {
            const typeSelect = BX(this.typeSelectId);
            typeSelect && typeSelect.addEventListener('change', (e) => {
                this.checkBindingFormTypeSelectValue(e.target);
            });

            this.checkBindingFormTypeSelectValue(typeSelect);
        },

        checkBindingFormTypeSelectValue(typeSelect){
            const empPercentInput = BX('STAFFING_EMPLOYMENT');
            if (empPercentInput && (typeSelect.value === this.staffingEmploymentTypeStaffed))
            {
                empPercentInput.value = 100;
                //empPercentInput.setAttribute('readonly', true);
            }
            else
            {
                empPercentInput.value = '';
                //empPercentInput.removeAttribute('readonly');
            }
        },

        submitBindingForm(bindingForm, bindingPopup) {
            if (!this.validateBindingForm(bindingForm))
            {
                return false;
            }

            if (!this.checkPerDiemEditData())
            {
                return false;
            }

            const formData = new FormData(bindingForm);

            //add manually because select is disabled
            formData.set(this.typeSelectName, BX(this.typeSelectId).value);

            const submitBtn = BX(this.bindingFormSubmitBtnId);
            submitBtn && submitBtn.classList.add("ui-btn-wait");

            BX.ajax.runComponentAction(this.componentName, "bindUserToProject", {
                mode: 'ajax',
                data: formData
            }).then( response => {
                if (response.status === 'success')
                {
                    bindingPopup.close();
                    bindingPopup.destroy();
                    BX.Cbit?.Mc?.Core?.MainUI?.showSuccessPopup(response.data.message ? response.data.message : false);
                    this.getProjects();
                }
                else
                {
                    throw new Error('Something went wrong. Unknown response status - '.response.status);
                }

                submitBtn && submitBtn.classList.remove("ui-btn-wait");

            }).catch(response => {
                const error = response.errors?.[0]?.message ?? 'Something went wrong in submitBindingForm. Unknown error';
                BX.Cbit?.Mc?.Core?.MainUI?.showErrorPopup(error);
                submitBtn && submitBtn.classList.remove("ui-btn-wait");
            });
        },

        validateBindingForm(bindingForm) {
            let result = true;

            const fields = bindingForm.querySelectorAll('input, select');
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
        },

        initExportButtons() {
            const usersExportBtn = BX('users-export-excel-btn');
            usersExportBtn && usersExportBtn.addEventListener('click', () => {
                window.location.href = `${window.location.origin}${window.location.pathname}?EXCEL_MODE=Y&EXPORT_USERS=Y&USER_PAGES_COUNT=${this.usersLimitCount}`;
            });

            const projectsExportBtn = BX('projects-export-excel-btn');
            projectsExportBtn && projectsExportBtn.addEventListener('click', () => {
                window.location.href = `${window.location.origin}${window.location.pathname}?EXCEL_MODE=Y&EXPORT_PROJECTS=Y&PROJECT_PAGES_COUNT=${this.projectsLimitCount}`;
            });
        },

        togglePerDiemEditBlock(editBtnNode = false) {
            if (!editBtnNode)
            {
                editBtnNode = BX(this.basicPerDiemEditBtnId);
            }

            if (BX.type.isDomNode(editBtnNode))
            {
                editBtnNode.classList.toggle('ui-btn-icon-edit');
                editBtnNode.classList.toggle('ui-btn-icon-eye-closed');
            }

            const editWrapper = BX(this.perDiemEditWrapperId);
            editWrapper && editWrapper.classList.toggle('show');
        },

        addPerDiemEditRow() {
            const table = BX(this.perDiemEditTableId);
            const tbody = table ? table.querySelector('tbody') : false;
            if (tbody)
            {
                tbody.append(this.createPerDiemEditRow());
            }
        },

        createPerDiemEditRow() {
            if (this.currentPerDiemEditRowsCount >= this.maxPerDiemEditRowsCount)
            {
                BX.Cbit?.Mc?.Core?.MainUI?.showErrorPopup(`Can not add more than ${this.maxPerDiemEditRowsCount} rows`);
                return '';
            }

            this.currentPerDiemEditRowsCount++;

            return BX.create('tr', {
                props: {
                    className: 'staffing-popup-per-diem-edit-table-row'
                },
                children: [
                    BX.create('td', {
                        children: [
                            BX.create('button', {
                                props: {
                                    className: 'ui-btn ui-btn-xs ui-btn-icon-remove'
                                },
                                events: {
                                    click: (e) => {
                                        e.currentTarget.closest('tr').remove();
                                        this.currentPerDiemEditRowsCount--;
                                        if (this.currentPerDiemEditRowsCount === 0)
                                        {
                                            this.togglePerDiemEditBlock();
                                        }
                                    },
                                }
                            }),
                        ]
                    }),
                    BX.create('td', {
                        children: [
                            BX.create('input', {
                                props: {
                                    className: this.perDiemEditRowPdoValueClassName
                                },
                                attrs: {
                                    type: 'number',
                                    name: 'PER_DIEM_EDIT_PDO[]',
                                    min: '0',
                                    max: '99999',
                                }
                            }),
                        ]
                    }),
                    BX.create('td', {
                        children: [
                            BX.create('input', {
                                props: {
                                    className: this.perDiemEditRowDateFromValueClassName,
                                },
                                attrs: {
                                    type: 'text',
                                    readonly: true,
                                    name: 'PER_DIEM_EDIT_DATE_FROM[]'
                                },
                                events: {
                                    click: (e) => {
                                        BX.calendar({
                                            node: e.currentTarget,
                                            field: e.currentTarget,
                                            bTime: false
                                        });
                                    }
                                }
                            }),
                        ]
                    }),
                    BX.create('td', {
                        children: [
                            BX.create('input', {
                                props: {
                                    className: this.perDiemEditRowDateToValueClassName,
                                },
                                attrs: {
                                    type: 'text',
                                    readonly: true,
                                    name: 'PER_DIEM_EDIT_DATE_TO[]'
                                },
                                events: {
                                    click: (e) => {
                                        BX.calendar({
                                            node: e.currentTarget,
                                            field: e.currentTarget,
                                            bTime: false
                                        });
                                    }
                                }
                            }),
                        ]
                    }),
                    BX.create('td', {
                        children: [
                            BX.create('select', {
                                props: {
                                    className: this.perDiemEditRowReasonValueClassName,
                                },
                                attrs: {
                                    name: 'PER_DIEM_EDIT_REASON[]'
                                },
                                children: this.getPerDiemReasonOptionNodes()
                            }),
                        ]
                    }),
                ]
            });
        },

        checkPerDiemEditData() {
            let result = true;

            const table = BX(this.perDiemEditTableId);
            const tbody = table ? table.querySelector('tbody') : false;
            if (tbody)
            {
                const rows = tbody.querySelectorAll('tr');
                rows.length && rows.forEach(row => {
                    const inputs = row.querySelectorAll('input, select');
                    inputs.length && inputs.forEach(input => {
                        if (!input.value)
                        {
                            input.classList.add('error');
                            result = false;

                            const editWrapper = BX(this.perDiemEditWrapperId);
                            if (editWrapper && !editWrapper.classList.contains('show'))
                            {
                                this.togglePerDiemEditBlock();
                            }
                        }
                        else
                        {
                            input.classList.remove('error');
                        }
                    });
                });
            }

            return result;
        },

        getPerDiemReasonOptionNodes() {
            const result = [];

            if (typeof this.perDiemEditReasons === 'object')
            {
                for (let key in this.perDiemEditReasons)
                {
                    result.push(BX.create('option', {
                        props: {
                            className: this.perDiemEditRowReasonValueClassName,
                            value: key
                        },
                        text: this.perDiemEditReasons[key]
                    }));
                }
            }

            return result;
        }
    }
})