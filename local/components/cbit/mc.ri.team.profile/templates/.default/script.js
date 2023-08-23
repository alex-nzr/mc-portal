BX.ready(function (){
    const namespace = BX.namespace("Cbit.Mc.RI");

    namespace.TeamProfileComponent = {
        init(params){
            this.componentName 				= params.componentName;
            this.signedParameters 			= params.signedParameters;

            this.coordinatorInstance        = new namespace.Coordinator(params);

            this.teamDescription            = params.teamDescription;
            this.teamDescWrap               = BX(params.teamDescWrapId);
            this.teamDescCurrentWrapper     = BX(params.teamDescCurrentWrapperId);
            this.teamDescCurrentBlock       = BX(params.teamDescCurrentBlockId);
            this.teamDescEditBtn            = BX(params.teamDescEditBtnId);
            this.teamDescEditWrapper        = BX(params.teamDescEditWrapperId);
            this.teamDescTextarea           = BX(params.teamDescEditBlockId);
            this.teamDescSubmitBtn          = BX(params.teamDescSubmitBtnId);

            this.teamWorkTime                 = params.teamWorkTime;
            this.teamWorkTimeWrap             = BX(params.teamWorkTimeWrapId);
            this.teamWorkTimeCurrentWrapper   = BX(params.teamWorkTimeCurrentWrapperId);
            this.teamWorkTimeCurrentBlock     = BX(params.teamWorkTimeCurrentBlockId);
            this.teamWorkTimeEditBtn          = BX(params.teamWorkTimeEditBtnId);
            this.teamWorkTimeEditWrapper      = BX(params.teamWorkTimeEditWrapperId);
            this.teamWorkTimeEditFrom         = BX(params.teamWorkTimeEditFrom);
            this.teamWorkTimeEditTo           = BX(params.teamWorkTimeEditTo);
            this.teamWorkTimeSubmitBtn        = BX(params.teamWorkTimeSubmitBtnId);

            if (!this.checkViewNodes())
            {
                console.error('Some of required view-nodes not found in TeamProfileComponent');
                return;
            }

            if (this.checkEditNodes())
            {
                this.initEditButtons();
                this.initSubmitButtons();
            }
        },

        initEditButtons() {
            this.teamDescEditBtn.addEventListener('click', () => this.toggleEditBlock(this.teamDescWrap, true))
            this.teamWorkTimeEditBtn.addEventListener('click', () => this.toggleEditBlock(this.teamWorkTimeWrap, true))
        },

        initSubmitButtons() {
            this.teamDescSubmitBtn.addEventListener('click', this.submitNewTeamDescription.bind(this));
            this.teamWorkTimeSubmitBtn.addEventListener('click', this.submitNewTeamWorkTime.bind(this));
        },

        submitNewTeamDescription(){
            const newText = this.teamDescTextarea.value;
            if (!newText)
            {
                BX.Cbit.Mc.Core.MainUI.showErrorPopup('Team description is empty');
                return;
            }
            this.teamDescSubmitBtn.classList.add("ui-btn-wait");
            BX.ajax.runComponentAction(this.componentName, 'updateRITeamDescription', {
                mode: 'ajax',
                data: {
                    text: newText
                }
            }).then( response => {
                if (response.status === 'success')
                {
                    this.teamDescCurrentBlock.innerHTML = newText;
                    this.toggleEditBlock(this.teamDescWrap, false);

                    BX.Cbit.Mc.Core.MainUI.showSuccessPopup();
                    this.teamDescSubmitBtn.classList.remove("ui-btn-wait");
                }
                else
                {
                    throw new Error('Something went wrong. Unknown response status - '.response.status);
                }
            }).catch(response => {
                const error = response.errors?.[0]?.message ?? 'Something went wrong. Unknown error';
                BX.Cbit.Mc.Core.MainUI.showErrorPopup(error);
                this.teamDescSubmitBtn.classList.remove("ui-btn-wait");
            });
        },

        submitNewTeamWorkTime(){
            const newFrom = this.teamWorkTimeEditFrom.value;
            const newTo = this.teamWorkTimeEditTo.value;
            if (!newFrom || !newTo)
            {
                BX.Cbit.Mc.Core.MainUI.showErrorPopup('Required to select time "from" and time "to"');
                return;
            }
            this.teamWorkTimeSubmitBtn.classList.add("ui-btn-wait");
            BX.ajax.runComponentAction(this.componentName, 'updateRITeamWorkTime', {
                mode: 'ajax',
                data: {
                    from: newFrom,
                    to: newTo
                }
            }).then( response => {
                if (response.status === 'success')
                {
                    this.teamWorkTimeCurrentBlock.innerHTML = `${newFrom} - ${newTo}`;
                    this.toggleEditBlock(this.teamWorkTimeWrap, false);

                    BX.Cbit.Mc.Core.MainUI.showSuccessPopup();
                    this.teamWorkTimeSubmitBtn.classList.remove("ui-btn-wait");
                }
                else
                {
                    throw new Error('Something went wrong. Unknown response status - '.response.status);
                }
            }).catch(response => {
                const error = response.errors?.[0]?.message ?? 'Something went wrong. Unknown error';
                BX.Cbit.Mc.Core.MainUI.showErrorPopup(error);
                this.teamWorkTimeSubmitBtn.classList.remove("ui-btn-wait");
            });
        },

        checkViewNodes() {
            return (
                this.teamDescWrap
                && this.teamDescCurrentWrapper
                && this.teamDescCurrentBlock
                && this.teamWorkTimeWrap
                && this.teamWorkTimeCurrentWrapper
                && this.teamWorkTimeCurrentBlock
            );
        },

        checkEditNodes() {
            return (
                this.teamDescEditBtn
                && this.teamDescEditWrapper
                && this.teamDescTextarea
                && this.teamDescSubmitBtn
                && this.teamWorkTimeEditBtn
                && this.teamWorkTimeEditWrapper
                && this.teamWorkTimeEditFrom
                && this.teamWorkTimeEditTo
                && this.teamWorkTimeSubmitBtn
            );
        },

        toggleEditBlock(blockNode, editMode = false) {
            if (editMode)
            {
                blockNode.classList.add('edit');
            }
            else
            {
                blockNode.classList.remove('edit');
            }
        }
    }

    namespace.Coordinator = function(params)
    {
        this.init(params);
    };

    namespace.Coordinator.prototype = {
        init(params){
            this.componentName 				 = params.componentName;
            this.signedParameters 			 = params.signedParameters;
            this.currentCoordinator 		 = params.currentCoordinator;
            this.coordinatorEditBlock 		 = BX(params.coordinatorEditBlockId);
            this.coordinatorCurrentBlock 	 = BX(params.coordinatorCurrentBlockId);
            this.coordinatorEntityId 		 = params.coordinatorEntityId;
            this.coordinatorEntityType 		 = params.coordinatorEntityType;
            this.coordinatorCurrentWrapper   = BX(params.coordinatorCurrentWrapperId);
            this.coordinatorEditWrapper      = BX(params.coordinatorEditWrapperId);
            this.coordinatorEditBtn 		 = BX(params.coordinatorEditBtnId);
            this.coordinatorSubmitBtn   	 = BX(params.coordinatorSubmitBtnId)
            this.coordinatorSelectionWrapper = BX(params.coordinatorSelectionWrapId)

            if (!this.coordinatorSelectionWrapper || !this.coordinatorCurrentWrapper)
            {
                return;
            }

            if (this.coordinatorEditWrapper && this.coordinatorEditBtn && this.coordinatorSubmitBtn)
            {
                this.initSelectionBlock();
                this.coordinatorSubmitBtn.addEventListener('click', this.submitNewCoordinator.bind(this));
                this.coordinatorEditBtn.addEventListener('click', () => this.toggleEditBlock(true));
            }

            this.renderCurrentCoordinator();
        },

        renderCurrentCoordinator() {
            BX.Cbit?.Mc?.RI?.MainRiUI?.insertCurrentCoordinatorToPanel(this.currentCoordinator);

            if(Object.keys(this.currentCoordinator).length > 0)
            {
                if (this.coordinatorCurrentBlock)
                {
                    this.coordinatorCurrentBlock.innerHTML = '';
                    const node = BX.create('a', {
                        attrs: {
                            href: this.currentCoordinator['LINK']
                        },
                        children: [
                            this.currentCoordinator['PHOTO']
                                ? BX.create('img', {
                                    attrs: {
                                        src: this.currentCoordinator['PHOTO']
                                    }
                                })
                                : BX.create('span', {
                                    props: {
                                        className: 'user-photo-placeholder'
                                    }
                                }),
                            BX.create('span', {
                                props: {
                                    className: 'coordinator-name'
                                },
                                text: this.currentCoordinator['NAME']
                            })
                        ]
                    });
                    this.coordinatorCurrentBlock.append(node);
                }

                if (this.tagSelector)
                {
                    this.tagSelector.removeTags();
                    this.tagSelector.addTag({
                        id: this.currentCoordinator['ID'],
                        entityId: this.coordinatorEntityId,
                        entityType: this.coordinatorEntityType,
                        link: this.currentCoordinator['LINK'],
                        avatar: this.currentCoordinator['PHOTO'],
                        title: {
                            text: this.currentCoordinator['NAME'],
                        },
                    });
                }
            }
        },

        initSelectionBlock(){
            this.tagSelector = new BX.UI.EntitySelector.TagSelector({
                id: this.coordinatorEditBlockId,
                multiple: false,
                addButtonCaption: BX.message('SELECT_TEXT'),
                addButtonCaptionMore: BX.message('SELECT_TEXT'),
                dialogOptions: {
                    context: 'COORDINATOR_SELECTOR_CONTEXT',
                    entities: [
                        {
                            id           : this.coordinatorEntityId,
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
                        this.newCoordinatorId = tag.id;
                    },
                    onAfterTagRemove: () => {
                        this.newCoordinatorId = null;
                    }
                }
            });

            if (this.coordinatorEditBlock)
            {
                this.tagSelector.renderTo(this.coordinatorEditBlock);
            }
        },

        submitNewCoordinator(){
            if (!this.newCoordinatorId)
            {
                BX.Cbit.Mc.Core.MainUI.showErrorPopup('Coordinator not selected');
                return;
            }
            this.tagSelector.lock();
            this.coordinatorSubmitBtn.classList.add("ui-btn-wait");
            BX.ajax.runComponentAction(this.componentName, 'updateRICoordinator', {
                mode: 'ajax',
                data: {
                    id: Number(this.newCoordinatorId)
                }
            }).then( response => {
                if (response.status === 'success')
                {
                    if (response.data['coordinator'])
                    {
                        this.currentCoordinator = response.data['coordinator'];
                        this.renderCurrentCoordinator();
                        this.toggleEditBlock(false);
                    }

                    BX.Cbit.Mc.Core.MainUI.showSuccessPopup();
                    this.coordinatorSubmitBtn.classList.remove("ui-btn-wait");
                    this.tagSelector.unlock();
                }
                else
                {
                    throw new Error('Something went wrong. Unknown response status - '.response.status);
                }
            }).catch(response => {
                const error = response.errors?.[0]?.message ?? 'Something went wrong. Unknown error';
                BX.Cbit.Mc.Core.MainUI.showErrorPopup(error);
                this.coordinatorSubmitBtn.classList.remove("ui-btn-wait");
                this.tagSelector.unlock();
            });
        },

        toggleEditBlock(editMode = false) {
            if (editMode)
            {
                this.coordinatorSelectionWrapper.classList.add('edit');
            }
            else
            {
                this.coordinatorSelectionWrapper.classList.remove('edit');
            }
        }
    }
})