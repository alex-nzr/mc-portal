//import {Event as EventBX} from 'main.core';

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
	}

	addEvents()
	{
		/*EventBX.EventEmitter.subscribe('BX.Main.Popup:onShow', (event) => {

		})*/
	}
}