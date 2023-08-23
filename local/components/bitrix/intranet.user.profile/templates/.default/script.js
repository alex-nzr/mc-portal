(function ()
{
	var namespace = BX.namespace('BX.Intranet.UserProfile');
	if (namespace.Manager)
	{
		return;
	}

	namespace.Manager = function(params)
	{
		this.init(params);
	};

	namespace.Manager.prototype = {
		init: function(params)
		{
			this.signedParameters = params.signedParameters;
			this.componentName = params.componentName;
			this.canEditProfile = params.canEditProfile === "Y";
			this.userId = params.userId || "";
			this.userStatus = params.userStatus || "";
			this.isOwnProfile = params.isOwnProfile === "Y";
			this.isSessionAdmin = params.isSessionAdmin === "Y";
			this.urls = params.urls;
			this.isExtranetUser = params.isExtranetUser === "Y";
			this.adminRightsRestricted = params.adminRightsRestricted === "Y";
			this.delegateAdminRightsRestricted = params.delegateAdminRightsRestricted === "Y";
			this.isFireUserEnabled = params.isFireUserEnabled === "Y";
			this.showSonetAdmin = params.showSonetAdmin === "Y";
			this.languageId = params.languageId;
			this.initialFields = params.initialFields;
			this.siteId = params.siteId;
			this.isCloud = params.isCloud === "Y";
			this.isRusCloud = params.isRusCloud === "Y";
			this.isCurrentUserIntegrator = params.isCurrentUserIntegrator === "Y";
			this.personalMobile = this.initialFields["PERSONAL_MOBILE"];
			this.currentTime  = params.currentTime;
			this.timeStampOffset = 0;
			this.selectorsToDelete = params.selectorsToDelete;
			this.lastLoadedProjectId = params.lastLoadedProjectId;
			this.educationTypes = params.educationTypes;

			this.entityEditorInstance = new namespace.EntityEditor({
				managerInstance: this,
				params: params
			});

			BX.ready(function () {
				this.initClock();
				this.initCvInputs(params.cvFileInputIds);
				this.hideForbiddenNodes();
				this.deleteForbiddenNodes();
				this.addCustomEvents();
				this.initAvatarSlider();
				this.initLeftColumnPosition();
				this.initTabButtons();
				this.initAddEducationButton();
				this.initSummaryBlock();

				if (!this.isOwnProfile || BX.SidePanel.Instance.isOpen())
				{
					this.initSectionMenu();
				}

				this.tagsManagerInstance = new namespace.Tags({
					managerInstance: this,
					inputNode: document.getElementById('intranet-user-profile-tags-input'),
					tagsNode: document.getElementById('intranet-user-profile-tags')
				});

				this.stressLevelManagerInstance = new namespace.StressLevel({
					managerInstance: this,
					options: params
				});

				this.gratsManagerInstance = new namespace.Grats({
					managerInstance: this,
					options: params
				});

				this.profilePostManagerInstance = new namespace.ProfilePost({
					managerInstance: this,
					options: params
				});

				this.initAvailableActions();
				this.initAvatarLoader();

				if (this.isCloud)
				{
					this.initGdpr();
				}

				var subordinateMoreButton = BX("intranet-user-profile-subordinate-more");
				if (BX.type.isDomNode(subordinateMoreButton))
				{
					BX.bind(subordinateMoreButton, "click", function () {
						this.loadMoreUsers(subordinateMoreButton);
					}.bind(this));
				}

				var managerMoreButton = BX("intranet-user-profile-manages-more");
				if (BX.type.isDomNode(subordinateMoreButton))
				{
					BX.bind(managerMoreButton, "click", function () {
						this.loadMoreUsers(managerMoreButton);
					}.bind(this));
				}

				//hack for form view button
				var bottomContainer = document.querySelector('.intranet-user-profile-bottom-controls');
				var cardButton = document.getElementById('intranet-user-profile_buttons');
				if (BX.type.isDomNode(bottomContainer) && BX.type.isDomNode(cardButton))
				{
					var cardButtonLink = cardButton.querySelector('.ui-entity-settings-link');
					cardButtonLink.setAttribute('class', 'ui-btn ui-btn-sm ui-btn-light-border ui-btn-themes');
					cardButton.parentNode.removeChild(cardButton);
					bottomContainer.appendChild(cardButtonLink);
				}
			}.bind(this));
		},

		initAvailableActions: function()
		{
			if (!this.userStatus)
				return;

			var actionElement = document.querySelector("[data-role='user-profile-actions-button']");
			if (BX.type.isDomNode(actionElement))
			{
				BX.bind(actionElement, "click", BX.proxy(function () {
					this.showActionPopup(BX.proxy_context);
				}, this));
			}
		},

		initGdpr: function ()
		{
			var gdprInputs = document.querySelectorAll("[data-role='gdpr-input']");
			gdprInputs.forEach(
				function(currentValue, currentIndex, listObj) {
					BX.bind(currentValue, "change", function () {
						this.changeGdpr(currentValue);
					}.bind(this));
				}.bind(this)
			);

			var dropdownTarget = document.querySelector('.intranet-user-profile-column-block-title-dropdown');
			BX.bind(dropdownTarget, "click", function () {
				this.animateGdprBlock(dropdownTarget);
			}.bind(this));
		},

		initAvatarLoader: function()
		{
			var resCamera = new BX.AvatarEditor({enableCamera : true});
			if (
				BX('intranet-user-profile-photo-camera')
				&& !resCamera.isCameraEnabled()
			)
			{
				BX.hide(BX('intranet-user-profile-photo-camera'));
			}

			BX.bind(BX('intranet-user-profile-photo-camera'), "click", function(){ resCamera.show('camera'); });
			BX.bind(BX('intranet-user-profile-photo-file'), "click", function(){ resCamera.show('file'); });

			BX.addCustomEvent(resCamera, "onApply", BX.proxy(function(file, canvas) {
				var formObj = new FormData();
				if (!file.name)
				{
					file.name = "tmp.png"
				}
				formObj.append('newPhoto', file, file.name);

				formObj.append('userId', this.userId);

				this.changePhoto(formObj);
			}, this));

			BX.bind(BX("intranet-user-profile-photo-remove"), "click", BX.proxy(function () {
				if (BX("intranet-user-profile-photo").style.backgroundImage !== "")
				{
					this.showConfirmPopup(BX.message("INTRANET_USER_PROFILE_PHOTO_DELETE_CONFIRM"), this.deletePhoto.bind(this));
				}
			}, this))
		},

		showActionPopup: function(bindElement)
		{
			var menuItems = [];

			if (this.showSonetAdmin)
			{
				menuItems.push({
					text: BX.message(this.isSessionAdmin ? "INTRANET_USER_PROFILE_QUIT_ADMIN_MODE" : "INTRANET_USER_PROFILE_ADMIN_MODE"),
					className: "menu-popup-no-icon",
					onclick: function () {
						this.popupWindow.close();
						__SASSetAdmin();
					}
				});
			}

			if (this.userStatus === "admin" && this.canEditProfile && !this.isOwnProfile)
			{
				menuItems.push({
					text: BX.message("INTRANET_USER_PROFILE_REMOVE_ADMIN_RIGHTS"),
					className: "menu-popup-no-icon",
					onclick: BX.proxy(function () {
						BX.proxy_context.popupWindow.close();
						this.removeAdminRights();
					}, this)
				});
			}

			if (this.userStatus === "employee" && this.canEditProfile && !this.isOwnProfile && !this.isCurrentUserIntegrator)
			{
				var itemText = BX.message("INTRANET_USER_PROFILE_SET_ADMIN_RIGHTS");
				if (this.delegateAdminRightsRestricted)
				{
					itemText+= "<span class='intranet-user-profile-lock-icon'></span>";
				}
				menuItems.push({
					html: itemText,
					onclick: BX.proxy(function () {
						BX.proxy_context.popupWindow.close();
						if (this.adminRightsRestricted)
						{
							if (this.delegateAdminRightsRestricted)
							{
								top.BX.UI.InfoHelper.show('limit_admin_admins');
							}
							else
							{
								this.showConfirmPopup(BX.message("INTRANET_USER_PROFILE_MOVE_ADMIN_RIGHTS_CONFIRM"), this.setAdminRights.bind(this));
							}
						}
						else
						{
							this.setAdminRights();
						}
					}, this)
				});
			}

			if (
				(this.userStatus === "admin" || this.userStatus === "employee" || this.userStatus === "integrator" || this.isExtranetUser)
				&& this.canEditProfile
				&& !this.isOwnProfile
				&& !BX.util.in_array(this.userStatus, ['email', 'shop' ])
			)
			{
				itemText = BX.message("INTRANET_USER_PROFILE_FIRE");
				if (!this.isFireUserEnabled && this.userStatus !== "integrator")
				{
					itemText+= "<span class='intranet-user-profile-lock-icon'></span>";
				}

				menuItems.push({
					text: itemText,
					className: "menu-popup-no-icon",
					onclick: BX.proxy(function () {
						BX.proxy_context.popupWindow.close();
						if (!this.isFireUserEnabled && this.userStatus !== "integrator")
						{
							top.BX.UI.InfoHelper.show('limit_dismiss');
						}
						else
						{
							this.showConfirmPopup(BX.message("INTRANET_USER_PROFILE_FIRE_CONFIRM"), this.fireUser.bind(this));
						}
					}, this)
				});
			}

			if (this.userStatus === "fired" && this.canEditProfile && !this.isOwnProfile)
			{
				menuItems.push({
					text: BX.message("INTRANET_USER_PROFILE_HIRE"),
					className: "menu-popup-no-icon",
					onclick: BX.proxy(function () {
						BX.proxy_context.popupWindow.close();
						this.showConfirmPopup(BX.message("INTRANET_USER_PROFILE_HIRE_CONFIRM"), this.hireUser.bind(this));
					}, this)
				});
			}

			if (this.userStatus === "invited" && this.canEditProfile && !this.isOwnProfile)
			{
				menuItems.push({
					text: BX.message("INTRANET_USER_PROFILE_REINVITE"),
					className: "menu-popup-no-icon",
					onclick: BX.proxy(function () {
						BX.proxy_context.popupWindow.close();
						this.reinviteUser();
					}, this)
				});

				menuItems.push({
					text: BX.message("INTRANET_USER_PROFILE_DELETE"),
					className: "menu-popup-no-icon",
					onclick: BX.proxy(function () {
						BX.proxy_context.popupWindow.close();
						this.showConfirmPopup(BX.message("INTRANET_USER_PROFILE_DELETE_CONFIRM"), this.deleteUser.bind(this));
					}, this)
				});
			}

			if (this.isExtranetUser && this.canEditProfile && !this.isOwnProfile && this.isCloud)
			{
				menuItems.push({
					text: BX.message("INTRANET_USER_PROFILE_MOVE_TO_INTRANET"),
					className: "menu-popup-no-icon",
					onclick: BX.proxy(function () {
						BX.proxy_context.popupWindow.close();
						this.moveToIntranet();
					}, this)
				});
			}

			if (
				this.isCloud
				&& this.canEditProfile && !this.isOwnProfile
				&& this.userStatus !== "integrator"
			)
			{
				menuItems.push({
					text: BX.message("INTRANET_USER_PROFILE_SET_INEGRATOR_RIGHTS"),
					className: "menu-popup-no-icon",
					onclick: BX.proxy(function () {
						BX.proxy_context.popupWindow.close();
						this.showConfirmPopup(BX.message("INTRANET_USER_PROFILE_SET_INTEGRATOR_RIGHTS_CONFIRM"), this.setIntegratorRights.bind(this));
					}, this)
				});
			}

			BX.PopupMenu.show("user-profile-action-popup", bindElement, menuItems,
			{
				offsetTop: 0,
				offsetLeft: 10,
				angle: true,
				events: {
					onPopupClose: function ()
					{
						BX.PopupMenu.destroy();
					}
				}
			});
		},

		showConfirmPopup: function(text, confirmCallback)
		{
			BX.PopupWindowManager.create({
				id: "intranet-user-profile-confirm-popup",
				content:
					BX.create("div", {
						props : {
							style : "max-width: 450px"
						},
						html: text
					}),
				closeIcon : false,
				lightShadow : true,
				offsetLeft : 100,
				overlay : false,
				contentPadding: 10,
				buttons: [
					new BX.UI.CreateButton({
						text: BX.message("INTRANET_USER_PROFILE_YES"),
						events: {
							click: function (button) {
								button.setWaiting();
								this.context.close();
								confirmCallback();
							}
						}
					}),
					new BX.UI.CancelButton({
						text : BX.message("INTRANET_USER_PROFILE_NO"),
						events : {
							click: function () {
								this.context.close();
							}
						}
					})
				],
				events : {
					onPopupClose: function ()
					{
						this.destroy();
					}
				}
			}).show();
		},

		showFireInvitedUserPopup: function(callback)
		{
			BX.PopupWindowManager.create({
				id: "intranet-user-profile-fire-invited-popup",
				content:
					BX.create("div", {
						props : {
							style : "max-width: 450px"
						},
						html: BX.message('INTRANET_USER_PROFILE_FIRE_INVITED_USER')
					}),
				closeIcon : true,
				lightShadow : true,
				offsetLeft : 100,
				overlay : false,
				contentPadding: 10,
				buttons: [
					new BX.UI.CreateButton({
						text: BX.message("INTRANET_USER_PROFILE_YES"),
						events: {
							click: function (button) {
								button.setWaiting();
								this.context.close();
								callback();
							}
						}
					}),

					new BX.UI.CancelButton({
						text : BX.message("INTRANET_USER_PROFILE_NO"),
						events : {
							click: function () {
								this.context.close();
							}
						}
					})
				]
			}).show();
		},

		showErrorPopup: function(error)
		{
			if (!error)
			{
				return;
			}

			BX.PopupWindowManager.create({
				id: "intranet-user-profile-error-popup",
				content:
					BX.create("div", {
						props : {
							style : "max-width: 450px"
						},
						html: error
					}),
				closeIcon : true,
				lightShadow : true,
				offsetLeft : 100,
				overlay : false,
				contentPadding: 10
			}).show();
		},

		loadMoreUsers: function(button)
		{
			if (!BX.type.isDomNode(button))
			{
				return;
			}

			var block = button.parentNode;

			var items = block.querySelectorAll("[data-role='user-profile-item']");
			var itemsLength = items.length;
			for (var i = 0; i < 4 && i < itemsLength; i++)
			{
				items[i].style.display = "inline-block";
				items[i].setAttribute("data-role", "");
			}

			if (itemsLength - 4 <= 0)
			{
				button.style.display = "none";
			}
			else
			{
				BX.findChild(button).innerHTML = itemsLength - 4;
			}
		},

		changePhoto: function(dataObj)
		{
			var loader = this.showLoader({node: BX("intranet-user-profile-photo"), loader: null, size: 100});

			BX.ajax.runComponentAction(this.componentName, "loadPhoto", {
				signedParameters: this.signedParameters,
				mode: 'ajax',
				data: dataObj
			}).then(function (response) {
				if (response.data)
				{
					(top || window).BX.onCustomEvent('BX.Intranet.UserProfile:Avatar:changed', [{url: response.data}]);
					BX("intranet-user-profile-photo").style = "background-image: url('" + response.data + "'); background-size: cover;";
				}

				this.hideLoader({loader: loader});
			}.bind(this), function (response) {
				this.hideLoader({loader: loader});
				this.showErrorPopup(response["errors"][0].message);
			}.bind(this));
		},

		deletePhoto: function(fileId = false)
		{
			var loader = this.showLoader({node: BX("intranet-user-profile-photo"), loader: null, size: 100});

			const data = fileId ? {fileId: fileId} : {};

			BX.ajax.runComponentAction(this.componentName, "deletePhoto", {
				signedParameters: this.signedParameters,
				mode: 'ajax',
				data: data
			}).then(function (response) {

				if (!fileId)
				{
					BX("intranet-user-profile-photo").style = "";
				}

				this.hideLoader({loader: loader});
				if (response.data?.DELETED_FILE_ID)
				{
					const slideActions = document.querySelector(`[data-id="${response.data.DELETED_FILE_ID}"] .intranet-user-profile-photo-slider__item-actions`);
					if (slideActions){
						slideActions.remove();
						if (fileId)
						{
							this.showSuccessPopup(BX.message("SLIDE_DELETE_SUCCESSFUL"));
						}
					}
				}
			}.bind(this), function (response) {
				this.hideLoader({loader: loader});
				this.showErrorPopup(response["errors"][0].message);
			}.bind(this));
		},

		setAdminRights: function()
		{
			var loader = this.showLoader({node: BX("intranet-user-profile-wrap"), loader: null, size: 100});

			BX.ajax.runComponentAction(this.componentName, "setAdminRights", {
				signedParameters: this.signedParameters,
				mode: 'ajax',
				data: {}
			}).then(function (response) {
				if (response.data === true)
				{
					BX.SidePanel.Instance.postMessageTop(window, 'userProfileSlider::reloadList', {});
					location.reload();
				}
				else
				{
					this.hideLoader({loader: loader});
					this.showErrorPopup("Error");
				}
			}, function (response) {
				this.hideLoader({loader: loader});
				this.showErrorPopup(response["errors"][0].message);
			}.bind(this));
		},

		setIntegratorRights: function()
		{
			var loader = this.showLoader({node: BX("intranet-user-profile-wrap"), loader: null, size: 100});

			BX.ajax.runComponentAction(this.componentName, "setIntegratorRights", {
				signedParameters: this.signedParameters,
				mode: 'ajax',
				data: {}
			}).then(function (response) {
				if (response.data === true)
				{
					location.reload();
				}
				else
				{
					this.hideLoader({loader: loader});
					this.showErrorPopup("Error");
				}
			}, function (response) {
				this.hideLoader({loader: loader});
				this.showErrorPopup(response["errors"][0].message);
			}.bind(this));
		},

		removeAdminRights: function()
		{
			var loader = this.showLoader({node: BX("intranet-user-profile-wrap"), loader: null, size: 100});

			BX.ajax.runComponentAction(this.componentName, "removeAdminRights", {
				signedParameters: this.signedParameters,
				mode: 'ajax',
				data: {}
			}).then(function (response) {
				if (response.data === true)
				{
					BX.SidePanel.Instance.postMessageTop(window, 'userProfileSlider::reloadList', {});
					location.reload();
				}
				else
				{
					this.hideLoader({loader: loader});
					this.showErrorPopup("Error");
				}
			}, function (response) {
				this.hideLoader({loader: loader});
				this.showErrorPopup(response["errors"][0].message);
			}.bind(this));
		},

		fireUser: function()
		{
			var loader = this.showLoader({node: BX("intranet-user-profile-wrap"), loader: null, size: 100});

			BX.ajax.runComponentAction(this.componentName, "fireUser", {
				signedParameters: this.signedParameters,
				mode: 'ajax',
				data: {}
			}).then(function (response) {
				if (response.data === true)
				{
					BX.SidePanel.Instance.postMessageTop(window, 'userProfileSlider::reloadList', {});
					location.reload();
				}
				else
				{
					this.hideLoader({loader: loader});
					this.showErrorPopup("Error");
				}
			}, function (response) {
				this.hideLoader({loader: loader});
				this.showErrorPopup(response["errors"][0].message);
			}.bind(this));
		},

		hireUser: function()
		{
			var loader = this.showLoader({node: BX("intranet-user-profile-wrap"), loader: null, size: 100});

			BX.ajax.runComponentAction(this.componentName, "hireUser", {
				signedParameters: this.signedParameters,
				mode: 'ajax',
				data: {}
			}).then(function (response) {
				if (response.data === true)
				{
					location.reload();
				}
				else
				{
					this.hideLoader({loader: loader});
					this.showErrorPopup("Error");
				}
			}, function (response) {

				this.hideLoader({loader: loader});
			}.bind(this));
		},

		deleteUser: function()
		{
			var loader = this.showLoader({node: BX("intranet-user-profile-wrap"), loader: null, size: 100});

			BX.ajax.runComponentAction(this.componentName, "deleteUser", {
				signedParameters: this.signedParameters,
				mode: 'ajax',
				data: {}
			}).then(function (response) {
				if (response.data === true)
				{
					BX.SidePanel.Instance.postMessageTop(window, 'userProfileSlider::reloadList', {});
					BX.SidePanel.Instance.close();
				}
				else
				{
					this.hideLoader({loader: loader});
					this.showErrorPopup("Error");
				}
			}, function (response) {
				this.hideLoader({loader: loader});
				//this.showErrorPopup(response["errors"][0].message);
				this.showFireInvitedUserPopup(this.fireUser.bind(this));
			}.bind(this));
		},

		reinviteUser: function()
		{
			var loader = this.showLoader({node: BX("intranet-user-profile-wrap"), loader: null, size: 100});

			BX.ajax.runAction('intranet.controller.invite.reinvite', {
				data: {
					params: {
						userId: this.userId,
						extranet: (this.isExtranetUser == "Y" ? 'Y' : 'N')
					}
				}
			}).then(function (response) {
				this.hideLoader({loader: loader});
				if (response.data.result)
				{
					BX.PopupWindowManager.create('intranet-user-profile-invited-popup', null, {
						content: '<p>'+BX.message("INTRANET_USER_PROFILE_REINVITE_SUCCESS")+'</p>',
						offsetLeft:27,
						offsetTop:7,
						autoHide:true
					}).show();
				}
			}.bind(this), function (response) {
				this.hideLoader({loader: loader});
			}.bind(this));
		},

		moveToIntranet: function(isEmail)
		{
			if (isEmail !== true)
				isEmail = false;

			BX.PopupWindowManager.create("BXExtranet2Intranet", null, {
				autoHide: false,
				zIndex: 0,
				offsetLeft: 0,
				offsetTop: 0,
				overlay : true,
				draggable: {restrict:true},
				closeByEsc: true,
				titleBar: BX.message("INTRANET_USER_PROFILE_MOVE_TO_INTRANET_TITLE"),
				closeIcon: false,
				width: 500,
				buttons: [
					new BX.UI.CreateButton({
						text: BX.message("INTRANET_USER_PROFILE_MOVE"),
						events: {
							click: BX.proxy(function () {
								var button = BX.proxy_context;
								BX.addClass(button.button, "ui-btn-wait");

								var form = BX('moveToIntranetForm');
								if(BX.type.isDomNode(form))
								{
									BX.ajax.runComponentAction(this.componentName, "moveToIntranet", {
										signedParameters: this.signedParameters,
										mode: 'ajax',
										data: {
											departmentId: BX("toIntranetDepartment").value,
											isEmail: isEmail ? "Y" : "N"
										}
									}).then(function (response) {
										if (response.data)
										{
											button.context.setContent(response.data);
											button.context.setButtons([
												new BX.UI.CloseButton({
													events : {
														click: function () {
															location.reload();
														}
													}
												})
											]);
										}
									}, function (response) {
										BX.removeClass(button.button, "ui-btn-wait");

										var form = BX('moveToIntranetForm');
										if(BX.type.isDomNode(form) && !BX("moveToIntranetError"))
										{
											var errorBlock = BX.create("div", {
												attrs: {
													id: "moveToIntranetError",
													class: "ui-alert ui-alert-danger ui-alert-icon-danger"
												},
												children: [
													BX.create("span", {
														attrs: {class: "ui-alert-message"},
														html: response["errors"][0].message
													})
												]
											});
											form.insertBefore(errorBlock, BX.findChild(form));
										}

									}.bind(this));
								}
							}, this)
						}
					}),

					new BX.UI.CancelButton({
						events : {
							click: function () {
								this.context.close();
							}
						}
					})
				],
				events: {
					onAfterPopupShow: BX.proxy(function()
					{
						var popup = BX.proxy_context;
						popup.setContent('<div style="width:450px;height:230px"></div>');

						var loader = this.showLoader({node: popup.contentContainer, loader: null, size: 100});

						BX.ajax.post(
							'/bitrix/tools/b24_extranet2intranet.php',
							{
								USER_ID: this.userId,
								IS_EMAIL: isEmail ? 'Y' : 'N'
							},
							BX.proxy(function(result)
							{
								this.hideLoader({loader: loader});
								popup.setContent(result);
							}, this)
						);
					}, this)
				}
			}).show();
		},

		showLoader: function(params)
		{
			var loader = null;

			if (params.node)
			{
				if (params.loader === null)
				{
					loader = new BX.Loader({
						target: params.node,
						size: params.hasOwnProperty("size") ? params.size : 40
					});
				}
				else
				{
					loader = params.loader;
				}

				loader.show();
			}

			return loader;
		},

		hideLoader: function(params)
		{
			if (params.loader !== null)
			{
				params.loader.hide();
			}

			if (params.node)
			{
				BX.cleanNode(params.node);
			}

			if (params.loader !== null)
			{
				params.loader = null;
			}
		},

		processSliderCloseEvent: function(params)
		{
			BX.addCustomEvent('SidePanel.Slider:onMessage', function(event) {

				if (event.getSlider() != BX.SidePanel.Instance.getSliderByWindow(window))
				{
					return;
				}

				if (event.getEventId() != 'SidePanel.Wrapper:onClose')
				{
					return;
				}

				var data = event.getData();

				if (!BX.type.isNotEmptyObject(data.sliderData))
				{
					return;
				}

				var
					entityType = data.sliderData.get('entityType'),
					entityId = data.sliderData.get('entityId');

				if (
					BX.type.isNotEmptyString(entityType)
					&& entityType == params.entityType
					&& entityId == this.userId
				)
				{
					params.callback();
				}
			}.bind(this));
		},

		changeGdpr: function (inputNode)
		{
			var requestData = {
				type: inputNode.name,
				value: inputNode.checked ? "Y" : "N"
			};

			BX.ajax.runComponentAction(this.componentName, "changeGdpr", {
				signedParameters: this.signedParameters,
				mode: 'class',
				data: requestData
			}).then(function (response) {

			}, function (response) {

			}.bind(this));
		},

		animateGdprBlock: function (element)
		{
			var sliderTarget = document.querySelector('[data-role="' + element.getAttribute('for') + '"]');

			if(element.classList.contains('intranet-user-profile-column-block-title-dropdown--open'))
			{
				element.classList.remove('intranet-user-profile-column-block-title-dropdown--open');
				sliderTarget.style.height = null;
			}
			else
			{
				element.classList.add('intranet-user-profile-column-block-title-dropdown--open');
				sliderTarget.style.height = sliderTarget.firstElementChild.offsetHeight + 'px';
			}
		},

		initClock: function ()
		{
			const timeNode = BX("intranet-user-profile-current-time-block");
			if(timeNode)
			{
				const hoursNode = BX.create({
					tag:'span',
					props: {
						className: "intranet-user-profile-current-time-block-hours"
					},
					text: ''
				});
				const minutesNode = BX.create({
					tag:'span',
					props: {
						className: "intranet-user-profile-current-time-block-minutes"
					},
					text: ''
				});
				const separatorNode = BX.create({
					tag:'span',
					props: {
						className: "intranet-user-profile-current-time-block-separator"
					},
					text: ':'
				});

				this.changeCurrentTime(hoursNode, minutesNode);

				timeNode.append(hoursNode, separatorNode, minutesNode);

				setInterval(() => {
					this.timeStampOffset = this.timeStampOffset + 1;
					this.changeCurrentTime(hoursNode, minutesNode);
				}, 1000);
			}
		},

		changeCurrentTime: function (hoursNode, minutesNode)
		{
			const currentDate = this.isOwnProfile ? new Date() : new Date(this.currentTime);

			if (!this.isOwnProfile)
			{
				currentDate.setSeconds(this.timeStampOffset + currentDate.getSeconds());
			}

			let currentHours = Number(currentDate.getHours());
			let currentMinutes = Number(currentDate.getMinutes());
			const hours = Number(hoursNode.textContent);
			const minutes = Number(minutesNode.textContent);

			if (currentHours < 10){
				currentHours = `0${currentHours}`
			}
			if (currentHours !== hours)
			{
				hoursNode.textContent = currentHours;
			}

			if (currentMinutes < 10){
				currentMinutes = `0${currentMinutes}`
			}
			if (currentMinutes !== minutes)
			{
				minutesNode.textContent = currentMinutes;
			}
		},

		initCvInputs(params) {
			if (typeof params === 'object')
			{
				for (const key in params)
				{
					if (params.hasOwnProperty(key))
					{
						const input = BX(params[key]);
						if (input)
						{
							input.addEventListener('change', (e) => {
								const file = e.target?.files?.[0];
								if (file)
								{
									let formData = new FormData();
									formData.append(`UF_${key.toUpperCase()}_CV`, file);

									const loader = this.showLoader({
										node: BX("intranet-user-profile-photo"),
										loader: null,
										size: 100
									});

									BX.ajax.runComponentAction(this.componentName, "loadCvFile", {
										signedParameters: this.signedParameters,
										mode: 'ajax',
										data: formData
									}).then( response => {
										if (response.status === 'success')
										{
											if (typeof response.data === 'object')
											{
												for (const dataKey in response.data)
												{
													if (response.data.hasOwnProperty(dataKey))
													{
														const link = BX(`${dataKey}_DOWNLOAD_LINK`);
														if (link)
														{
															link.setAttribute('href', response.data[dataKey]);
															link.removeAttribute('style');
															link.removeAttribute('onclick');
															link.textContent = link.textContent.replace(
																BX.message('FILE_NOT_UPLOADED'),
																''
															);
														}
													}
												}
											}
											this.showSuccessPopup();
										}
										else
										{
											throw new Error('Something went wrong. Unknown response status.');
										}
										this.hideLoader({loader: loader});

									}).catch(response => {
										const error = response.errors?.[0]?.message ?? 'Something went wrong. Unknown error';
										this.showErrorPopup(error);
										this.hideLoader({loader: loader});
									});
								}
							});
						}
					}
				}
			}
		},

		showSuccessPopup: function (message = false){
			if (!message)
			{
				message = BX.message("OPERATION_SUCCESSFUL");
			}
			BX.UI.Dialogs.MessageBox.alert( message, (messageBox) => {
				messageBox.close();
			});
		},

		hideForbiddenNodes() {
			const style = document.createElement('style');
			style.textContent = `${this.selectorsToDelete.join(',')} {display: none;}`
			document.body.prepend(style);
		},

		deleteForbiddenNodes() {
			if (Array.isArray(this.selectorsToDelete))
			{
				this.selectorsToDelete.forEach(selector => {
					const node = document.querySelector(selector);
					if (node){
						node.remove();
					}
				});
			}
		},

		addCustomEvents() {
			window.addEventListener('load', () => {
				this.correctTabsHeight();
			});
			BX.addCustomEvent('BX.UI.EntityEditorField:onLayout', (event) => {
				this.correctTabsHeight();
			});
			BX.addCustomEvent('onAjaxSuccessFinish', () => {
				this.deleteForbiddenNodes();
				this.correctTabsHeight();
			});
			BX.Event.EventEmitter.subscribe('BX.Main.Popup:onDestroy', () => {
				this.deleteForbiddenNodes();
			});
		},

		initAvatarSlider() {
			const sliderNode = BX('intranet-user-profile-photo-slider');
			const openBtn 	 = BX('intranet-user-profile-photo-slider-show');
			const overlay 	 = BX('intranet-user-profile-photo-slider-overlay');
			if (sliderNode && openBtn && overlay)
			{
				const avatarSlider = new ItcSimpleSlider('#intranet-user-profile-photo-slider', {
					loop: true,
					autoplay: false,
					interval: 5000,
					swipe: true,
				});

				sliderNode.addEventListener('click', (e) => {
					e.stopPropagation();
				});

				openBtn.addEventListener('click', () => {
					overlay.classList.add('active');
				});

				overlay.addEventListener('click', (e) => {
					if (e.currentTarget?.getAttribute('id') === 'intranet-user-profile-photo-slider-overlay')
					{
						overlay.classList.remove('active');
					}
				});

				const deleteButtons = overlay.querySelectorAll('button[data-action="delete_file"]');
				deleteButtons.length && deleteButtons.forEach(btn => {
					btn.addEventListener('click', () => {
						const slide = btn.closest('.intranet-user-profile-photo-slider__item');
						if (slide){
							const fileId = slide.dataset.id;
							this.showConfirmPopup(BX.message("INTRANET_USER_PROFILE_PHOTO_DELETE_CONFIRM"), () => {
								this.deletePhoto(fileId)
							});
						}
					});
				});

				const setAvatarButtons = overlay.querySelectorAll('button[data-action="set_as_avatar"]');
				setAvatarButtons.length && setAvatarButtons.forEach(btn => {
					btn.addEventListener('click', () => {
						const slide = btn.closest('.intranet-user-profile-photo-slider__item');
						if (slide){
							const fileId = slide.dataset.id;
							if(Number(fileId) > 0)
							{
								const formObj = new FormData();
								formObj.append('newPhotoFromGalleryId', fileId);
								formObj.append('userId', this.userId);
								this.changePhoto(formObj);

								const oldCurrentAvatar = overlay.querySelector(`[data-avatar="Y"]`);
								oldCurrentAvatar && oldCurrentAvatar.removeAttribute('data-avatar');
								slide.setAttribute('data-avatar', "Y");
							}
						}
					});
				});
			}
		},

		initLeftColumnPosition() {
			window.addEventListener('load', () => {
				this.correctLeftColumnBlocksPosition();
			});

			BX.addCustomEvent('BX.UI.EntityEditorField:onLayout', (event) => {
				if (event._id === 'UF_STAFFING_MANAGER' || event._id === 'UF_DGL'|| event._id === 'UF_USER_AVAILABLE')
				{
					this.correctLeftColumnBlocksPosition();
				}
			});

			if (!this.customControlEventsAdded)
			{
				const buttons = document.querySelectorAll(`.ui-entity-section-control .ui-btn`);
				if (buttons.length)
				{
					buttons.forEach((btn) => {
						btn.addEventListener('click', () => {
							this.correctLeftColumnBlocksPosition();
						});
					});
					this.customControlEventsAdded = true;
				}
			}
		},

		correctLeftColumnBlocksPosition()
		{
			const leftFormBlock = document.querySelector(`[data-cid="left_column"]`);
			const underBlock = document.querySelector('.intranet-user-profile-column-block.block-with-large-margin');
			const profileBlock = BX('intranet-user-profile-main-block');
			if (leftFormBlock && underBlock && profileBlock)
			{
				setTimeout(()=>{
					const leftBlockData = leftFormBlock.getBoundingClientRect();
					const profileBlockData = profileBlock.getBoundingClientRect();
					leftFormBlock.style.top = `${profileBlockData.height + 22}px`;
					underBlock.style.marginTop = `${leftBlockData.height + 36}px`;
				}, 500)
			}

			const editBtn = document.querySelector(`[data-cid="left_column"] .ui-entity-editor-header-actions`);
			if (editBtn)
			{
				editBtn.addEventListener('click', () => {
					this.correctLeftColumnBlocksPosition();
				});
			}
		},

		initSectionMenu() {
			const menuWrapper = document.querySelector('.main-buttons-inner-container');
			if (menuWrapper)
			{
				const items = menuWrapper.querySelectorAll('.main-buttons-item');
				items.length && items.forEach((item) => {
					if (!(item.dataset.id === 'calendar') && !(item.dataset.id === 'profile'))
					{
						item.remove();
					}
				});
			}
		},

		initTabButtons() {
			const tabControlNode = BX('intranet-user-profile-tab-control');
			if (tabControlNode)
			{
				this.tabButtons = tabControlNode.querySelectorAll('button');
				this.tabButtons?.length && this.tabButtons.forEach(btn => {
					btn.addEventListener('click', () => {
						const tabId = btn.dataset.target;
						this.disableTabButtons();
						btn.classList.add('active');
						const tabNode = BX(`intranet-user-profile-tab-${tabId}`);
						if (tabNode)
						{
							tabNode.classList.add('active');
							this.disableTabs(tabNode.getAttribute('id'));
						}
					});
				});
			}
		},

		disableTabs(excludeId = '') {
			if (!this.tabs)
			{
				this.setTabs();
			}
			this.tabs?.length && this.tabs.forEach(tab => {
				const tabId = tab.getAttribute('id');
				if (tabId !== excludeId)
				{
					tab.classList.remove('active');
				}
			});
		},

		disableTabButtons() {
			this.tabButtons?.length && this.tabButtons.forEach(btn => {
				btn.classList.remove('active');
			});
		},

		correctTabsHeight() {
			if (!this.tabs)
			{
				this.setTabs();
			}

			if (!this.tabsWrapper)
			{
				this.tabsWrapper = document.querySelector('.intranet-user-profile-column-right');
			}

			/*let maxHeight = 0;
			this.tabs.length && this.tabs.forEach(tab => {
				const tabData = tab.getBoundingClientRect();
				if (Number(tabData.height) > maxHeight)
				{
					maxHeight = Number(tabData.height);
				}
			});

			this.tabsWrapper && (this.tabsWrapper.style.minHeight = `${maxHeight}px`);*/
		},

		setTabs(){
			this.tabs = document.querySelectorAll('.intranet-user-profile-tab, #intranet-user-profile-tab-general');
		},

		initAddEducationButton() {
			const btn = BX('intranet-user-profile-edu-add-btn');
			btn && btn.addEventListener('click', () => {
				if (!this.educationForm)
				{
					this.educationForm = this.createEducationForm();
				}

				if (!this.educationPopup)
				{
					const that = this;
					that.educationPopup = BX.PopupWindowManager.create(
						"intranet-user-profile-education-add-popup",
						null,
						{
							content: that.educationForm,
							width: 700,
							closeIcon: {
								opacity: 1
							},
							titleBar: BX.message('INTRANET_USER_PROFILE_EDU_POPUP_TITLE'),
							closeByEsc: true,
							overlay: {
								backgroundColor: 'black',
								opacity: 500
							},
							buttons: [
								new BX.PopupWindowButton({
									text: BX.message('INTRANET_USER_PROFILE_SAVE'),
									id: 'intranet-user-profile-edu-popup-save-btn',
									className: 'ui-btn ui-btn-primary',
									events: {
										click: function() {
											that.submitEducationForm();
										}
									}
								}),
								new BX.PopupWindowButton({
									text: BX.message('INTRANET_USER_PROFILE_CLOSE'),
									id: 'intranet-user-profile-edu-popup-cancel-btn',
									className: 'ui-btn ui-btn-success',
									events: {
										click: function() {
											that.educationPopup.close();
										}
									}
								})
							],
						}
					);
				}

				this.educationPopup && this.educationPopup.show();
				this.initPopupFormCalendarSelectors();
			});
		},

		createEducationForm() {
			return BX.create({
				tag: 'form',
				props: {
					id: 'intranet-user-profile-edu-add-form'
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
								text: BX.message('EDUCATION_TYPE')
							}),
							BX.create({
								tag: 'select',
								props: {
									id: 'EDUCATION_TYPE_UUID',
									className: 'ui-ctl-element',
									name: 'EDUCATION_TYPE_UUID',
									required: 'true',
								},
								children: this.createEducationTypeNodes()
							}),
						]
					}),

					BX.create({
						tag: 'label',
						props: {
							className: 'ui-ctl ui-ctl-textbox ui-ctl-inline'
						},
						children: [
							BX.create({
								tag: 'span',
								props: {
									className: 'ui-ctl-tag'
								},
								text: BX.message('INSTITUTION_RU')
							}),
							BX.create({
								tag: 'input',
								props: {
									id: 'INSTITUTION_RU',
									type: 'text',
									className: 'ui-ctl-element',
									name: 'INSTITUTION_RU',
									required: 'true',
								}
							}),
						]
					}),

					BX.create({
						tag: 'label',
						props: {
							className: 'ui-ctl ui-ctl-textbox ui-ctl-inline'
						},
						children: [
							BX.create({
								tag: 'span',
								props: {
									className: 'ui-ctl-tag'
								},
								text: BX.message('INSTITUTION_EN')
							}),
							BX.create({
								tag: 'input',
								props: {
									id: 'INSTITUTION_EN',
									type: 'text',
									className: 'ui-ctl-element',
									name: 'INSTITUTION_EN',
									required: 'true',
								}
							}),
						]
					}),

					BX.create({
						tag: 'label',
						props: {
							className: 'ui-ctl ui-ctl-textbox ui-ctl-inline'
						},
						children: [
							BX.create({
								tag: 'span',
								props: {
									className: 'ui-ctl-tag'
								},
								text: BX.message('SPECIALTY_RU')
							}),
							BX.create({
								tag: 'input',
								props: {
									id: 'SPECIALTY_RU',
									type: 'text',
									className: 'ui-ctl-element',
									name: 'SPECIALTY_RU',
									required: 'true',
								}
							}),
						]
					}),

					BX.create({
						tag: 'label',
						props: {
							className: 'ui-ctl ui-ctl-textbox ui-ctl-inline'
						},
						children: [
							BX.create({
								tag: 'span',
								props: {
									className: 'ui-ctl-tag'
								},
								text: BX.message('SPECIALTY_EN')
							}),
							BX.create({
								tag: 'input',
								props: {
									id: 'SPECIALTY_EN',
									type: 'text',
									className: 'ui-ctl-element',
									name: 'SPECIALTY_EN',
									required: 'true',
								}
							}),
						]
					}),

					BX.create({
						tag: 'label',
						props: {
							className: 'ui-ctl ui-ctl-textbox ui-ctl-inline'
						},
						children: [
							BX.create({
								tag: 'span',
								props: {
									className: 'ui-ctl-tag'
								},
								text: BX.message('QUALIFICATION_RU')
							}),
							BX.create({
								tag: 'input',
								props: {
									id: 'QUALIFICATION_RU',
									type: 'text',
									className: 'ui-ctl-element',
									name: 'QUALIFICATION_RU',
									required: 'true',
								}
							}),
						]
					}),

					BX.create({
						tag: 'label',
						props: {
							className: 'ui-ctl ui-ctl-textbox ui-ctl-inline'
						},
						children: [
							BX.create({
								tag: 'span',
								props: {
									className: 'ui-ctl-tag'
								},
								text: BX.message('QUALIFICATION_EN')
							}),
							BX.create({
								tag: 'input',
								props: {
									id: 'QUALIFICATION_EN',
									type: 'text',
									className: 'ui-ctl-element',
									name: 'QUALIFICATION_EN',
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
									className: 'ui-ctl-tag',
								},
								text: BX.message('DATE_BEGIN_STUDYING')
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
									id: 'DATE_BEGIN_STUDYING',
									type: 'text',
									className: 'ui-ctl-element',
									name: 'DATE_BEGIN_STUDYING',
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
								text: BX.message('DATE_END_STUDYING')
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
									id: 'DATE_END_STUDYING',
									type: 'text',
									className: 'ui-ctl-element',
									name: 'DATE_END_STUDYING',
									required: 'true',
								}
							}),
						]
					}),

					BX.create({
						tag: 'label',
						props: {
							className: 'ui-ctl ui-ctl-checkbox'
						},
						children: [
							BX.create({
								tag: 'input',
								props: {
									id: 'OUTSIDE_RUSSIA',
									type: 'checkbox',
									className: 'ui-ctl-element',
									name: 'OUTSIDE_RUSSIA',
									value: 'Y'
								}
							}),
							BX.create({
								tag: 'span',
								props: {
									className: 'ui-ctl-label-text'
								},
								text: BX.message('OUTSIDE_RUSSIA')
							}),
						]
					}),
				]
			});
		},

		createEducationTypeNodes() {
			const nodes = [];
			if (typeof this.educationTypes === 'object')
			{
				for (const key in this.educationTypes)
				{
					if (this.educationTypes.hasOwnProperty(key))
					{
						nodes.push(
							BX.create({
								tag: 'option',
								props: {
									value: this.educationTypes[key]['UUID'],
								},
								html: `${this.educationTypes[key]['DESCRIPTION_RU']}<br>
									   ${this.educationTypes[key]['DESCRIPTION_EN']}`
							})
						);
					}
				}
			}
			return nodes;
		},

		submitEducationForm(){
			if (!this.validateEducationForm(this.educationForm))
			{
				return false;
			}

			const formData = new FormData(this.educationForm);

			BX.ajax.runComponentAction(this.componentName, "addEmployeeEducation", {
				signedParameters: this.signedParameters,
				mode: 'ajax',
				data: formData
			}).then( response => {
				if (response.status === 'success')
				{
					this.educationPopup.close();
					this.educationPopup.destroy();
					this.educationPopup = null;
					this.educationForm  = null;
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

		initPopupFormCalendarSelectors() {
			const nodes = [
				BX('DATE_BEGIN_STUDYING'), BX('DATE_END_STUDYING')
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

		validateEducationForm(educationForm) {
			let result = true;

			const fields = educationForm.querySelectorAll('input, select');
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

		initSummaryBlock() {
			this.summaryBlock 	  = BX('intranet-user-profile-summary-edit-block');
			this.summaryValue 	  = BX('intranet-user-profile-summary-value');
			this.summaryForm 	  = BX('intranet-user-profile-summary-edit-form');
			this.summaryEditBtn   = BX('intranet-user-profile-summary-edit-btn');
			this.summarySubmitBtn = BX('intranet-user-profile-summary-submit-btn');
			this.summaryTextarea  = BX('intranet-user-profile-summary-textarea');

			if (this.summaryBlock && this.summaryForm && this.summaryValue
				&& this.summaryEditBtn	&& this.summarySubmitBtn && this.summaryTextarea
			){
				this.summaryTextarea.addEventListener('input', (e) => {
					let text = e.target.value;
					if (text.indexOf('<style>') !== false || text.indexOf('<script>') !== false)
					{
						text = text.replace('<style>', '');
						text = text.replace('<script>', '');
						text = text.replace('</style>', '');
						text = text.replace('</script>', '');
					}
					this.summaryValue.innerHTML = text;
				});
				this.summaryEditBtn.addEventListener('click', () => {
					this.summaryBlock.classList.add('edit-mode');
					this.summaryEditBtn.classList.add('ui-btn-disabled');
				});
				this.summarySubmitBtn.addEventListener('click', () => {
					this.summarySubmitBtn.classList.add('ui-btn-wait');
					BX.ajax.runComponentAction(this.componentName, "updateSummaryField", {
						signedParameters: this.signedParameters,
						mode: 'ajax',
						data: {
							newSummaryText: this.summaryTextarea.value
						}
					}).then( response => {
						if (response.status === 'success')
						{
							this.summarySubmitBtn.classList.remove('ui-btn-wait');
							this.summaryBlock.classList.remove('edit-mode');
							this.summaryEditBtn.classList.remove('ui-btn-disabled');
						}
						else
						{
							throw new Error('Something went wrong. Unknown response status.');
						}
					}).catch(response => {
						const error = response.errors?.[0]?.message ?? 'Something went wrong. Unknown error';
						this.showErrorPopup(error);
						this.summarySubmitBtn.classList.remove('ui-btn-wait');
					});
				});
			}
		}
	}
})();
