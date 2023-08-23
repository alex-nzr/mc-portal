this.BX = this.BX || {};
this.BX.Intranet = this.BX.Intranet || {};
(function (exports,ui_popupcomponentsmaker,main_popup,main_qrcode,main_core,main_core_events,ui_qrauthorization) {
	'use strict';

	var Options = function Options() {
	  babelHelpers.classCallCheck(this, Options);
	};

	babelHelpers.defineProperty(Options, "eventNameSpace", 'BX.Intranet.Userprofile:');

	var _templateObject, _templateObject2;
	var StressLevel = /*#__PURE__*/function () {
	  function StressLevel() {
	    babelHelpers.classCallCheck(this, StressLevel);
	  }

	  babelHelpers.createClass(StressLevel, null, [{
	    key: "getOpenSliderFunction",
	    value: function getOpenSliderFunction(url) {
	      if (main_core.Type.isStringFilled(url)) {
	        return function () {
	          main_core_events.EventEmitter.emit(main_core_events.EventEmitter.GLOBAL_TARGET, Options.eventNameSpace + 'onNeedToHide');
	          BX.SidePanel.Instance.open(url, {
	            cacheable: false,
	            data: {},
	            width: 500
	          });
	        };
	      }

	      return function () {};
	    }
	  }, {
	    key: "showData",
	    value: function showData(data) {
	      data.value = parseInt(data.value || 0);
	      var result = main_core.Tag.render(_templateObject || (_templateObject = babelHelpers.taggedTemplateLiteral(["\n\t\t\t<div class=\"system-auth-form__item system-auth-form__scope --vertical\" id=\"user-indicator-pulse\">\n\t\t\t\t<div class=\"system-auth-form__item-block --margin-bottom\">\n\t\t\t\t\t<div class=\"system-auth-form__stress-widget\">\n\t\t\t\t\t\t<div data-role=\"value-degree\" class=\"system-auth-form__stress-widget--arrow\" style=\"transform: rotate(90deg);\"></div>\n\t\t\t\t\t\t<div class=\"system-auth-form__stress-widget--content\">\n\t\t\t\t\t\t\t<div class=\"system-auth-form__stress-widget--content-title\">", "</div>\n\t\t\t\t\t\t\t<div data-role=\"value\" class=\"system-auth-form__stress-widget--content-progress ", "\">0</div>\n\t\t\t\t\t\t</div>\n\t\t\t\t\t</div>\n\t\t\t\t\t<div class=\"system-auth-form__item-container --stress-widget-sp\">\n\t\t\t\t\t\t<div class=\"system-auth-form__item-block --center --width-100\">\n\t\t\t\t\t\t\t<span class=\"system-auth-form__stress-widget--status  --flex --", "\">", "</span>\n\t\t\t\t\t\t\t<span class=\"system-auth-form__icon-help\" onclick=\"", "\"></span>\n\t\t\t\t\t\t</div>\n\t\t\t\t\t\t<div class=\"system-auth-form__item-title --link-dotted\" onclick=\"", "\">", "</div>\n\t\t\t\t\t</div>\n\t\t\t\t</div>\n\t\t\t\t<div class=\"system-auth-form__item-block --flex --center\">\n\t\t\t\t\t<div class=\"system-auth-form__stress-widget--message\">", "</div>\n\t\t\t\t</div>\n\t\t\t</div>\n\t\t"])), main_core.Loc.getMessage('INTRANET_USER_PROFILE_STRESSLEVEL_NORESULT_INDICATOR_TEXT'), data.value > 0 ? '' : '--empty', main_core.Text.encode(data.type), main_core.Text.encode(data.typeDescription), this.getOpenSliderFunction(data.url.result), this.getOpenSliderFunction(data.url.check), main_core.Loc.getMessage('INTRANET_USER_PROFILE_STRESSLEVEL_NORESULT_BUTTON'), main_core.Text.encode(data.comment));
	      setTimeout(function () {
	        var intervalId = setInterval(function (value) {
	          value.current++;
	          value.node.innerHTML = value.current;

	          if (value.current >= value.end) {
	            clearInterval(intervalId);
	          }
	        }, 600 / data.value, {
	          current: 0,
	          end: data.value,
	          node: result.querySelector('[data-role="value"]')
	        });
	        result.querySelector('[data-role="value-degree"]').style.transform = 'rotate(' + 1.8 * data.value + 'deg)';
	      }, 1000);
	      return result;
	    }
	  }, {
	    key: "showEmpty",
	    value: function showEmpty(_ref) {
	      var check = _ref.url.check;
	      return main_core.Tag.render(_templateObject2 || (_templateObject2 = babelHelpers.taggedTemplateLiteral(["\n\t\t\t<div class=\"system-auth-form__item system-auth-form__scope --vertical --empty-stress --clickable\" onclick=\"", "\">\n\t\t\t\t<div class=\"system-auth-form__item-block --margin-bottom\">\n\t\t\t\t\t<div class=\"system-auth-form__stress-widget\">\n\t\t\t\t\t\t<div data-role=\"value-degree\" class=\"system-auth-form__stress-widget--arrow\" style=\"transform: rotate(90deg);\"></div>\n\t\t\t\t\t\t<div class=\"system-auth-form__stress-widget--content\">\n\t\t\t\t\t\t\t<div class=\"system-auth-form__stress-widget--content-title\">", "</div>\n\t\t\t\t\t\t\t<div data-role=\"value\" class=\"system-auth-form__stress-widget--content-progress --empty\">?</div>\n\t\t\t\t\t\t</div>\n\t\t\t\t\t</div>\n\t\t\t\t\t<div class=\"system-auth-form__item-container --stress-widget-sp\">\n\t\t\t\t\t\t<div class=\"system-auth-form__stress-widget--message\">", "</div>\n\t\t\t\t\t</div>\n\t\t\t\t</div>\n\t\t\t\t<div class=\"system-auth-form__item-block --flex --center\">\n\t\t\t\t\t<div class=\"system-auth-form__item-title --link-dotted\">", "</div>\n\t\t\t\t</div>\n\t\t\t\t<div class=\"system-auth-form__item-new\">\n\t\t\t\t\t<div class=\"system-auth-form__item-new--title\">", "</div>\n\t\t\t\t</div>\n\t\t\t</div>"])), this.getOpenSliderFunction(check), main_core.Loc.getMessage('INTRANET_USER_PROFILE_STRESSLEVEL_NORESULT_INDICATOR_TEXT'), main_core.Loc.getMessage('INTRANET_USER_PROFILE_STRESSLEVEL_NORESULT_TITLE'), main_core.Loc.getMessage('INTRANET_USER_PROFILE_STRESSLEVEL_NORESULT_BUTTON'), main_core.Loc.getMessage('INTRANET_USER_PROFILE_STRESSLEVEL_RESULT_COME_ON'));
	    }
	  }, {
	    key: "getPromise",
	    value: function getPromise(_ref2) {
	      var _this = this;

	      var signedParameters = _ref2.signedParameters,
	          componentName = _ref2.componentName,
	          userId = _ref2.userId,
	          data = _ref2.data;
	      return new Promise(function (resolve, reject) {
	        var promise = data ? Promise.resolve({
	          data: data
	        }) : main_core.ajax.runAction('socialnetwork.api.user.stresslevel.get', {
	          signedParameters: signedParameters,
	          data: {
	            c: componentName,
	            fields: {
	              userId: userId
	            }
	          }
	        });
	        promise.then(function (_ref3) {
	          var data = _ref3.data;

	          if (data && data.id !== undefined && data.value !== undefined) {
	            return resolve(_this.showData(data));
	          }

	          var node = main_core.Loc.getMessage('USER_ID') === userId ? _this.showEmpty(data) : document.createElement('DIV');
	          return resolve(node);
	        })["catch"](function (error) {
	          resolve(_this.showData({
	            id: undefined,
	            value: undefined,
	            urls: {
	              check: undefined
	            }
	          }));
	        });
	      });
	    }
	  }]);
	  return StressLevel;
	}();

	var _templateObject$1;

	function _classPrivateFieldInitSpec(obj, privateMap, value) { _checkPrivateRedeclaration(obj, privateMap); privateMap.set(obj, value); }

	function _checkPrivateRedeclaration(obj, privateCollection) { if (privateCollection.has(obj)) { throw new TypeError("Cannot initialize the same private elements twice on an object"); } }

	var _params = /*#__PURE__*/new WeakMap();

	var _container = /*#__PURE__*/new WeakMap();

	var ThemePicker = /*#__PURE__*/function (_EventEmitter) {
	  babelHelpers.inherits(ThemePicker, _EventEmitter);

	  function ThemePicker(data) {
	    var _this;

	    babelHelpers.classCallCheck(this, ThemePicker);
	    _this = babelHelpers.possibleConstructorReturn(this, babelHelpers.getPrototypeOf(ThemePicker).call(this));

	    _classPrivateFieldInitSpec(babelHelpers.assertThisInitialized(_this), _params, {
	      writable: true,
	      value: void 0
	    });

	    _classPrivateFieldInitSpec(babelHelpers.assertThisInitialized(_this), _container, {
	      writable: true,
	      value: void 0
	    });

	    babelHelpers.classPrivateFieldSet(babelHelpers.assertThisInitialized(_this), _params, Object.assign({}, data));
	    babelHelpers.classPrivateFieldGet(babelHelpers.assertThisInitialized(_this), _params).lightning = String(data.id).indexOf('light:') === 0 ? 'light' : String(data.id).indexOf('dark:') === 0 ? 'dark' : null;
	    _this.applyTheme = _this.applyTheme.bind(babelHelpers.assertThisInitialized(_this));

	    _this.setEventNamespace(Options.eventNameSpace);

	    main_core_events.EventEmitter.subscribe('BX.Intranet.Bitrix24:ThemePicker:onThemeApply', function (_ref) {
	      var _ref$data = _ref.data,
	          id = _ref$data.id,
	          theme = _ref$data.theme;
	      babelHelpers.classPrivateFieldGet(babelHelpers.assertThisInitialized(_this), _params).id = id;
	      babelHelpers.classPrivateFieldGet(babelHelpers.assertThisInitialized(_this), _params).lightning = String(id).indexOf('light:') === 0 ? 'light' : String(id).indexOf('dark:') === 0 ? 'dark' : null;
	      babelHelpers.classPrivateFieldGet(babelHelpers.assertThisInitialized(_this), _params).title = theme.title;
	      babelHelpers.classPrivateFieldGet(babelHelpers.assertThisInitialized(_this), _params).previewImage = theme.previewImage;
	      babelHelpers.classPrivateFieldGet(babelHelpers.assertThisInitialized(_this), _params).previewColor = theme.previewColor;

	      _this.applyTheme();
	    });
	    return _this;
	  }

	  babelHelpers.createClass(ThemePicker, [{
	    key: "applyTheme",
	    value: function applyTheme() {
	      var _babelHelpers$classPr;

	      var container = this.getContainer();
	      container.querySelector('[data-role="title"]').innerHTML = (_babelHelpers$classPr = babelHelpers.classPrivateFieldGet(this, _params).title) !== null && _babelHelpers$classPr !== void 0 ? _babelHelpers$classPr : main_core.Loc.getMessage('AUTH_THEME_DIALOG');

	      if (main_core.Type.isStringFilled(babelHelpers.classPrivateFieldGet(this, _params).previewImage) && babelHelpers.classPrivateFieldGet(this, _params).lightning) {
	        container.style.removeProperty('backgroundImage');
	        container.style.removeProperty('backgroundSize');
	        container.style.backgroundImage = 'url("' + babelHelpers.classPrivateFieldGet(this, _params).previewImage + '")';
	        container.style.backgroundSize = 'cover';
	      } else {
	        container.style.background = 'none';
	      }

	      if (main_core.Type.isStringFilled(babelHelpers.classPrivateFieldGet(this, _params).previewColor)) {
	        this.getContainer().style.backgroundColor = babelHelpers.classPrivateFieldGet(this, _params).previewColor;
	      }

	      if (!babelHelpers.classPrivateFieldGet(this, _params).lightning) {
	        container.style.backgroundColor = 'rgba(255,255,255,1)';
	      }

	      main_core.Dom.removeClass(container, '--light --dark');
	      main_core.Dom.addClass(container, '--' + babelHelpers.classPrivateFieldGet(this, _params).lightning);
	    }
	  }, {
	    key: "getContainer",
	    value: function getContainer() {
	      var _this2 = this;

	      if (babelHelpers.classPrivateFieldGet(this, _container)) {
	        return babelHelpers.classPrivateFieldGet(this, _container);
	      }

	      var onclick = function onclick() {
	        _this2.emit('onOpen');

	        BX.Intranet.Bitrix24.ThemePicker.Singleton.showDialog(false);
	      };

	      babelHelpers.classPrivateFieldSet(this, _container, main_core.Tag.render(_templateObject$1 || (_templateObject$1 = babelHelpers.taggedTemplateLiteral(["\n\t\t\t<div class=\"system-auth-form__item system-auth-form__scope --border ", " --padding-sm\" title=\"", "\">\n\t\t\t\t<div class=\"system-auth-form__item-logo\">\n\t\t\t\t\t<div data-role=\"preview-color\" class=\"system-auth-form__item-logo--image --theme\"></div>\n\t\t\t\t</div>\n\t\t\t\t<div class=\"system-auth-form__item-container --flex --column\">\n\t\t\t\t\t<div class=\"system-auth-form__item-title --white-space --block\">\n\t\t\t\t\t\t<span data-role=\"title\">Theme</span>\n\t\t\t\t\t</div>\n\t\t\t\t\t<div class=\"system-auth-form__item-content --margin-top-auto --center --center-force\">\n\t\t\t\t\t\t<div class=\"ui-qr-popupcomponentmaker__btn\" onclick=\"", "\">", "</div>\n\t\t\t\t\t</div>\n\t\t\t\t</div>\n\t\t\t</div>"])), babelHelpers.classPrivateFieldGet(this, _params).lightning ? '--' + babelHelpers.classPrivateFieldGet(this, _params).lightning : '', main_core.Loc.getMessage('AUTH_THEME_DIALOG'), onclick, main_core.Loc.getMessage('INTRANET_USER_PROFILE_CHANGE')));
	      setTimeout(this.applyTheme, 0);
	      return babelHelpers.classPrivateFieldGet(this, _container);
	    }
	  }, {
	    key: "getPromise",
	    value: function getPromise() {
	      var _this3 = this;

	      return new Promise(function (resolve) {
	        resolve(_this3.getContainer());
	      });
	    }
	  }]);
	  return ThemePicker;
	}(main_core_events.EventEmitter);

	var _templateObject$2, _templateObject2$1, _templateObject3, _templateObject4, _templateObject5, _templateObject6;

	function _classPrivateMethodInitSpec(obj, privateSet) { _checkPrivateRedeclaration$1(obj, privateSet); privateSet.add(obj); }

	function _checkPrivateRedeclaration$1(obj, privateCollection) { if (privateCollection.has(obj)) { throw new TypeError("Cannot initialize the same private elements twice on an object"); } }

	function _classPrivateMethodGet(receiver, privateSet, fn) { if (!privateSet.has(receiver)) { throw new TypeError("attempted to get private field on non-instance"); } return fn; }

	var _renderUsers = /*#__PURE__*/new WeakSet();

	var Ustat = /*#__PURE__*/function () {
	  function Ustat(data) {
	    babelHelpers.classCallCheck(this, Ustat);

	    _classPrivateMethodInitSpec(this, _renderUsers);

	    this.data = data;
	    this.onclickHandle = this.onclickHandle.bind(this);
	  }

	  babelHelpers.createClass(Ustat, [{
	    key: "showData",
	    value: function showData() {
	      var _classPrivateMethodGe = _classPrivateMethodGet(this, _renderUsers, _renderUsers2).call(this),
	          myPosition = _classPrivateMethodGe.myPosition,
	          userList = _classPrivateMethodGe.userList,
	          range = _classPrivateMethodGe.range;

	      var div;

	      if (range > 0 && myPosition > 0) {
	        div = main_core.Tag.render(_templateObject$2 || (_templateObject$2 = babelHelpers.taggedTemplateLiteral(["\n\t\t\t<div class=\"system-auth-form__item system-auth-form__scope --clickable\" onclick=\"", "\">\n\t\t\t\t<div class=\"system-auth-form__item-container\">\n\t\t\t\t\t<div class=\"system-auth-form__item-title --without-margin\">", "</div>\n\t\t\t\t\t<div class=\"system-auth-form__item-title --link-light --margin-s\">\n\t\t\t\t\t\t<span>", "</span>\n\t\t\t\t\t\t<span class=\"system-auth-form__icon-help\" data-hint=\"", "\" data-hint-no-icon></span>\n\t\t\t\t\t</div>\n\t\t\t\t\t<div class=\"system-auth-form__item-title --link-light\" data-role=\"empty-info\">", "</div>\n\n\t\t\t\t\t<div class=\"system-auth-form__item-title --white-space --margin-xl\">\n\t\t\t\t\t\t<span>", "</span>\n\t\t\t\t\t\t<span class=\"system-auth-form__ustat-icon --up\"></span>\n\t\t\t\t\t</div>\n\t\t\t\t\t<div class=\"system-auth-form__userlist\">\n\t\t\t\t\t\t", "\n\t\t\t\t\t</div>\n\t\t\t\t</div>\n\t\t\t</div>\n\t\t"])), this.onclickHandle, main_core.Loc.getMessage('INTRANET_USER_PROFILE_PULSE_TITLE'), main_core.Loc.getMessage('INTRANET_USER_PROFILE_PULSE_MY_RATING'), main_core.Loc.getMessage('INTRANET_USTAT_COMPANY_HELP_RATING'), main_core.Loc.getMessage('INTRANET_USER_PROFILE_PULSE_MY_IS_EMPTY'), main_core.Loc.getMessage('INTRANET_USER_PROFILE_PULSE_MY_POSITION', {
	          '#POSITION#': myPosition,
	          '#AMONG#': range
	        }), userList);
	      } else {
	        var onclick = range > 0 ? this.onclickHandle : function () {};
	        div = main_core.Tag.render(_templateObject2$1 || (_templateObject2$1 = babelHelpers.taggedTemplateLiteral(["\n\t\t\t<div class=\"system-auth-form__item system-auth-form__scope --without-stat ", "\" onclick=\"", "\">\n\t\t\t\t<div class=\"system-auth-form__item-container --flex --column\">\n\t\t\t\t\t<div class=\"system-auth-form__item-title\">", "</div>\n\t\t\t\t\t<div class=\"system-auth-form__item-container --center\">\n\t\t\t\t\t\t<div class=\"system-auth-form__item-title --lighter\" data-role=\"empty-info\">", "</div>\n\t\t\t\t\t</div>\n\t\t\t\t</div>\n\t\t\t</div>\n\t\t"])), range > 0 ? '--clickable' : '', onclick, main_core.Loc.getMessage('INTRANET_USER_PROFILE_PULSE_TITLE'), main_core.Loc.getMessage('INTRANET_USER_PROFILE_PULSE_MY_IS_EMPTY_BRIEF'));
	      }

	      BX.UI.Hint.init(div);
	      return div;
	    }
	  }, {
	    key: "onclickHandle",
	    value: function onclickHandle(event) {
	      main_core_events.EventEmitter.emit(Options.eventNameSpace + 'onNeedToHide');

	      if (window['openIntranetUStat']) {
	        openIntranetUStat(event);
	      }
	    }
	  }, {
	    key: "showWideData",
	    value: function showWideData() {
	      var _classPrivateMethodGe2 = _classPrivateMethodGet(this, _renderUsers, _renderUsers2).call(this),
	          myPosition = _classPrivateMethodGe2.myPosition,
	          userList = _classPrivateMethodGe2.userList,
	          range = _classPrivateMethodGe2.range;

	      var div = main_core.Tag.render(_templateObject3 || (_templateObject3 = babelHelpers.taggedTemplateLiteral(["\n\t\t\t<div class=\"system-auth-form__item system-auth-form__scope --center --padding-ustat ", "\">\n\t\t\t\t<div class=\"system-auth-form__item-image\">\n\t\t\t\t\t<div class=\"system-auth-form__item-image--src --ustat\"></div>\n\t\t\t\t</div>\n\t\t\t\t<div class=\"system-auth-form__item-container --overflow\">\n\t\t\t\t\t<div class=\"system-auth-form__item-title --xl --without-margin\">", "</div>\n\t\t\t\t</div>\n\t\t\t\t<div class=\"system-auth-form__item-container --block\">\n\t\t\t\t\t<div class=\"system-auth-form__item-title --link-light\" data-role=\"empty-info\">", "</div>\n\t\t\t\t\t<div class=\"system-auth-form__item-container--inline\" data-role=\"my-position\">\n\t\t\t\t\t\t<div class=\"system-auth-form__item-title --link-light --without-margin --margin-right\">\n\t\t\t\t\t\t\t", "\n\t\t\t\t\t\t</div>\n\t\t\t\t\t\t<div class=\"system-auth-form__item-title --white-space --margin-xl\">\n\t\t\t\t\t\t\t<span>", "</span>\n\t\t\t\t\t\t\t<span class=\"system-auth-form__ustat-icon --up\"></span>\n\t\t\t\t\t\t</div>\n\t\t\t\t\t</div>\n\t\t\t\t\t<div class=\"system-auth-form__userlist\" data-role=\"user-list\">\n\t\t\t\t\t\t", "\n\t\t\t\t\t</div>\n\t\t\t\t</div>\n\t\t\t\t<div class=\"system-auth-form__icon-help --absolute-right-bottom\" data-hint=\"", "\" data-hint-no-icon></div>\n\t\t\t</div>\n\t\t"])), range > 0 ? '--clickable' : '--without-stat', main_core.Loc.getMessage('INTRANET_USER_PROFILE_PULSE_TITLE'), main_core.Loc.getMessage('INTRANET_USER_PROFILE_PULSE_MY_IS_EMPTY'), main_core.Loc.getMessage('INTRANET_USER_PROFILE_PULSE_MY_RATING'), main_core.Loc.getMessage('INTRANET_USER_PROFILE_PULSE_MY_POSITION', {
	        '#POSITION#': myPosition,
	        '#AMONG#': range
	      }), userList, main_core.Loc.getMessage('INTRANET_USTAT_COMPANY_HELP_RATING'));

	      if (range > 0) {
	        div.addEventListener('click', this.onclickHandle);
	      }

	      return div;
	    }
	  }], [{
	    key: "getPromise",
	    value: function getPromise(_ref) {
	      var _this = this;

	      var userId = _ref.userId,
	          isNarrow = _ref.isNarrow,
	          data = _ref.data;
	      return new Promise(function (resolve, reject) {
	        (data ? Promise.resolve({
	          data: data
	        }) : main_core.ajax.runComponentAction('bitrix:intranet.ustat.department', 'getJson', {
	          mode: 'class',
	          data: {}
	        })).then(function (_ref2) {
	          var data = _ref2.data;
	          var ustat = new _this(data);
	          resolve(isNarrow ? ustat.showData() : ustat.showWideData());
	        })["catch"](function (errors) {
	          errors = main_core.Type.isArray(errors) ? errors : [errors];
	          var node = document.createElement('ul');
	          errors.forEach(function (_ref3) {
	            var message = _ref3.message;
	            var errorNode = document.createElement('li');
	            errorNode.innerHTML = message;
	            errorNode.className = 'ui-alert-message';
	            node.appendChild(errorNode);
	          });
	          resolve(main_core.Tag.render(_templateObject4 || (_templateObject4 = babelHelpers.taggedTemplateLiteral(["\n\t\t\t\t\t\t<div class=\"ui-alert ui-alert-danger\">\n\t\t\t\t\t\t\t", "\n\t\t\t\t\t\t</div>"])), node));
	        });
	      });
	    }
	  }]);
	  return Ustat;
	}();

	function _renderUsers2() {
	  var _this2 = this;

	  var userList = document.createDocumentFragment();
	  var myPosition = parseInt(this.data['USERS_RATING']['position']);
	  var myActivity = 0;
	  var usersData = main_core.Type.isPlainObject(this.data['USERS_RATING']['top']) ? Object.values(this.data['USERS_RATING']['top']) : main_core.Type.isArray(this.data['USERS_RATING']['top']) && this.data['USERS_RATING']['top'].length > 0 ? babelHelpers.toConsumableArray(this.data['USERS_RATING']['top']) : [{
	    'USER_ID': main_core.Loc.getMessage('USER_ID'),
	    ACTIVITY: 0
	  }];
	  var dataResult = myPosition > 5 ? [].concat(babelHelpers.toConsumableArray(usersData.slice(0, 3)), babelHelpers.toConsumableArray(usersData.slice(-1)), [null]) : usersData;
	  dataResult.forEach(function (userRating, index) {
	    if (userRating === null) {
	      userList.appendChild(main_core.Tag.render(_templateObject5 || (_templateObject5 = babelHelpers.taggedTemplateLiteral(["<div class=\"system-auth-form__userlist-item --list\"></div>"]))));
	      return;
	    }

	    var fullName = userRating['ACTIVITY'];
	    var avatarSrc = '';

	    if (_this2.data['USERS_INFO'][userRating['USER_ID']]) {
	      fullName = [_this2.data['USERS_INFO'][userRating['USER_ID']]['FULL_NAME'], ': ', userRating['ACTIVITY']].join('');
	      avatarSrc = String(_this2.data['USERS_INFO'][userRating['USER_ID']]['AVATAR_SRC']).length > 0 ? _this2.data['USERS_INFO'][userRating['USER_ID']]['AVATAR_SRC'] : null;
	    }

	    var isCurrentUser = String(userRating['USER_ID']) === String(main_core.Loc.getMessage('USER_ID'));

	    if (isCurrentUser) {
	      myActivity = userRating['ACTIVITY'];
	    }

	    userList.appendChild(main_core.Tag.render(_templateObject6 || (_templateObject6 = babelHelpers.taggedTemplateLiteral(["\n\t\t\t\t\t\t<div title=\"", "\" class=\"system-auth-form__userlist-item ui-icon ui-icon ui-icon-common-user\">\n\t\t\t\t\t\t\t<i ", "></i>\n\t\t\t\t\t\t</div>\n\t\t\t\t\t"])), main_core.Text.encode(fullName), avatarSrc ? "style=\"background-image: url('".concat(avatarSrc, "');background-size: cover;\"") : ''));
	  });
	  return {
	    userList: userList,
	    myPosition: myPosition,
	    range: parseInt(this.data['USERS_RATING']['range']),
	    myActivity: myActivity
	  };
	}

	var _templateObject$3, _templateObject2$2, _templateObject3$1, _templateObject23, _templateObject24;

	function _classPrivateMethodInitSpec$1(obj, privateSet) { _checkPrivateRedeclaration$2(obj, privateSet); privateSet.add(obj); }

	function _classPrivateFieldInitSpec$1(obj, privateMap, value) { _checkPrivateRedeclaration$2(obj, privateMap); privateMap.set(obj, value); }

	function _checkPrivateRedeclaration$2(obj, privateCollection) { if (privateCollection.has(obj)) { throw new TypeError("Cannot initialize the same private elements twice on an object"); } }

	function _classPrivateMethodGet$1(receiver, privateSet, fn) { if (!privateSet.has(receiver)) { throw new TypeError("attempted to get private field on non-instance"); } return fn; }
	var widgetMarker = Symbol('user.widget');

	var _container$1 = /*#__PURE__*/new WeakMap();

	var _popup = /*#__PURE__*/new WeakMap();

	var _profile = /*#__PURE__*/new WeakMap();

	var _features = /*#__PURE__*/new WeakMap();

	var _cache = /*#__PURE__*/new WeakMap();

	var _getProfileContainer = /*#__PURE__*/new WeakSet();

	var _getb24NetPanelContainer = /*#__PURE__*/new WeakSet();

	var _getAdminPanelContainer = /*#__PURE__*/new WeakSet();

	var _getThemeContainer = /*#__PURE__*/new WeakSet();

	var _getMaskContainer = /*#__PURE__*/new WeakSet();

	var _getCompanyPulse = /*#__PURE__*/new WeakSet();

	var _getStressLevel = /*#__PURE__*/new WeakSet();

	var _getQrContainer = /*#__PURE__*/new WeakSet();

	var _getDeskTopContainer = /*#__PURE__*/new WeakSet();

	var _getOTPContainer = /*#__PURE__*/new WeakSet();

	var _getLoginHistoryContainer = /*#__PURE__*/new WeakSet();

	var _getBindings = /*#__PURE__*/new WeakSet();

	var _getNotificationContainer = /*#__PURE__*/new WeakSet();

	var _getLogoutContainer = /*#__PURE__*/new WeakSet();

	var Widget = /*#__PURE__*/function (_EventEmitter) {
	  babelHelpers.inherits(Widget, _EventEmitter);

	  function Widget(container, _ref) {
	    var _this;

	    var _ref$profile = _ref.profile,
	        ID = _ref$profile.ID,
	        FULL_NAME = _ref$profile.FULL_NAME,
	        PHOTO = _ref$profile.PHOTO,
	        STATUS = _ref$profile.STATUS,
	        URL = _ref$profile.URL,
	        WORK_POSITION = _ref$profile.WORK_POSITION,
	        _ref$component = _ref.component,
	        componentName = _ref$component.componentName,
	        signedParameters = _ref$component.signedParameters,
	        features = _ref.features;
	    babelHelpers.classCallCheck(this, Widget);
	    _this = babelHelpers.possibleConstructorReturn(this, babelHelpers.getPrototypeOf(Widget).call(this));

	    _classPrivateMethodInitSpec$1(babelHelpers.assertThisInitialized(_this), _getLogoutContainer);

	    _classPrivateMethodInitSpec$1(babelHelpers.assertThisInitialized(_this), _getNotificationContainer);

	    _classPrivateMethodInitSpec$1(babelHelpers.assertThisInitialized(_this), _getBindings);

	    _classPrivateMethodInitSpec$1(babelHelpers.assertThisInitialized(_this), _getLoginHistoryContainer);

	    _classPrivateMethodInitSpec$1(babelHelpers.assertThisInitialized(_this), _getOTPContainer);

	    _classPrivateMethodInitSpec$1(babelHelpers.assertThisInitialized(_this), _getDeskTopContainer);

	    _classPrivateMethodInitSpec$1(babelHelpers.assertThisInitialized(_this), _getQrContainer);

	    _classPrivateMethodInitSpec$1(babelHelpers.assertThisInitialized(_this), _getStressLevel);

	    _classPrivateMethodInitSpec$1(babelHelpers.assertThisInitialized(_this), _getCompanyPulse);

	    _classPrivateMethodInitSpec$1(babelHelpers.assertThisInitialized(_this), _getMaskContainer);

	    _classPrivateMethodInitSpec$1(babelHelpers.assertThisInitialized(_this), _getThemeContainer);

	    _classPrivateMethodInitSpec$1(babelHelpers.assertThisInitialized(_this), _getAdminPanelContainer);

	    _classPrivateMethodInitSpec$1(babelHelpers.assertThisInitialized(_this), _getb24NetPanelContainer);

	    _classPrivateMethodInitSpec$1(babelHelpers.assertThisInitialized(_this), _getProfileContainer);

	    _classPrivateFieldInitSpec$1(babelHelpers.assertThisInitialized(_this), _container$1, {
	      writable: true,
	      value: void 0
	    });

	    _classPrivateFieldInitSpec$1(babelHelpers.assertThisInitialized(_this), _popup, {
	      writable: true,
	      value: void 0
	    });

	    _classPrivateFieldInitSpec$1(babelHelpers.assertThisInitialized(_this), _profile, {
	      writable: true,
	      value: null
	    });

	    _classPrivateFieldInitSpec$1(babelHelpers.assertThisInitialized(_this), _features, {
	      writable: true,
	      value: {}
	    });

	    _classPrivateFieldInitSpec$1(babelHelpers.assertThisInitialized(_this), _cache, {
	      writable: true,
	      value: new main_core.Cache.MemoryCache()
	    });

	    _this.setEventNamespace(Options.eventNameSpace);

	    babelHelpers.classPrivateFieldSet(babelHelpers.assertThisInitialized(_this), _container$1, container);
	    babelHelpers.classPrivateFieldSet(babelHelpers.assertThisInitialized(_this), _profile, {
	      ID: ID,
	      FULL_NAME: FULL_NAME,
	      PHOTO: PHOTO,
	      STATUS: STATUS,
	      URL: URL,
	      WORK_POSITION: WORK_POSITION
	    });
	    babelHelpers.classPrivateFieldSet(babelHelpers.assertThisInitialized(_this), _features, features);

	    if (!main_core.Type.isStringFilled(babelHelpers.classPrivateFieldGet(babelHelpers.assertThisInitialized(_this), _features).browser)) {
	      babelHelpers.classPrivateFieldGet(babelHelpers.assertThisInitialized(_this), _features).browser = main_core.Browser.isLinux() ? 'Linux' : main_core.Browser.isWin() ? 'Windows' : 'MacOs';
	    }

	    babelHelpers.classPrivateFieldGet(babelHelpers.assertThisInitialized(_this), _cache).set('componentParams', {
	      componentName: componentName,
	      signedParameters: signedParameters
	    });
	    _this.hide = _this.hide.bind(babelHelpers.assertThisInitialized(_this));
	    return _this;
	  }

	  babelHelpers.createClass(Widget, [{
	    key: "toggle",
	    value: function toggle() {
	      if (this.getPopup().isShown()) {
	        this.hide();
	      } else {
	        this.show();
	      }
	    }
	  }, {
	    key: "hide",
	    value: function hide() {
	      this.getPopup().close();
	    }
	  }, {
	    key: "show",
	    value: function show() {
	      this.getPopup().show();
	    }
	  }, {
	    key: "getPopup",
	    value: function getPopup() {
	      var _classPrivateMethodGe;

	      if (babelHelpers.classPrivateFieldGet(this, _popup)) {
	        return babelHelpers.classPrivateFieldGet(this, _popup);
	      }

	      var content = [_classPrivateMethodGet$1(this, _getProfileContainer, _getProfileContainer2).call(this),
	      /*(this.#getb24NetPanelContainer() ? {
	      	html: this.#getb24NetPanelContainer(),
	      	backgroundColor: '#fafafa'
	      } : null),
	      (this.#getAdminPanelContainer() ? {
	      	html: this.#getAdminPanelContainer(),
	      	backgroundColor: '#fafafa'
	      } : null),
	      [
	      	{
	      		html: this.#getThemeContainer(),
	      		marginBottom: 24,
	      		overflow: true
	      	},
	      	{
	      		html: this.#getMaskContainer(),
	      		disabled: true,
	      		backgroundColor: '#fafafa'
	      	}
	      ],
	      this.#getCompanyPulse(!!this.#getStressLevel()) ?
	      [
	      	{
	      		html: this.#getCompanyPulse(!!this.#getStressLevel()),
	      		overflow: true,
	      		marginBottom: 24,
	      		flex: this.#getStressLevel() ? 0.5 : 1
	      	},
	      	this.#getStressLevel(),
	      ] : null,
	      this.#getOTPContainer(this.#getDeskTopContainer() === null) && this.#getDeskTopContainer() ?
	      [
	      	{
	      		flex: 0.5,
	      		html: this.#getQrContainer(0.7)
	      	},
	      	[
	      		{
	      			html: this.#getDeskTopContainer(),
	      			displayBlock: true
	      		},
	      		this.#getOTPContainer()
	      	]
	      ] : this.#getDeskTopContainer() || this.#getOTPContainer() ? [
	      		{
	      			html: this.#getQrContainer(2),
	      			flex: 2
	      		},
	      		this.#getDeskTopContainer() ?? this.#getOTPContainer()
	      ] : this.#getQrContainer(0),
	      this.#getLoginHistoryContainer(),
	      {
	      	html: this.#getBindings(),
	      	marginBottom: 24,
	      	backgroundColor: '#fafafa'
	      },*/
	      [{
	        html: (_classPrivateMethodGe = _classPrivateMethodGet$1(this, _getNotificationContainer, _getNotificationContainer2).call(this)) !== null && _classPrivateMethodGe !== void 0 ? _classPrivateMethodGe : null,
	        backgroundColor: '#fafafa'
	      }, {
	        html: _classPrivateMethodGet$1(this, _getLogoutContainer, _getLogoutContainer2).call(this),
	        backgroundColor: '#fafafa'
	      }]];

	      var filterFunc = function filterFunc(data) {
	        var result = [];

	        if (main_core.Type.isArray(data)) {
	          for (var i = 0; i < data.length; i++) {
	            if (main_core.Type.isArray(data[i])) {
	              var buff = filterFunc(data[i]);

	              if (buff !== null) {
	                if (main_core.Type.isArray(buff) && buff.length === 1) {
	                  result.push(buff[0]);
	                } else {
	                  result.push(buff);
	                }
	              }
	            } else if (data[i] !== null) {
	              result.push(data[i]);
	            }
	          }
	        }

	        return result.length <= 0 ? null : result.length === 1 ? result[0] : result;
	      };

	      content = filterFunc(content);

	      var prepareFunc = function prepareFunc(item, index, array) {
	        if (main_core.Type.isArray(item)) {
	          return {
	            html: item.map(prepareFunc)
	          };
	        }

	        return {
	          flex: item['flex'] || 0,
	          html: item['html'] || item,
	          backgroundColor: item['backgroundColor'] || null,
	          disabled: item['disabled'] || null,
	          overflow: item['overflow'] || null,
	          marginBottom: item['marginBottom'] || null,
	          displayBlock: item['displayBlock'] || null
	        };
	      };

	      babelHelpers.classPrivateFieldSet(this, _popup, new ui_popupcomponentsmaker.PopupComponentsMaker({
	        target: babelHelpers.classPrivateFieldGet(this, _container$1),
	        content: content.map(prepareFunc),
	        width: 400
	      }));
	      main_core_events.EventEmitter.subscribe('BX.Main.InterfaceButtons:onMenuShow', this.hide);
	      main_core_events.EventEmitter.subscribe(Options.eventNameSpace + 'onNeedToHide', this.hide);
	      return babelHelpers.classPrivateFieldGet(this, _popup);
	    }
	  }], [{
	    key: "init",
	    value: function init(node, options) {
	      var _this2 = this;

	      if (node[widgetMarker]) {
	        return;
	      }

	      var onclick = function onclick() {
	        if (!node['popupSymbol']) {
	          node['popupSymbol'] = new _this2(node, options);
	        }

	        node['popupSymbol'].toggle();
	        _this2.instance = node['popupSymbol'];
	      };

	      node[widgetMarker] = true;
	      node.addEventListener('click', onclick);
	      setTimeout(onclick, 100);
	    }
	  }, {
	    key: "getInstance",
	    value: function getInstance() {
	      return this.instance;
	    }
	  }]);
	  return Widget;
	}(main_core_events.EventEmitter);

	function _getProfileContainer2() {
	  var _this3 = this;

	  return babelHelpers.classPrivateFieldGet(this, _cache).remember('profile', function () {
	    var onclick = function onclick(event) {
	      _this3.hide();

	      return BX.SidePanel.Instance.open(babelHelpers.classPrivateFieldGet(_this3, _profile).URL);
	    };

	    var avatarNode = main_core.Tag.render(_templateObject$3 || (_templateObject$3 = babelHelpers.taggedTemplateLiteral(["\n\t\t\t\t<span class=\"system-auth-form__profile-avatar--image\"\n\t\t\t\t\t", ">\n\t\t\t\t</span>\n\t\t\t\t"])), babelHelpers.classPrivateFieldGet(_this3, _profile).PHOTO ? "\n\t\t\t\t\t\tstyle=\"background-size: cover; background-image: url('".concat(babelHelpers.classPrivateFieldGet(_this3, _profile).PHOTO, "')\"") : '');
	    var nameNode = main_core.Tag.render(_templateObject2$2 || (_templateObject2$2 = babelHelpers.taggedTemplateLiteral(["\n\t\t\t\t<div class=\"system-auth-form__profile-name\">", "</div>\n\t\t\t"])), babelHelpers.classPrivateFieldGet(_this3, _profile).FULL_NAME);
	    main_core_events.EventEmitter.subscribe(main_core_events.EventEmitter.GLOBAL_TARGET, 'BX.Intranet.UserProfile:Avatar:changed', function (_ref2) {
	      var _ref2$data = babelHelpers.slicedToArray(_ref2.data, 1),
	          url = _ref2$data[0].url;

	      babelHelpers.classPrivateFieldGet(_this3, _profile).PHOTO = url;
	      avatarNode.style = main_core.Type.isStringFilled(url) ? "background-size: cover; background-image: url('".concat(babelHelpers.classPrivateFieldGet(_this3, _profile).PHOTO, "')") : '';
	    });
	    main_core_events.EventEmitter.subscribe(main_core_events.EventEmitter.GLOBAL_TARGET, 'BX.Intranet.UserProfile:Name:changed', function (_ref3) {
	      var _ref3$data = babelHelpers.slicedToArray(_ref3.data, 1),
	          fullName = _ref3$data[0].fullName;

	      babelHelpers.classPrivateFieldGet(_this3, _profile).FULL_NAME = fullName;
	      nameNode.innerHTML = fullName;
	      babelHelpers.classPrivateFieldGet(_this3, _container$1).querySelector('#user-name').innerHTML = fullName;
	    });
	    var workPosition = main_core.Type.isStringFilled(babelHelpers.classPrivateFieldGet(_this3, _profile).WORK_POSITION) ? main_core.Text.encode(babelHelpers.classPrivateFieldGet(_this3, _profile).WORK_POSITION) : '';

	    if (babelHelpers.classPrivateFieldGet(_this3, _profile).STATUS && main_core.Loc.hasMessage('INTRANET_USER_PROFILE_' + babelHelpers.classPrivateFieldGet(_this3, _profile).STATUS)) {
	      workPosition = main_core.Loc.getMessage('INTRANET_USER_PROFILE_' + babelHelpers.classPrivateFieldGet(_this3, _profile).STATUS);
	    }

	    return main_core.Tag.render(_templateObject3$1 || (_templateObject3$1 = babelHelpers.taggedTemplateLiteral(["\n\t\t\t\t<div class=\"system-auth-form__item system-auth-form__scope --clickable\" onclick=\"", "\">\n\t\t\t\t\t<div class=\"system-auth-form__profile\">\n\t\t\t\t\t\t<div class=\"system-auth-form__profile-avatar\">\n\t\t\t\t\t\t\t", "\n\t\t\t\t\t\t</div>\n\t\t\t\t\t\t<div class=\"system-auth-form__profile-content --margin--right\">\n\t\t\t\t\t\t\t", "\n\t\t\t\t\t\t\t<div class=\"system-auth-form__profile-position\">", "</div>\n\t\t\t\t\t\t</div>\n\t\t\t\t\t\t<div class=\"system-auth-form__profile-controls\">\n\t\t\t\t\t\t\t<span class=\"ui-qr-popupcomponentmaker__btn --large --border\" >\n\t\t\t\t\t\t\t\t", "\n\t\t\t\t\t\t\t</span>\n\t\t\t\t\t \t\t<!-- <span class=\"ui-qr-popupcomponentmaker__btn --large --success\">any text</span> -->\n\t\t\t\t\t\t</div>\n\t\t\t\t\t</div>\n\t\t\t\t</div>\n\t\t\t"])), onclick, avatarNode, nameNode, workPosition, main_core.Loc.getMessage('INTRANET_USER_PROFILE_PROFILE'));
	  });
	}

	function _getNotificationContainer2() {
	  var _this14 = this;

	  if (babelHelpers.classPrivateFieldGet(this, _features)['im'] !== 'Y') {
	    return null;
	  }

	  return babelHelpers.classPrivateFieldGet(this, _cache).remember('getNotificationContainer', function () {
	    var div = main_core.Tag.render(_templateObject23 || (_templateObject23 = babelHelpers.taggedTemplateLiteral(["\n\t\t\t\t<div class=\"system-auth-form__item --hover system-auth-form__scope --padding-sm\">\n\t\t\t\t\t<div class=\"system-auth-form__item-logo\">\n\t\t\t\t\t\t<div class=\"system-auth-form__item-logo--image --notification\"></div>\n\t\t\t\t\t</div>\n\t\t\t\t\t<div class=\"system-auth-form__item-container --center\">\n\t\t\t\t\t\t<div class=\"system-auth-form__item-title --light\">", "</div>\n\t\t\t\t\t</div>\n\t\t\t\t</div>\n\t\t\t"])), main_core.Loc.getMessage('AUTH_NOTIFICATION'));
	    div.addEventListener('click', function () {
	      _this14.hide();

	      BXIM.openSettings({
	        'onlyPanel': 'notify'
	      });
	    });
	    return div;
	  });
	}

	function _getLogoutContainer2() {
	  return babelHelpers.classPrivateFieldGet(this, _cache).remember('getLogoutContainer', function () {
	    var backUrl = new main_core.Uri(window.location.pathname);
	    backUrl.removeQueryParam(['logout', 'login', 'back_url_pub', 'user_lang']);
	    var newUrl = new main_core.Uri('/auth/?logout=yes');
	    newUrl.setQueryParam('sessid', BX.bitrix_sessid());
	    newUrl.setQueryParam('backurl', encodeURIComponent(backUrl.toString())); //TODO   

	    return main_core.Tag.render(_templateObject24 || (_templateObject24 = babelHelpers.taggedTemplateLiteral(["\n\t\t\t\t<div class=\"system-auth-form__item system-auth-form__scope --padding-sm\">\n\t\t\t\t\t<div class=\"system-auth-form__item-logo\">\n\t\t\t\t\t\t<div class=\"system-auth-form__item-logo--image --logout\"></div>\n\t\t\t\t\t</div>\n\t\t\t\t\t<div class=\"system-auth-form__item-container --center\">\n\t\t\t\t\t\t<div class=\"system-auth-form__item-title --light\">", "</div>\n\t\t\t\t\t</div>\n\t\t\t\t\t<a href=\"", "\" class=\"system-auth-form__item-link-all\"></a>\n\t\t\t\t</div>\n\t\t\t"])), main_core.Loc.getMessage('AUTH_LOGOUT'), newUrl.toString());
	  });
	}

	babelHelpers.defineProperty(Widget, "instance", null);

	exports.Widget = Widget;

}((this.BX.Intranet.UserProfile = this.BX.Intranet.UserProfile || {}),BX.UI,BX.Main,BX,BX,BX.Event,BX.UI));
//# sourceMappingURL=script.js.map
