/**
 * ==================================================
 * Developer: Alexey Nazarov
 * E-mail: alsnazarov@1cbit.ru
 * Copyright (c) 2019 - 2022
 * ==================================================
 * mc-portal - ui-detail.js
 * 13.07.2022 14:46
 * ==================================================
 */

export class UiDetail
{
	constructor(options = {}) {
		this.moduleId 			 = options.moduleId;
		this.typeId 			 = options.typeId;
		this.entityTypeId 		 = options.entityTypeId;
		this.entityId 			 = options.entityId;
		this.opportunity		 = Number(options.opportunity);
		this.isNew				 = options.isNew;
		this.typeOfRequest		 = options.typeOfRequest;
		this.splitBtnDatasetId	 = options.splitBtnDatasetId;
		this.pullActions		 = options.pullActions;
		this.postActionKey  	 = options.postActionKey;
		this.splitRequestAction  = options.splitRequestAction;
		this.isAdmin			 = options.isAdmin;

		this.typeOfRequestNodeId   = 'type_of_request_block';
		this.submitSplitFormBtnId  = 'split-request-amount-submit-btn';
		this.submitRejectFormBtnId = 'reject-request-submit-btn';

		this.init();
	}

	init() {
		this.initEntityDetailManager();
		this.initEvents();
		this.initButtons();
	}

	initEntityDetailManager(){
		this.entityDetailManager = new BX.Cbit.Mc.Core.EntityDetailManager({
			moduleId: 			 	     this.moduleId,
			typeId: 			 	     this.typeId,
			entityTypeId: 		 	     this.entityTypeId,
			entityId: 			 	     this.entityId,
			isNew:				 	     this.isNew,
			pageTitleEditable: 		     false,
			enableCategorySelector:      false,
			cardConfigEditable: 	     this.isAdmin,
			enableCommunicationControls: false,
			enableSectionCreation: 	     this.isAdmin,
			enableSectionEdit: 		     this.isAdmin,
			enableFieldsContextMenu:     false,
			enableSectionEditMode: 	     this.isAdmin,
			showEmptySections: 		     false,
			hideTimelineInCreationPage:  true,
			isStageFlowActive:			 true,
			reloadOnStageChange:		 true,
		});
	}

	initEvents(){
		BX.addCustomEvent("onPullEvent", BX.delegate(function(moduleId, command, params){
			if ((moduleId === this.moduleId) && (Number(params.itemId) === Number(this.entityId)))
			{
				switch (command) {
					case this.pullActions['showRejectReasonPopup']:
						this.showRejectReasonPopup();
						break;
				}
			}
		}, this));

		BX.addCustomEvent('BX.Crm.EntityEditorSection:onLayout', (e) => {
			this.insertTypeOfRequestToEditorFrom();
		})
	}

	initButtons(){
		this.splitBtn = document.querySelector(`[data-id="${this.splitBtnDatasetId}"]`);
		this.splitBtn && this.splitBtn.addEventListener('click', () => this.showSplitAmountPopup());
	}

	showRejectReasonPopup() {
		if (!this.rejectFormPopup)
		{
			this.rejectFormNode  = this.createRejectForm();
			this.rejectFormPopup = BX.PopupWindowManager.create(
				"reject-request-popup",
				null,
				{
					content: this.rejectFormNode,
					width: 500,
					closeIcon: false,
					titleBar: BX.message('UI_DETAIL_REJECT_REQUEST_REASON'),
					closeByEsc: false,
					overlay: {
						backgroundColor: 'black',
						opacity: 500
					},
					buttons: [
						new BX.PopupWindowButton({
							text: BX.message('SAVE_TEXT'),
							className: 'ui-btn ui-btn-primary',
							id: this.submitRejectFormBtnId,
							events: {
								click: () => {
									this.submitRejectForm();
								}
							}
						}),
						new BX.PopupWindowButton({
							text: BX.message('EXIT_TEXT'),
							className: 'ui-btn ui-btn-default',
							events: {
								click: () => {
									this.rejectFormPopup.close();
								}
							}
						})
					],
				}
			);
		}

		this.rejectFormPopup.show();
	}

	createRejectForm() {
		return BX.create('form', {
			children: [
				BX.create({
					tag: 'label',
					props: {
						className: 'ui-ctl ui-ctl-textarea ui-ctl-no-resize'
					},
					children: [
						BX.create({
							tag: 'span',
							props: {
								className: 'ui-ctl-tag'
							},
							text: BX.message('UI_DETAIL_REJECT_REQUEST_REASON')
						}),
						BX.create({
							tag: 'textarea',
							props: {
								name: `REJECT_REASON`,
								className: 'ui-ctl-element',
							},
							attrs: {
								required: true,
							}
						}),
					]
				}),

				BX.create('input', {
					props: {
						type: 'hidden',
						name: 'ITEM_ID',
						value: this.entityId
					}
				}),
			]
		});
	}

