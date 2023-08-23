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
		this.isNew				 = options.isNew;
		this.isAdmin			 = options.isAdmin;

		this.init();
	}

	init() {
		this.initEntityDetailManager();
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
			isStageFlowActive:			 false,
			reloadOnStageChange:		 true,
		});
	}
}