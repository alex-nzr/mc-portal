//import {Event as EventBX} from 'main.core';
import {MessageBox, MessageBoxButtons} from 'ui.dialogs.messagebox';

export class MainUI
{
	constructor(options = {})
	{
		this.options = options;
		this.init();
	}

	init()
	{
		this.addEvents();
		this.removeLockedCrmTabs();
	}

	addEvents()
	{
		/*EventBX.EventEmitter.subscribe('BX.Main.Popup:onShow', (event) => {

		})*/

		BX.addCustomEvent("SidePanel.Slider:onOpenComplete", function(event) {
			if (event.slider?.url?.indexOf('/company/personal/user/') !== -1)
			{
				if (BX.type.isDomNode(event.slider?.layout?.container))
				{
					event.slider.layout.container.style.maxWidth = 'none';
					event.slider.layout.container.style.width = 'calc(100% - 50px)';
				}
			}
		});

		BX.addCustomEvent("SidePanel.Slider:onLoad", function(event) {
			if (event.slider?.url?.indexOf('/company/personal/user/') !== -1)
			{
				if (BX.type.isDomNode(event.slider?.layout?.container))
				{
					event.slider.layout.container.style.maxWidth = 'none';
					event.slider.layout.container.style.width = 'calc(100% - 50px)';
				}
			}
		});
	}

	showErrorPopup(error) {
		if (!error)
		{
			error = 'Operation ERROR';
		}

		MessageBox.show(
			{
				message: `<div style="color: red;text-align: center;">${error}</div>`,
				modal: true,
				buttons: MessageBoxButtons.CANCEL,
				cancelCaption: 'Got it',
				onCancel: (messageBox) => {
					messageBox.close();
				},
			}
		);
	}

	showSuccessPopup (message = false){
		if (!message)
		{
			message = 'Operation successful';
		}

		MessageBox.show(
			{
				message: `<div style="text-align: center;">${message}</div>`,
				modal: true,
				buttons: MessageBoxButtons.OK,
				onOk: (messageBox) => {
					messageBox.close();
				},
			}
		);
	}

	removeLockedCrmTabs() {
		const marketTabBtn = document.querySelector(`[data-id='crm_rest_marketplace']`);
		marketTabBtn && marketTabBtn.remove();
	}
}