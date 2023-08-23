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

import {Event as EventBX} from 'main.core';

export class UiDetail
{
	constructor(options = {})
	{
		this.moduleId 					= options.moduleId;
		this.typeId 					= options.typeId;
		this.pullActions				= options.pullActions
		this.typeId 					= options.typeId;
		this.entityTypeId 				= options.entityTypeId;
		this.entityId 					= options.entityId;
		this.isOnSuccessStage 			= options.isOnSuccessStage;
		this.isOnUnassignedStages		= options.isOnUnassignedStages;
		this.isItemScored 				= options.isItemScored;
		this.isItemCreatedByCurrentUser = options.isItemCreatedByCurrentUser;
		this.isCancelReasonRequired 	= options.isCancelReasonRequired;
		this.cancelReasonsList 	        = options.cancelReasonsList;
		this.cancelRequestBtnDatasetId	= options.cancelRequestBtnDatasetId;
		this.unScoredRequests 			= options.unScoredRequests;
		this.hasUnScoredRequests 		= options.hasUnScoredRequests;
		this.isNew						= options.isNew;
		this.isAdmin					= options.isAdmin;
		this.hasRiPerms  				= options.hasRiPerms;
		this.readonlyFields				= options.readonlyFields;
		this.allowQuickCancelling		= 'N';

		this.submitScoringFormBtnId = 'submit_scoring_form_btn_id';
		this.submitCancelFormBtnId = 'submit_cancel_form_btn_id';

		this.scoreParams = {
			SCORING_FORM_SPEED: {
				title: BX.message('UI_DETAIL_SCORING_FORM_SPEED'),
				selected: false,
				optionKey: 'SCORING_FORM_SPEED'
			},
			SCORING_FORM_WORK: {
				title: BX.message('UI_DETAIL_SCORING_FORM_WORK'),
				selected: false,
				optionKey: 'SCORING_FORM_WORK'
			},
			SCORING_FORM_COMMUNICATIONS: {
				title: BX.message('UI_DETAIL_SCORING_FORM_COMMUNICATIONS'),
				selected: false,
				optionKey: 'SCORING_FORM_COMMUNICATIONS'
			}
		}

		this.sliderEvents = {
			scoringCompletedEvent: 'scoringCompleted',
		}

		this.init();
	}

