//import {Event as EventBX} from 'main.core';

export class MainRiUI
{
	constructor(options = {})
	{
		this.currentCoordinator    = options['coordinator'];
		this.riCustomSectionMore   = BX('menu_custom_section_ri_more_button');
		this.init();
	}

	init()
	{
		this.addEvents();
		this.insertCurrentCoordinatorToPanel();
	}

	addEvents()
	{
	}

	insertCurrentCoordinatorToPanel(currentCoordinatorData = false) {
		if (currentCoordinatorData !== false)
		{
			this.currentCoordinator = currentCoordinatorData;
		}

		if(Object.keys(this.currentCoordinator).length > 0)
		{
			if (this.riCustomSectionMore)
			{
				this.coordinatorPanelBlock = BX('coordinator-current-panel-block');

				if (!this.coordinatorPanelBlock)
				{
					this.coordinatorPanelBlock = BX.create('div', {
						props: {
							id: 'coordinator-current-panel-block'
						}
					});
					this.riCustomSectionMore.after(this.coordinatorPanelBlock);
				}

				this.coordinatorPanelBlock.innerHTML = '';
				const title = BX.create('h3', {
					text: 'Coordinator today'
				});
				const link = BX.create('a', {
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
								className: 'coordinator-info'
							},
							children: [
								BX.create('span', {
									props: {
										className: 'coordinator-name'
									},
									html: `${this.currentCoordinator['NAME']}<br>`
								}),
								BX.create('span', {
									props: {
										className: 'coordinator-email'
									},
									html: `${this.currentCoordinator['EMAIL']}<br>`
								}),
								BX.create('span', {
									props: {
										className: 'coordinator-phone'
									},
									html: `${this.currentCoordinator['PERSONAL_PHONE']}`
								})
							]
						})
					]
				});

				this.coordinatorPanelBlock.append(title);
				this.coordinatorPanelBlock.append(link);
			}
		}
	}
}