	submitRejectForm() {
		if (!this.validateForm(this.rejectFormNode))
		{
			return false;
		}

		const submitBtn =  BX(this.submitRejectFormBtnId);
		submitBtn && submitBtn.classList.add("ui-btn-wait");

		const formData = new FormData(this.rejectFormNode);

		BX.ajax.runAction('cbit.mc:expense.base.rejectRequest', {
			sessid: BX.bitrix_sessid(),
			data: formData
		}).then( response => {
			if (response.status === 'success')
			{
				this.rejectFormPopup.close();
				this.rejectFormPopup.destroy();
				this.rejectFormPopup = null;
				this.rejectFormNode  = null;

				BX.Cbit?.Mc?.Core?.MainUI?.showSuccessPopup();
				BX.Crm.EntityEditor.getDefault().refreshLayout();

				if (BX.SidePanel?.Instance?.opened)
				{
					//BX.SidePanel.Instance.reload();
				}
				else
				{
					//window.location.reload();
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

	showSplitAmountPopup() {
		if (!this.splitFormPopup)
		{
			this.splitFormNode  = this.createSplitForm();
			this.splitFormPopup = BX.PopupWindowManager.create(
				"split-request-amount-popup",
				null,
				{
					content: this.splitFormNode,
					width: 500,
					closeIcon: false,
					titleBar: BX.message('UI_DETAIL_SPLIT_POPUP_TITLE'),
					closeByEsc: false,
					overlay: {
						backgroundColor: 'black',
						opacity: 500
					},
					buttons: [
						new BX.PopupWindowButton({
							text: BX.message('SAVE_TEXT'),
							className: 'ui-btn ui-btn-primary',
							id: this.submitSplitFormBtnId,
							events: {
								click: () => {
									this.submitSplitForm();
								}
							}
						}),
						new BX.PopupWindowButton({
							text: BX.message('EXIT_TEXT'),
							className: 'ui-btn ui-btn-default',
							events: {
								click: () => {
									this.splitFormPopup.close();
								}
							}
						})
					],
				}
			);
		}

		this.splitFormPopup.show();
	}

	createSplitForm() {
		const rejectedFormInput = BX.create({
			tag: 'input',
			props: {
				name: `UF_CRM_${this.typeId}_AMOUNT_REJECTED`,
				className: 'ui-ctl-element',
			},
			attrs: {
				type: 'number',
				readonly: true,
				required: true,
			}
		});

		return BX.create('form', {
			children: [
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
							text: BX.message('UI_DETAIL_SPLIT_FORM_APPROVED_SUM')
						}),
						BX.create({
							tag: 'input',
							props: {
								name: `OPPORTUNITY`,
								className: 'ui-ctl-element',
							},
							attrs: {
								type: 'number',
								required: true,
								min: '0',
								max: `${this.opportunity}`,
								autocomplete: "new-password"
							},
							events: {
								input: (e) => {
									if (Number(e.target.value) > this.opportunity)
									{
										e.target.value = this.opportunity;
									}

									if (Number(e.target.value) < 0)
									{
										e.target.value = 0;
									}

									rejectedFormInput.value = this.opportunity - Number(e.target.value);
								}
							}
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
							text: BX.message('UI_DETAIL_SPLIT_FORM_REJECTED_SUM')
						}),
						rejectedFormInput,
					]
				}),

				BX.create({
					tag: 'label',
					props: {
						className: 'ui-ctl ui-ctl-textarea ui-ctl-no-resize'
					},
					children: [
						BX.create({
							tag: 'span',
							props: {
								className: 'ui-ctl-tag'
							},
							text: BX.message('UI_DETAIL_SPLIT_FORM_REJECT_REASON')
						}),
						BX.create({
							tag: 'textarea',
							props: {
								name: `UF_CRM_${this.typeId}_REASON`,
								className: 'ui-ctl-element',
							},
							attrs: {
								required: true,
							}
						}),
					]
				}),

				BX.create('input', {
					props: {
						type: 'hidden',
						name: 'ITEM_ID',
						value: this.entityId
					}
				}),

				BX.create('input', {
					props: {
						type: 'hidden',
						name: this.postActionKey,
						value: this.splitRequestAction
					}
				}),
			]
		});
	}

	submitSplitForm() {
		if (!this.validateForm(this.splitFormNode))
		{
			return false;
		}

		const submitBtn =  BX(this.submitSplitFormBtnId);
		submitBtn && submitBtn.classList.add("ui-btn-wait");

		const formData = new FormData(this.splitFormNode);

		BX.ajax.runAction('cbit.mc:expense.base.splitRequestAmount', {
			sessid: BX.bitrix_sessid(),
			data: formData
		}).then( response => {
			if (response.status === 'success')
			{
				this.splitFormPopup.close();
				this.splitFormPopup.destroy();
				this.splitFormPopup = null;
				this.splitFormNode  = null;

				BX.Cbit?.Mc?.Core?.MainUI?.showSuccessPopup();
				BX.Crm.EntityEditor.getDefault().refreshLayout();
				this.opportunity = formData.get(`OPPORTUNITY`);

				if (BX.SidePanel?.Instance?.opened)
				{
					BX.SidePanel.Instance.reload();
				}
				else
				{
					window.location.reload();
				}
				//this.splitBtn && this.splitBtn.remove();
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

	insertTypeOfRequestToEditorFrom() {
		const inserted = BX(this.typeOfRequestNodeId);
		if (!inserted)
		{
			const sectionContent = document.querySelector('[data-cid="general"] .ui-entity-editor-section-content');
			if (sectionContent)
			{
				sectionContent.prepend(BX.create('div', {
					props: {
						className: 'ui-entity-editor-content-block',
						id: this.typeOfRequestNodeId
					},
					children: [
						BX.create('div', {
							props: {
								className: 'ui-entity-editor-block-title'
							},
							text: BX.message('TYPE_OF_REQUEST_BLOCK_TITLE')
						}),
						BX.create('div', {
							props: {
								className: 'ui-entity-editor-content-block'
							},
							children: [
								BX.create('div', {
									props: {
										className: 'ui-entity-editor-content-block-text'
									},
									html: `<b>${this.typeOfRequest}</b>`
								}),
							]
						}),
					]
				}));
			}
		}
	}
}