	init()
	{
		if((!this.isOnSuccessStage || this.isItemScored) && this.hasUnScoredRequests)
		{
			this.showUnScoredItemsListPopup();
			this.hideControlPanel();
		}

		this.initEntityDetailManager();
		this.initEvents();
		this.initButtons();
		this.addCustomStyles();
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
			isStageFlowActive:			 this.hasRiPerms,
			reloadOnStageChange:		 true,
		});
	}

	initEvents(){
		if (this.isOnSuccessStage && !this.isItemScored)
		{
			if (this.isItemCreatedByCurrentUser)
			{
				BX.addCustomEvent("SidePanel.Slider:onClose", (event) => {
					event.denyAction();
					this.showScoringPopup(this.entityId);
				});
			}
		}

		BX.addCustomEvent("onPullEvent", BX.delegate(function(moduleId, command, params){
			if ((moduleId === this.moduleId) && (Number(params.itemId) === Number(this.entityId)))
			{
				switch (command) {
					case this.pullActions.showScoringPopup:
						this.showScoringPopup(params.itemId);
						break;
				}
			}
		}, this));

		BX.addCustomEvent("SidePanel.Slider:onMessage", (event) => {
			if (event.eventId === this.sliderEvents.scoringCompletedEvent)
			{
				const itemId = event.data?.itemId;
				if (itemId)
				{
					this.updateUnScoredRequestsData(itemId);
				}
			}
		});

		BX.addCustomEvent('BX.UI.EntityEditorField:onLayout', (event) => {
			if (event._id === `UF_CRM_${this.typeId}_ATTACHMENTS`)
			{
				if (event._wrapper)
				{
					const nodes = event._wrapper.querySelectorAll(`del, [data-bx-role="file-delete"]`);
					nodes.length && nodes.forEach(node => {
						node.remove();
					});
				}
			}

			if (event._id === `UF_CRM_${this.typeId}_MAX_BUDGET`)
			{
				if (event._wrapper)
				{
					event._wrapper.querySelector('.ui-entity-editor-block-title').append(
						BX.UI.Hint.createNode(BX.message('UI_DETAIL_MAX_BUDGET_HINT'))
					);
				}
			}
		});

		EventBX.EventEmitter.subscribe('BX.Main.Popup:onShow', (event) => {
			const id = event.target.popupContainer ? event.target.popupContainer.getAttribute('id') : '';
			if (id && (id.indexOf("calendar_popup") === 0))
			{
				const cells 	= event.target.popupContainer.querySelectorAll(".bx-calendar-cell");
				const timestamp = (new Date()).getTime();

				cells.length && cells.forEach(cell => {
					const cellTime = Number(cell.dataset.date);
					if ((timestamp - cellTime) > 24*60*60*1000)
					{
						cell.classList.add('bx-calendar-date-hidden', 'disabled');
					}
				});
			}
		})
	}

	updateUnScoredRequestsData(itemId) {
		this.rebuildUnScoredRequests(itemId);

		if (this.unScoredListPopup)
		{
			this.unScoredListPopup.close();
			this.unScoredListPopup.destroy();
			this.unScoredListPopup = null;
		}

		if (Object.keys(this.unScoredRequests).length > 0)
		{
			this.showUnScoredItemsListPopup();
		}
		else
		{
			this.showControlPanel();
		}
	}

	showScoringPopup(itemId) {
		this.scoringFormNode = this.createScoringForm(itemId);
		this.scoringFormPopup = BX.PopupWindowManager.create(
			"request-scoring-popup",
			null,
			{
				content: this.scoringFormNode,
				width: 700,
				closeIcon: false,
				titleBar: BX.message('UI_DETAIL_SCORING_POPUP_TITLE'),
				closeByEsc: false,
				overlay: {
					backgroundColor: 'black',
					opacity: 500
				},
				buttons: [
					new BX.PopupWindowButton({
						text: BX.message('SAVE_TEXT'),
						className: 'ui-btn ui-btn-primary',
						id: this.submitScoringFormBtnId,
						events: {
							click: () => {
								this.submitScoringFrom();
							}
						}
					}),
					/*new BX.PopupWindowButton({
						text: BX.message('EXIT_TEXT'),
						className: 'ui-btn ui-btn-default',
						events: {
							click: () => {
								this.scoringFormPopup.close();
								this.scoringFormPopup.destroy();
								this.scoringFormPopup = null;
							}
						}
					})*/
				],
			}
		);
		this.scoringFormPopup.show();
	}

	createScoringForm(itemId) {
		this.scoreParams.SCORING_FORM_SPEED.selected = false;
		this.scoreParams.SCORING_FORM_WORK.selected = false;
		this.scoreParams.SCORING_FORM_COMMUNICATIONS.selected = false;

		return BX.create({
			tag: 'form',
			props: {
				id: 'staffing-emp-needle-add-form'
			},
			children: [
				this.createScoringBlock(this.scoreParams.SCORING_FORM_SPEED),
				this.createScoringBlock(this.scoreParams.SCORING_FORM_WORK),
				this.createScoringBlock(this.scoreParams.SCORING_FORM_COMMUNICATIONS),

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
							text: BX.message('UI_DETAIL_SCORING_FORM_COMMENT')
						}),
						BX.create({
							tag: 'textarea',
							props: {
								name: 'SCORING_FORM_COMMENT',
								className: 'ui-ctl-element'
							}
						}),
					]
				}),

				BX.create('input', {
					props: {
						type: 'hidden',
						name: 'ITEM_ID',
						value: itemId
					}
				})
			]
		});
	}

	submitScoringFrom(){
		if (!this.validateScoringForm())
		{
			BX.Cbit?.Mc?.Core?.MainUI?.showErrorPopup(BX.message('UI_DETAIL_SCORING_FORM_WARNING'));
			return false;
		}

		const submitBtn =  BX(this.submitScoringFormBtnId);
		submitBtn && submitBtn.classList.add("ui-btn-wait");

		const formData = new FormData(this.scoringFormNode);

		BX.ajax.runAction('cbit.mc:ri.base.setRequestScore', {
			sessid: BX.bitrix_sessid(),
			data: formData
		}).then( response => {
			if (response.status === 'success')
			{
				this.scoringFormPopup.close();
				this.scoringFormPopup.destroy();
				this.scoringFormPopup = null;
				this.scoringFormNode  = null;

				BX.Cbit?.Mc?.Core?.MainUI?.showSuccessPopup();
				BX.Crm.EntityEditor.getDefault().refreshLayout();
				BX.SidePanel.Instance.postMessage(
					BX.SidePanel.Instance.getTopSlider().getUrl(),
					this.sliderEvents.scoringCompletedEvent,
					{
						itemId: this.entityId
					}
				);
				setTimeout(() => {
					BX.SidePanel.Instance.reload();
				}, 1000);
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

	validateScoringForm() {
		return (
			this.scoreParams.SCORING_FORM_SPEED.selected
			&& this.scoreParams.SCORING_FORM_WORK.selected
			&& this.scoreParams.SCORING_FORM_COMMUNICATIONS.selected
		);
	}

	createScoringBlock(params) {
		return BX.create({
			tag: 'div',
			props: {
				className: 'score-selection-wrapper',
			},
			children: [
				BX.create({
					tag: 'h3',
					props: {
						className: 'score-selection-title',
					},
					text: params.title
				}),
				BX.create({
					tag: 'div',
					props: {
						className: 'score-selection-stars',
					},
					children: this.createScoringStars(params)
				}),
			]
		});
	}

	createScoringStars(params) {
		const stars = [];
		for (let i = 1; i <= 5; i++) {
			stars.push(
				BX.create('img', {
					attrs: {
						className: 'score-selection-stars-item',
						src: this.getStarImgInBase64(),
						id: `${params.optionKey}_${i}`
					},
					dataset: {
						score: i
					},
					events: {
						click: (e) => {
							const targetScore = Number(e.target.dataset.score);
							const input = BX(`${params.optionKey}_input`);
							if (input)
							{
								input.value = targetScore;
								this.scoreParams[params.optionKey].selected = true;
							}

							const parent = e.target.closest('.score-selection-stars');
							if (parent)
							{
								const stars = parent.querySelectorAll('.score-selection-stars-item');
								if (stars)
								{
									stars.forEach(star => {
										const score = Number(star.dataset.score);
										if (score <= targetScore)
										{
											star.classList.add('active');
										}
										else
										{
											star.classList.remove('active');
										}
									})
								}
							}
						}
					}
				})
			);
		}
		stars.push(BX.create('input', {
			attrs: {
				type: 'hidden',
				id: `${params.optionKey}_input`,
				name: params.optionKey,
			}
		}));
		return stars;
	}

	getStarImgInBase64() {
		return "data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAASwAAAEsCAMAAABOo35HAAAAAXNSR0IArs4c6QAAAARnQU1BAACxjwv8YQUAAACNUExURf///5+fn/Pz86Ojo1BQUExMTOvr65ubm+Pj44+Pj4ODg9vb26enp2BgYNPT0/v7+3R0dL+/v+/v71hYWFRUVN/f34eHh8/Pz/f392xsbLOzs5eXl9fX13x8fHh4eMfHx8PDw2RkZFxcXKurq4uLi3BwcLu7u6+vr+fn55OTk39/f8vLy2hoaLe3twAAADz07AYAAAAvdFJOU/////////////////////////////////////////////////////////////8AWqU49wAAAAlwSFlzAAAOwwAADsMBx2+oZAAABxpJREFUeF7tneta2zoQRWkI0FJoKJc2tKenhdLrOfX7P14TskOwLVmXWPKMtNcfILFsjz8WX7yRRweEEEIIIYQQQgghhBBCCCGE7MkLfCVuZs0M3xEnh80hviNO5s0c3xEXs6ahh74cri4WPfRkvrpY9NCPtYX00JO1hfTQk7WF9NCPjYX00IuNhfTQi42F9NCHrYX00IOthfTQg62F9NDNzkJ66GRnIT10srOQHrp4biE9dPDcQnro4LmF9HCYtoX0cJC2hfRwkLaFTXOE10mfroVNc4x3SI+uhU1zgndIj66FK/AO6dK3kB5a6VtID60YLKSHFkwW0kMLJgvpoQWjhfTQiNlCemjEbCE9NGKxkB4asFlIDw3YLKSHBqwW0sMedgvpYQ+7hfSwx4CF9LDDMS6LGXrY4gSXxQw9bIGrYgNbkTXDFtLDFsMW0sMWuCZ2sB1xW9g0L7ElcVrYNK+wJXFbSA+fcFtID59wW0gPn8D1GAbb1o6PhfQQ+FhIDwGuhgtsXTd+FtLDR/wspIeP4Fq4wfY142shPVzhayE9XIEr4QNG1Iu/hfQwwEJ6GGJh9R6GWFi9hyEWVu8hroIvGFUnYRZW7mGYhZV7iGvgD8bVSKiFVXsYamHTnGJkheAKhICR9RFuYdO8xtjqCLewYg9RfxgYWxsxFlbrYYyF1XqI6kPB6LqIs7BSD+MsrNRD1B4OxtdErIVSPTw7TwgqjwF7SMIZag/nFc6uHvYJzN5gH7XwBnXHsbjAbmrgYoGqo3mLPZXPW1S8D5dX2FnZXF+i3j0ZeiS3FMZrxXiDPZbLDSodg8U77LRM3u39l73Ne+y3RN6jxvFY3mLXpXGbpM3uB+y9LD6gurE5Hmy+oJKjhE9ff8QxSuEj6krDaxylDP5BValYfMKB9PPvyB8YTHzGsbTzBfWkZXaHw2nmPltf/q84ol6+opIcvMQxtZJ5mpfmxPkBNeRDb+I8/q2gG6WJ87clzj8zGhPn7zj3/KhLnK8nbcSlK3Geug2XpsR5zOw4DjWJ82mGW0E3OhLnzzjbqVGQON8JWqJNeuL8A+cpA9mJ80+cpRjkJs6/cIaSkJo4p86O4xCZOO8/jSgV8hLnMaYRpWK2z1TR8bkaaRpRKiQlzvGTaXMhJ3Ge/lbQAxmJ89jTiFIhIXGeIjuOYzl14nw7UXYcx7SJc6ppRKm4vMaJ52eucBGHqRLntNOIUjFN4qz12c0JEudPSj4wmMidOEvJjuOY5UyczwVlx3HkS5xzTiNKxfERiklMId2iciTO5fRsS58467kVdLNI+3f+XNWtoJO08fwdjlIIqCoVij+K9kn94XS6KWoJSP1PsqI8RE3pKMjD9LeIBXmY/l/VBXmIilJSjIc5gppiPMwxYaQYD1FPWgrx8AvKSUshHn5DOWkpxENUk5oiPMxjYSEe5rGwEA9RS3oK8DCXhUV4mMvCIjxEJTlQ72E+CwvwMJ+FTXOPY6oFdeRB+X/Ecloo7Um5YHJaqN5DVJEL1R7mtVC5h3ktVO4hasiHYg9zW6jaw9wWqvYQFeRErYffUUBO1HoY3XXyJHq5J7UeLnD+wayfKImehqrUw0gL8UTJ4jd+DkSph3EW7p4oiWt8oNPDKAtb3YjiWu2q9DDGwu4TJT/weggqPYz4teg/URLR+ECjh+EWmp8o+YV3/VHoYbCFtkXNghsfKPQw0MIL++9DaKvdK4zTQ6CFw92IAhsfqHtGM8hC56JmYa12x1v3KxMhFvoUd4ZtfZhjjBZCLPTrRhTS+ECZh/4WencjWjxghBtlHnpb+B8G+OA9n16Xh74W/h8mjHerXVUeeloY3o3oD0Y6UOWhl4VRi5r5tdrV5KGXhbGNab0aHyjy0EeW+Ma0PomzIg/vccp29lrUzKPVrh4PlzhjO/suauZOnNV46Mo37/dvTOtMnNV46LBwnG5Ejla7Wjx0WDhWNyJH4qzEw0ELx1zUbLDVrhIPhywctxvRUOKsw8MBC0df1GwocVbhod3CP9hiTOyJswoPbRYmWtTMmjhr8NBmYbpFzWytdhV0DbZYmLKTvSVxnnrNOQ+MFv5O+3SbpdUu3pWL0cL0jWmNibN4Dw0WZlnUzJQ4i/ewb2GuqQeGVrt4Ryp9C/M1pu0nzsI97FqYd1GzbuIs3MOOhbZpRKnoJs54WSZtCydY1KyTOIv2sGXhNIuatRJn0R4+WxJlskXNWokzXpPIDKe4Ysp7/meJs2APd7e00y5qtkucBXu4/aAz/aJmT4kzfpbH1kIJney3ibNYDzcW3srIc5E4i/Xw0UI5i5ptEmf8II21hVHTiFLxmDgL9XBlobRFzVanJNTDucD1rW+Eerg8nfwDg4HFg/DlkQkhhBBCCCGEEEIIIYQQQgghhBBCCCGEEEIIIYQQQgghhBBCCCEkDQcHfwEA068zT9RMZQAAAABJRU5ErkJggg==";
	}

	initButtons() {
		this.cancelBtn = document.querySelector(`[data-id="${this.cancelRequestBtnDatasetId}"]`);
		this.cancelBtn && this.cancelBtn.addEventListener('click', this.initCancelling.bind(this));
	}

	initCancelling(){
		this.showCancelPopup();
		/*
		this.cancelBtn && this.cancelBtn.classList.add('ui-btn-wait');
		this.getCurrentStagePromise()
			.then( response => {
				if (response.status === 'success')
				{
					this.currentStage = response.data.currentStage;
					this.allowQuickCancelling = response.data.allowQuickCancelling;

					if (this.allowQuickCancelling === 'Y')
					{
						this.showCancelConfirm();
					}
					else
					{
						this.showCancelPopup();
					}

					this.cancelBtn && this.cancelBtn.classList.remove('ui-btn-wait');
				}
				else
				{
					throw new Error('Something went wrong. Unknown response status - '.response.status);
				}
			}).catch(response => {
				const error = response.errors?.[0]?.message ?? 'Something went wrong. Unknown error';
				BX.Cbit?.Mc?.Core?.MainUI?.showErrorPopup(error);
				this.cancelBtn && this.cancelBtn.classList.remove('ui-btn-wait');
			});
		*/
	}

	getCurrentStagePromise(){
		return BX.ajax.runAction('cbit.mc:ri.base.getCurrentStage', {
			sessid: BX.bitrix_sessid(),
			data: {
				itemId: this.entityId
			}
		})
	}

	showCancelConfirm() {
		BX.UI.Dialogs.MessageBox.confirm(
			BX.message('UI_DETAIL_CANCEL_CONFIRM_TITLE'),
			(messageBox) => {
				messageBox.close();
				BX.ajax.runAction('cbit.mc:ri.base.cancelRequestWithoutReason', {
					sessid: BX.bitrix_sessid(),
					data: {
						itemId: this.entityId
					}
				}).then( response => {
					if (response.status === 'success')
					{
						BX.Crm.EntityEditor.getDefault().refreshLayout();
						BX.SidePanel.Instance.reload();
						this.cancelBtn && this.cancelBtn.remove();
					}
					else
					{
						throw new Error('Something went wrong. Unknown response status - '.response.status);
					}
				}).catch(response => {
					const error = response.errors?.[0]?.message ?? 'Something went wrong. Unknown error';
					BX.Cbit?.Mc?.Core?.MainUI?.showErrorPopup(error);
				});
			},
			(messageBox) => {
				messageBox.close();
			}
		);
	}

	showCancelPopup() {
		this.cancelFormNode = this.createCancelForm();
		this.cancelFormPopup = BX.PopupWindowManager.create(
			"request-cancelling-popup",
			null,
			{
				content: this.cancelFormNode,
				width: 500,
				closeIcon: false,
				titleBar: BX.message('UI_DETAIL_CANCEL_POPUP_TITLE'),
				closeByEsc: false,
				overlay: {
					backgroundColor: 'black',
					opacity: 500
				},
				buttons: [
					new BX.PopupWindowButton({
						text: BX.message('SAVE_TEXT'),
						className: 'ui-btn ui-btn-primary',
						id: this.submitCancelFormBtnId,
						events: {
							click: () => {
								this.submitCancelForm();
							}
						}
					}),
					new BX.PopupWindowButton({
						text: BX.message('EXIT_TEXT'),
						className: 'ui-btn ui-btn-default',
						events: {
							click: () => {
								this.cancelFormPopup.close();
								this.cancelFormPopup.destroy();
								this.cancelFormPopup = null;
							}
						}
					})
				],
			}
		);
		this.cancelFormPopup.show();
	}

	createCancelForm(){
		const textareaProps = {
			name: 'CANCEL_FORM_COMMENT',
			className: 'ui-ctl-element',
		};

		if (this.isCancelReasonRequired)
		{
			textareaProps.required = true;
		}

		return BX.create('form', {
			children: [
				BX.create({
					tag: 'label',
					props: {
						className: 'ui-ctl ui-ctl-after-icon ui-ctl-dropdown'
					},
					children: [
						BX.create('span',{
							props: {
								className: 'ui-ctl-after ui-ctl-icon-angle'
							},
						}),
						BX.create({
							tag: 'span',
							props: {
								className: 'ui-ctl-tag'
							},
							text: BX.message('UI_DETAIL_CANCEL_FORM_REASON')
						}),
						BX.create({
							tag: 'select',
							props: {
								className: 'ui-ctl-element',
								name: 'CANCEL_FORM_REASON',
								required: true,
							},
							children: this.getCancelRequestReasonOptions()
						}),
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
							text: BX.message('UI_DETAIL_CANCEL_FORM_COMMENT')
						}),
						BX.create({
							tag: 'textarea',
							props: textareaProps
						}),
					]
				}),
				BX.create('input', {
					props: {
						type: 'hidden',
						name: 'ITEM_ID',
						value: this.entityId
					}
				})
			]
		});
	}

	getCancelRequestReasonOptions(){
		const options = [];
		if(typeof this.cancelReasonsList === 'object')
		{
			for(let key in this.cancelReasonsList)
			{
				options.push(BX.create('option', {
					props: {
						value: key
					},
					text: this.cancelReasonsList[key]
				}));
			}
		}
		return options;
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

	submitCancelForm() {
		if (!this.validateForm(this.cancelFormNode))
		{
			return false;
		}

		const submitBtn =  BX(this.submitCancelFormBtnId);
		submitBtn && submitBtn.classList.add("ui-btn-wait");

		const formData = new FormData(this.cancelFormNode);

		BX.ajax.runAction('cbit.mc:ri.base.cancelRequest', {
			sessid: BX.bitrix_sessid(),
			data: formData
		}).then( response => {
			if (response.status === 'success')
			{
				this.cancelFormPopup.close();
				this.cancelFormPopup.destroy();
				this.cancelFormPopup = null;
				this.cancelFormNode  = null;

				BX.Cbit?.Mc?.Core?.MainUI?.showSuccessPopup();
				if (BX.SidePanel?.Instance?.opened)
				{
					BX.SidePanel.Instance.reload();
				}
				else
				{
					window.location.reload();
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

	showUnScoredItemsListPopup() {
		this.unScoredListPopup = BX.PopupWindowManager.create(
			"not-scored-requests-popup",
			null,
			{
				content: this.getUnScoredItemsNodes(),
				width: 500,
				closeIcon: false,
				titleBar: BX.message('UI_DETAIL_UN_SCORED_LIST_POPUP_TITLE'),
				closeByEsc: false,
				overlay: {
					backgroundColor: 'black',
					opacity: 500
				}
			}
		);
		this.unScoredListPopup.show();
	}

	getUnScoredItemsNodes(){
		const items = [];
		for (const key in this.unScoredRequests)
		{
			if (this.unScoredRequests.hasOwnProperty(key))
			{
				items.push(BX.create('p',{
					props: {
						className: 'not-scored-requests-list-item'
					},
					children: [
						BX.create('span',{
							text: `${this.unScoredRequests[key].NUMBER}. ${this.unScoredRequests[key].TITLE}. `
						}),
						BX.create('a',{
							attrs: {
								href: this.unScoredRequests[key].URL
							},
							text: BX.message('UI_DETAIL_UN_SCORED_LIST_ITEM_LINK')
						}),
					]
				}));
			}
		}

		return BX.create('div', {
			props: {
				className: 'not-scored-requests-list'
			},
			children: items
		});
	}

	addCustomStyles(){
		if (!this.hasRiPerms)
		{
			document.body.classList.add('internal-staff-interface');
			if (!this.isItemCreatedByCurrentUser)
			{
				document.body.classList.add('internal-staff-interface-not-requester');
			}
		}

		if (this.isOnUnassignedStages)
		{
			document.body.classList.add('unassigned-stages-interface');
		}
	}

	hideControlPanel(){
		const editor = BX.Crm?.EntityEditor?.getDefault();
		editor && editor._toolPanel?.setVisible(false);
	}

	showControlPanel(){
		const editor = BX.Crm?.EntityEditor?.getDefault();
		editor && editor._toolPanel?.setVisible(true);
	}

	rebuildUnScoredRequests(itemId) {
		const newObj = {};
		for (const key in this.unScoredRequests) {
			if (this.unScoredRequests.hasOwnProperty(key))
			{
				if (Number(key) !== Number(itemId))
				{
					newObj[key] = this.unScoredRequests[key];
				}
			}
		}
		this.unScoredRequests = newObj;
	}
}