<?php
namespace Cbit\Mc\RI\Component;

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) die();

use Bitrix\Crm\Item;
use Bitrix\Crm\PhaseSemantics;
use Bitrix\Main\Engine\CurrentUser;
use Bitrix\Main\Localization\Loc;
use Cbit\Mc\RI\Internals\Control\ServiceManager;
use Cbit\Mc\RI\Internals\Debug\Logger;
use Cbit\Mc\RI\Service\Access\Permission;
use Cbit\Mc\RI\Service\Container;
use CBitrixComponent;
use CrmItemListComponent;
use CUtil;

CBitrixComponent::includeComponentClass('bitrix:crm.item.list');

class RiItemListComponent extends CrmItemListComponent
{
    protected int $userId = 0;
    protected string $userName = 'unknown';

    /**
     * @param $component
     */
    public function __construct($component = null)
    {
        parent::__construct($component);

        if (!empty($GLOBALS['USER']))
        {
            $this->userId = CurrentUser::get()->getId();
            $this->userName = CurrentUser::get()->getFormattedName();
        }

        ServiceManager::getInstance()->addListPageExtensions();
    }

    /**
     * @return void
     */
	protected function init(): void
	{
		parent::init();
	}

    /**
     * @return array
     * @throws \Bitrix\Main\ArgumentException
     * @throws \Bitrix\Main\ObjectNotFoundException
     */
	protected function getDefaultFilterPresets(): array
    {
        //$presets = parent::getDefaultFilterPresets();
        $presets = [];

        if (Container::getInstance()->getUserPermissions()->hasUserAnyPermissionsForRi())
        {
            $presets['only_my'] = [
                'name' => 'MY',
                'default' => true,
                'disallow_for_all' => true,
                'fields' => [
                    'ASSIGNED_BY_ID_name'              => $this->userName,
                    'ASSIGNED_BY_ID'                   => $this->userId,
                    Item::FIELD_NAME_STAGE_SEMANTIC_ID => PhaseSemantics::getProcessSemantis(),
                ],
            ];
        }

        return $presets;
    }

    /**
     * @return string
     */
    protected function getGridId(): string
    {
        return 'ri-' . parent::getGridId();
    }

    /**
     * @return array
     */
    protected function getToolbarParameters(): array
    {
        $params = parent::getToolbarParameters();
        unset($params['views']);
        return $params;
    }

    /**
     * @param \Bitrix\Crm\Item $item
     * @return array[]
     * @throws \Exception
     */
    protected function getContextActions(Item $item): array
    {
        $userPermissions = Container::getInstance()->getUserPermissions();

        $itemDetailUrl = Container::getInstance()->getRouter()->getItemDetailUrl($this->entityTypeId, $item->getId());
        $actions = [
            [
                'TEXT' => Loc::getMessage('CRM_COMMON_ACTION_SHOW'),
                'HREF' => $itemDetailUrl,
            ],
        ];

        if ($userPermissions->canAddItem($item))
        {
            $copyUrl = clone $itemDetailUrl;
            $copyUrl->addParams([
                'copy' => '1',
            ]);
            $actions[] = [
                'TEXT' => Loc::getMessage('CRM_COMMON_ACTION_COPY'),
                'HREF' => $copyUrl,
            ];
        }

        return $actions;
    }
}
