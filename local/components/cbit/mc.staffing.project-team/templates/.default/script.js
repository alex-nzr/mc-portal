BX.ready(function (){
    BX.namespace("Cbit.Mc.Staffing.ProjectTeamComponent");

    BX.Cbit.Mc.Staffing.ProjectTeamComponent = {
        init(params){
            this.componentName          = params.componentName;
            this.signedParameters       = params.signedParameters;
            this.staffingUserRoles      = params.staffingUserRoles;
            this.needleFormSubmitBtnId  = 'needle-add-form-submit-btn';
            this.needleAddBtn           = BX('staffing-project-team-needle-add-btn');

            this.staffingPeriodEditFormSubmitBtnId = 'staffing-period-edit-form-submit';

            this.needleBlockTitleNode   = BX('needle-employees-title');
            this.needleTableBody        = BX('needle-employees-table-body');
            this.needleEmployees        = params.needleEmployees;
            this.projectTeamTitleNode   = BX('project-team-title');
            this.projectTeamTableHead   = BX('project-team-table-head');
            this.projectTeamTableBody   = BX('project-team-table-body');
            this.projectTeam            = params.projectTeam;
            this.projectData            = params.projectData;

            this.initNeedleAddBtn();
            this.renderNeedleEmployees();
            this.renderProjectTeam();
        },

        initNeedleAddBtn() {
            if (this.needleAddBtn)
            {
                this.needleAddBtn.addEventListener('click', () => this.showNeedleFormPopup({
                    USER_EMPLOYMENT_PERCENT: '100',
                    NEEDLE_DATE_FROM: this.projectData['START_DATE'],
                    NEEDLE_DATE_TO: this.projectData['END_DATE'],
                }));
            }
        },

        createNeedleFrom(formValues = {}) {
            const roleSelectProps = {
                id: 'NEEDLE_USER_ROLE',
                className: 'ui-ctl-element',
                name: 'USER_ROLE',
                required: 'true',
            };
            if(formValues.USER_ROLE !== undefined)
            {
                roleSelectProps.disabled = true;
            }

            return BX.create({
                tag: 'form',
                props: {
                    id: 'staffing-emp-needle-add-form'
                },
                children: [
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
                                text: BX.message('NEEDLE_USER_ROLE')
                            }),
                            BX.create({
                                tag: 'select',
                                props: roleSelectProps,
                                children: this.staffingUserRoles.map(
                                    (role) => {
                                        return BX.create({
                                            tag: 'option',
                                            props: {
                                                value: role,
                                                selected: (role === formValues.USER_ROLE)
                                            },
                                            html: role
                                        });
                                    }
                                ),
                            }),
                        ]
                    }),

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
                                text: BX.message('NEEDLE_EMPLOYMENT_PERCENT')
                            }),
                            BX.create({
                                tag: 'input',
                                props: {
                                    id: 'NEEDLE_EMPLOYMENT_PERCENT',
                                    type: 'number',
                                    className: 'ui-ctl-element',
                                    name: 'USER_EMPLOYMENT_PERCENT',
                                    required: 'true',
                                    value: formValues.USER_EMPLOYMENT_PERCENT ?? ''
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

                    BX.create({
                        tag: 'div',
                        props: {
                            className: 'form-calendar-fields-wrapper'
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
                                        text: BX.message('DATE_FROM')
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
                                            id: 'NEEDLE_DATE_FROM',
                                            type: 'text',
                                            className: 'ui-ctl-element',
                                            name: 'NEEDLE_DATE_FROM',
                                            required: 'true',
                                            readonly: 'true',
                                            value: formValues.NEEDLE_DATE_FROM ?? ''
                                        },
                                        attrs: {
                                            readonly: 'true',
                                        },
                                        events: {
                                            click: (event) => {
                                                BX.calendar({
                                                    node: event.target,
                                                    field: event.target,
                                                    bTime: false
                                                })
                                            }
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
                                        text: BX.message('DATE_TO')
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
                                            id: 'NEEDLE_DATE_TO',
                                            type: 'text',
                                            className: 'ui-ctl-element',
                                            name: 'NEEDLE_DATE_TO',
                                            required: 'true',
                                            value: formValues.NEEDLE_DATE_TO ?? ''
                                        },
                                        attrs: {
                                            readonly: 'true',
                                        },
                                        events: {
                                            click: (event) => {
                                                BX.calendar({
                                                    node: event.target,
                                                    field: event.target,
                                                    bTime: false
                                                })
                                            }
                                        }
                                    }),
                                ]
                            }),
                        ]
                    }),

                    BX.create('input', {
                        props: {
                            type: 'hidden',
                            name: 'ID',
                            value: formValues.ID ?? ''
                        }
                    })
                ]
            });
        },

        showNeedleFormPopup(formValues = {}){
            if (!this.needleFormNode)
            {
                this.needleFormNode = this.createNeedleFrom(formValues);
            }

            if (!this.needleFormPopup)
            {
                const that = this;
                that.needleFormPopup = BX.PopupWindowManager.create(
                    "needle-employment-add-popup",
                    null,
                    {
                        content: that.needleFormNode,
                        width: 700,
                        closeIcon: false,
                        titleBar: formValues.ID ? BX.message('NEEDLE_UPDATE_POPUP_TITLE') : BX.message('NEEDLE_ADD_POPUP_TITLE'),
                        closeByEsc: false,
                        overlay: {
                            backgroundColor: 'black',
                            opacity: 500
                        },
                        buttons: [
                            new BX.PopupWindowButton({
                                text: BX.message('SAVE_TEXT'),
                                className: 'ui-btn ui-btn-primary',
                                id: this.needleFormSubmitBtnId,
                                events: {
                                    click: function() {
                                        that.submitNeedleFrom();
                                    }
                                }
                            }),
                            new BX.PopupWindowButton({
                                text: BX.message('EXIT_TEXT'),
                                className: 'ui-btn ui-btn-default',
                                events: {
                                    click: function() {
                                        that.needleFormPopup.close();
                                        that.needleFormPopup.destroy();
                                        that.needleFormPopup = null;
                                        that.needleFormNode  = null;
                                    }
                                }
                            })
                        ],
                    }
                );
            }

            this.needleFormPopup && this.needleFormPopup.show();
        },

        submitNeedleFrom(){
            if (!this.validateForm(this.needleFormNode))
            {
                return false;
            }

            const submitBtn =  BX(this.needleFormSubmitBtnId);
            submitBtn && submitBtn.classList.add("ui-btn-wait");

            const formData = new FormData(this.needleFormNode);

            const action = (Number(formData.get('ID')) > 0) ? "updateNeedleEmployee" : "addNeedleEmployee";

            BX.ajax.runComponentAction(this.componentName, action, {
                signedParameters: this.signedParameters,
                mode: 'ajax',
                data: formData
            }).then( response => {
                if (response.status === 'success')
                {
                    this.needleFormPopup.close();
                    this.needleFormPopup.destroy();
                    this.needleFormPopup = null;
                    this.needleFormNode  = null;

                    if (response.data.needle)
                    {
                        this.needleEmployees = response.data.needle;
                        this.renderNeedleEmployees();
                    }

                    this.showSuccessPopup();
                    submitBtn && submitBtn.classList.remove("ui-btn-wait");
                }
                else
                {
                    throw new Error('Something went wrong. Unknown response status - '.response.status);
                }
            }).catch(response => {
                const error = response.errors?.[0]?.message ?? 'Something went wrong. Unknown error';
                this.showErrorPopup(error);
                submitBtn && submitBtn.classList.remove("ui-btn-wait");
            });
        },

        validateForm(form) {
            let result = true;

            const fields = form.querySelectorAll('input, select');
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

        showErrorPopup: function(error) {
            BX.Cbit?.Mc?.Core?.MainUI?.showErrorPopup(error);
        },

        showSuccessPopup: function (message = false){
            BX.Cbit?.Mc?.Core?.MainUI?.showSuccessPopup(message);
        },

        showConfirmPopup(text, callback) {
            BX.UI.Dialogs.MessageBox.confirm(BX.create('div',{
                attrs: {
                    style: 'text-align:center;font-weight:600;'
                },
                text: text
            }), (messageBox) => {
                callback();
                messageBox.close();
            });
        },

        renderNeedleEmployees() {
            if (this.needleBlockTitleNode && (typeof this.needleEmployees === 'object'))
            {
                this.needleBlockTitleNode.textContent = (Object.keys(this.needleEmployees).length > 0) ? BX.message('NEEDLE_BLOCK_TITLE') : BX.message('NEEDLE_BLOCK_EMPTY');
                this.needleTableBody.innerHTML = '';
                for (const id in this.needleEmployees)
                {
                    if (this.needleEmployees.hasOwnProperty(id))
                    {
                        const row = BX.create({
                            tag: 'tr',
                            attrs: {
                                className: '',
                            },
                            children: [
                                /*BX.create({
                                    tag:'td',
                                    html: this.needleEmployees[id]['NUMBER']
                                }),*/
                                BX.create({
                                    tag:'td',
                                    html: this.needleEmployees[id]['USER_ROLE']
                                }),
                                BX.create({
                                    tag:'td',
                                    html: this.needleEmployees[id]['USER_EMPLOYMENT_PERCENT'] + "%"
                                }),
                                BX.create({
                                    tag:'td',
                                    html: this.needleEmployees[id]['NEEDLE_DATE_FROM']
                                }),
                                BX.create({
                                    tag:'td',
                                    html: this.needleEmployees[id]['NEEDLE_DATE_TO']
                                }),
                                BX.create({
                                    tag:'td',
                                    children: [
                                        BX.create({
                                            tag:'button',
                                            props: {
                                                className: 'ui-btn ui-btn-xs ui-btn-icon-edit'
                                            },
                                            html: BX.message('EDIT_TEXT'),
                                            events: {
                                                click: () => {
                                                    this.showNeedleFormPopup({
                                                        ID: id,
                                                        USER_ROLE: this.needleEmployees[id]['USER_ROLE'],
                                                        USER_EMPLOYMENT_PERCENT: this.needleEmployees[id]['USER_EMPLOYMENT_PERCENT'],
                                                        NEEDLE_DATE_FROM: this.needleEmployees[id]['NEEDLE_DATE_FROM'],
                                                        NEEDLE_DATE_TO: this.needleEmployees[id]['NEEDLE_DATE_TO'],
                                                    });
                                                }
                                            }
                                        }),
                                    ]
                                }),
                                BX.create({
                                    tag:'td',
                                    children: [
                                        BX.create({
                                            tag:'button',
                                            props: {
                                                className: 'ui-btn ui-btn-xs ui-btn-danger ui-btn-icon-remove'
                                            },
                                            html: BX.message('REMOVE_TEXT'),
                                            events: {
                                                click: () => {
                                                    this.showConfirmPopup(
                                                        BX.message('CONFIRM_DELETION'), this.deleteNeedleEmployee.bind(this, id)
                                                    );
                                                }
                                            }
                                        }),
                                    ]
                                }),
                            ],
                        });
                        this.needleTableBody.append(row);
                    }
                }
            }
        },

        deleteNeedleEmployee(id) {
            BX.ajax.runComponentAction(this.componentName, "deleteNeedleEmployee", {
                signedParameters: this.signedParameters,
                mode: 'ajax',
                data: {
                    id: id
                }
            }).then( response => {
                if (response.status === 'success' && response.data.needle)
                {
                    this.needleEmployees = response.data.needle;
                    this.renderNeedleEmployees();
                    this.showSuccessPopup();
                }
                else
                {
                    throw new Error('Something went wrong. Unknown response status - '.response.status);
                }
            }).catch(response => {
                const error = response.errors?.[0]?.message ?? 'Something went wrong. Unknown error';
                this.showErrorPopup(error);
            });
        },

        renderProjectTeam() {
            if (this.projectTeamTitleNode && (typeof this.projectTeam === 'object'))
            {
                this.projectTeamTitleNode.textContent = (Object.keys(this.projectTeam).length > 0) ? BX.message('TEAM_BLOCK_TITLE') : BX.message('TEAM_BLOCK_EMPTY');

                this.projectTeamTableHead.innerHTML = '';
                if ((Object.keys(this.projectTeam).length > 0))
                {
                    const row = BX.create({
                        tag: 'tr',
                        attrs: {
                            className: '',
                        },
                        children: [
                            /*BX.create('th', { text: '№' }),*/
                            BX.create('th', { text: 'Photo'}),
                            BX.create('th', { text: 'Name' }),
                            BX.create('th', { text: 'Skill' }),
                            BX.create('th', { text: 'Involvement' }),
                            BX.create('th', { text: 'Type' }),
                            BX.create('th', { text: 'Per diem' }),
                            BX.create('th', { text: 'From' }),
                            BX.create('th', { text: 'To' }),
                            BX.create('th', {}),
                            BX.create('th', {}),
                        ],
                    });
                    this.projectTeamTableHead.append(row);
                }

                this.projectTeamTableBody.innerHTML = '';
                for (const id in this.projectTeam)
                {
                    const row = BX.create({
                        tag: 'tr',
                        attrs: {
                            className: '',
                        },
                        children: [
                            /*BX.create('td', { html: this.projectTeam[id]['NUMBER'] }),*/
                            BX.create({
                                tag:'td',
                                children: [
                                    BX.create('div', {
                                        props: {
                                            className: 'staffing-project-team-user-photo'
                                        },
                                        html: this.projectTeam[id]['USER_PHOTO'] ? `<img src="${this.projectTeam[id]['USER_PHOTO']}" alt="avatar">` : ''
                                    })
                                ]
                            }),
                            BX.create('td', { html: this.projectTeam[id]['USER_LINK'] }),
                            BX.create('td', { html: this.projectTeam[id]['USER_ROLE'] }),
                            BX.create('td', { html: this.projectTeam[id]['USER_EMPLOYMENT_PERCENT'] + '%' }),
                            BX.create('td', { html: this.projectTeam[id]['USER_EMPLOYMENT_TYPE'] }),
                            BX.create('td', { html: this.projectTeam[id]['USER_PER_DIEM'] }),
                            BX.create('td', { html: this.projectTeam[id]['STAFFING_DATE_FROM'] }),
                            BX.create('td', { html: this.projectTeam[id]['STAFFING_DATE_TO'] }),
                            BX.create({
                                tag:'td',
                                children: [
                                    BX.create({
                                        tag:'button',
                                        props: {
                                            className: 'ui-btn ui-btn-xs ui-btn-icon-edit'
                                        },
                                        html: BX.message('EDIT_TEXT'),
                                        events: {
                                            click: () => {
                                                this.showStaffingPeriodEditPopup(this.projectTeam[id] ?? {});
                                            }
                                        }
                                    }),
                                ]
                            }),
                            BX.create({
                                tag:'td',
                                children: [
                                    BX.create({
                                        tag:'button',
                                        props: {
                                            className: 'ui-btn ui-btn-xs ui-btn-danger ui-btn-icon-remove'
                                        },
                                        html: BX.message('REMOVE_TEXT'),
                                        events: {
                                            click: () => {
                                                this.showConfirmPopup(
                                                    BX.message('CONFIRM_DELETION'), this.deleteEmployeeFromProjectTeam.bind(this, id, this.projectTeam[id]['USER_ID'])
                                                );
                                            }
                                        }
                                    }),
                                ]
                            }),
                        ],
                    });
                    this.projectTeamTableBody.append(row);
                }
            }
        },

        deleteEmployeeFromProjectTeam(recordId, userId) {
            BX.ajax.runComponentAction(this.componentName, "deleteEmployeeFromProjectTeam", {
                signedParameters: this.signedParameters,
                mode: 'ajax',
                data: {
                    recordId: recordId,
                    userId: userId,
                }
            }).then( response => {
                if (response.status === 'success' && response.data.projectTeam)
                {
                    this.projectTeam     = response.data.projectTeam;
                    this.needleEmployees = response.data.needle;
                    this.renderNeedleEmployees();
                    this.renderProjectTeam();
                    this.showSuccessPopup();
                }
                else
                {
                    throw new Error('Something went wrong. Unknown response status - '.response.status);
                }
            }).catch(response => {
                const error = response.errors?.[0]?.message ?? 'Something went wrong. Unknown error';
                this.showErrorPopup(error);
            });
        },

        showStaffingPeriodEditPopup(formValues = {}) {
            if (!this.staffingPeriodEditFormNode)
            {
                this.staffingPeriodEditFormNode = this.createStaffingPeriodEditForm(formValues);
            }

            if (!this.staffingPeriodEditPopup)
            {
                const that = this;
                that.staffingPeriodEditPopup = BX.PopupWindowManager.create(
                    "staffing-period-edit-popup",
                    null,
                    {
                        content: that.staffingPeriodEditFormNode,
                        width: 700,
                        closeIcon: false,
                        titleBar: BX.message('PERIOD_EDIT_POPUP_TITLE'),
                        closeByEsc: false,
                        overlay: {
                            backgroundColor: 'black',
                            opacity: 500
                        },
                        buttons: [
                            new BX.PopupWindowButton({
                                text: BX.message('SAVE_TEXT'),
                                className: 'ui-btn ui-btn-primary',
                                id: this.staffingPeriodEditFormSubmitBtnId,
                                events: {
                                    click: function() {
                                        that.submitStaffingPeriodEditForm();
                                    }
                                }
                            }),
                            new BX.PopupWindowButton({
                                text: BX.message('EXIT_TEXT'),
                                className: 'ui-btn ui-btn-default',
                                events: {
                                    click: function() {
                                        that.staffingPeriodEditPopup.close();
                                        that.staffingPeriodEditPopup.destroy();
                                        that.staffingPeriodEditPopup = null;
                                        that.staffingPeriodEditFormNode  = null;
                                    }
                                }
                            })
                        ],
                    }
                );
            }

            this.staffingPeriodEditPopup && this.staffingPeriodEditPopup.show();
        },

        createStaffingPeriodEditForm(formValues = {}){
            return BX.create({
                tag: 'form',
                props: {
                    id: 'staffing-emp-period-edit-form'
                },
                children: [
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
                                text: BX.message('NEEDLE_USER_ROLE')
                            }),
                            BX.create({
                                tag: 'select',
                                props: {
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
                                                selected: (role === formValues.USER_ROLE)
                                            },
                                            html: role
                                        });
                                    }
                                ),
                            }),
                        ]
                    }),

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
                                text: BX.message('NEEDLE_EMPLOYMENT_PERCENT')
                            }),
                            BX.create({
                                tag: 'input',
                                props: {
                                    type: 'number',
                                    className: 'ui-ctl-element',
                                    name: 'USER_PER_DIEM',
                                    required: 'true',
                                    value: formValues['USER_PER_DIEM'] ?? ''
                                },
                                events: {
                                    input: (event) => {
                                        if (Number(event.target.value) < 0){
                                            event.target.value = 0;
                                        }
                                    }
                                }
                            }),
                        ]
                    }),

                    BX.create({
                        tag: 'div',
                        props: {
                            className: 'form-calendar-fields-wrapper'
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
                                        text: BX.message('DATE_FROM')
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
                                            readonly: 'true',
                                            value: formValues.STAFFING_DATE_FROM ?? ''
                                        },
                                        attrs: {
                                            readonly: 'true',
                                        },
                                        events: {
                                            click: (event) => {
                                                BX.calendar({
                                                    node: event.target,
                                                    field: event.target,
                                                    bTime: false
                                                })
                                            }
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
                                        text: BX.message('DATE_TO')
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
                                            value: formValues.STAFFING_DATE_TO ?? ''
                                        },
                                        attrs: {
                                            readonly: 'true',
                                        },
                                        events: {
                                            click: (event) => {
                                                BX.calendar({
                                                    node: event.target,
                                                    field: event.target,
                                                    bTime: false
                                                })
                                            }
                                        }
                                    }),
                                ]
                            }),
                        ]
                    }),

                    BX.create('input', {
                        props: {
                            type: 'hidden',
                            name: 'USER_ID',
                            value: formValues.USER_ID ?? ''
                        }
                    }),

                    BX.create('input', {
                        props: {
                            type: 'hidden',
                            name: 'ID',
                            value: formValues.ID ?? ''
                        }
                    })
                ]
            });
        },

        submitStaffingPeriodEditForm() {
            if (!this.validateForm(this.staffingPeriodEditFormNode))
            {
                return false;
            }

            const submitBtn =  BX(this.staffingPeriodEditFormSubmitBtnId);
            submitBtn && submitBtn.classList.add("ui-btn-wait");

            const formData = new FormData(this.staffingPeriodEditFormNode);

            BX.ajax.runComponentAction(this.componentName, "updateStaffingPeriodOfUser", {
                signedParameters: this.signedParameters,
                mode: 'ajax',
                data: formData
            }).then( response => {
                if (response.status === 'success')
                {
                    this.staffingPeriodEditPopup.close();
                    this.staffingPeriodEditPopup.destroy();
                    this.staffingPeriodEditPopup    = null;
                    this.staffingPeriodEditFormNode = null;

                    if (response.data.needle && response.data.projectTeam)
                    {
                        this.projectTeam     = response.data.projectTeam;
                        this.needleEmployees = response.data.needle;
                        this.renderNeedleEmployees();
                        this.renderProjectTeam();
                    }

                    this.showSuccessPopup();
                    submitBtn && submitBtn.classList.remove("ui-btn-wait");
                }
                else
                {
                    throw new Error('Something went wrong. Unknown response status - '.response.status);
                }
            }).catch(response => {
                const error = response.errors?.[0]?.message ?? 'Something went wrong. Unknown error';
                this.showErrorPopup(error);
                submitBtn && submitBtn.classList.remove("ui-btn-wait");
            });
        }
    }
})