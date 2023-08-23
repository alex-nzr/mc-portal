<?php
/**
 * ==================================================
 * Developer: Alexey Nazarov
 * E-mail: alsnazarov@1cbit.ru
 * Copyright (c) 2019 - 2023
 * ==================================================
 * mc-portal - ExpenseItemList.php
 * 15.02.2023 19:40
 * ==================================================
 */
namespace Cbit\Mc\Expense\Component;

use Bitrix\Crm\Item;
use Bitrix\Crm\PhaseSemantics;
use Bitrix\Crm\Restriction\RestrictionManager;
use Bitrix\Main\Engine\CurrentUser;
use Bitrix\Main\Localization\Loc;
use Bitrix\UI\Buttons\Button;
use Bitrix\UI\Buttons\Color;
use Bitrix\UI\Buttons\Icon;
use Bitrix\UI\Toolbar\ButtonLocation;
use Bitrix\UI\Toolbar\Facade\Toolbar;
use Cbit\Mc\Expense\Config\Constants;
use Cbit\Mc\Expense\Entity\Dynamic;
use Cbit\Mc\Expense\Filter\ItemDataProvider;
use Cbit\Mc\Expense\Internals\Control\ServiceManager;
use Cbit\Mc\Expense\Service\Container;
use CBitrixComponent;
use CrmItemListComponent;

CBitrixComponent::includeComponentClass('bitrix:crm.item.list');

/**
 * @class ExpenseItemList
 * @package Cbit\Mc\Expense\Component
 */
class ExpenseItemList extends CrmItemListComponent
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
     * @throws \Exception
     */
    protected function init(): void
    {
        parent::init();
        $categoryId   = $this->getCategoryId();
        //выводим сообщение всегда, так как отключили переключатель воронок
        /*if ($categoryId > 0)
        {
            $categoryCode = Dynamic::getInstance()->getCategoryCodeById((int)$categoryId);

            if ($categoryCode === Constants::DYNAMIC_CATEGORY_TR_YOUR_BUDGET_CODE)
            {
                $this->arResult['HAS_PERMS_TO_ADD_TYB'] = Container::getInstance()->getUserPermissions()->checkAddPermissions(
                    $this->entityTypeId, $categoryId
                );
            }
        }*/
        $this->arResult['HAS_PERMS_TO_ADD_TYB'] = Container::getInstance()->getUserPermissions()->checkAddPermissions(
            $this->entityTypeId, Dynamic::getInstance()->getCategoryIdByCode(Constants::DYNAMIC_CATEGORY_TR_YOUR_BUDGET_CODE)
        );
    }

    /**
     * @return array[]
     * @throws \Exception
     */
    protected function getDefaultFilterPresets(): array
    {
        //$presets = parent::getDefaultFilterPresets();
        $presets = [];
        $userHasExpenseRights = Container::getInstance()->getUserPermissions()->hasUserAnyPermissionsForExpense();

        $rejectedStages = [
            Dynamic::getInstance()->getStatusPrefix(Dynamic::getInstance()->getCategoryIdByCode(
                Constants::DYNAMIC_CATEGORY_DEFAULT_CODE
            )) . Constants::DYNAMIC_STAGE_DEFAULT_REJECTED,
            Dynamic::getInstance()->getStatusPrefix(Dynamic::getInstance()->getCategoryIdByCode(
                Constants::DYNAMIC_CATEGORY_DAILY_ALLOWANCE_CODE
            )) . Constants::DYNAMIC_STAGE_DAILY_ALLOWANCE_REJECTED,
            Dynamic::getInstance()->getStatusPrefix(Dynamic::getInstance()->getCategoryIdByCode(
                Constants::DYNAMIC_CATEGORY_TR_YOUR_BUDGET_CODE
            )) . Constants::DYNAMIC_STAGE_TR_YOUR_BUDGET_REJECTED,
        ];

        /*$submittedStages = [
            Dynamic::getInstance()->getStatusPrefix(Dynamic::getInstance()->getCategoryIdByCode(
                Constants::DYNAMIC_CATEGORY_DEFAULT_CODE
            )) . Constants::DYNAMIC_STAGE_DEFAULT_SUBMITTED,
            Dynamic::getInstance()->getStatusPrefix(Dynamic::getInstance()->getCategoryIdByCode(
                Constants::DYNAMIC_CATEGORY_DAILY_ALLOWANCE_CODE
            )) . Constants::DYNAMIC_STAGE_DAILY_ALLOWANCE_SUBMITTED,
            Dynamic::getInstance()->getStatusPrefix(Dynamic::getInstance()->getCategoryIdByCode(
                Constants::DYNAMIC_CATEGORY_TR_YOUR_BUDGET_CODE
            )) . Constants::DYNAMIC_STAGE_TR_YOUR_BUDGET_SUBMITTED,
        ];*/

        $presets['active'] = [
            'name' => 'Active',
            'default' => !$userHasExpenseRights,
            'disallow_for_all' => false,
            'fields' => [
                ItemDataProvider::FIELD_STAGE_SEMANTIC => PhaseSemantics::getProcessSemantis(),
            ]
        ];

        if ($userHasExpenseRights)
        {
            /*$presets['submitted_preset'] = [
                'name' => 'Submitted',
                'default' => true,
                'disallow_for_all' => true,
                'fields' => [
                    'STAGE_ID' => $submittedStages
                ],
            ];*/
            $presets['my'] = [
                'name' => 'My',
                'default' => true,
                'disallow_for_all' => true,
                'fields' => [
                    'ASSIGNED_BY_ID_name' => $this->userName,
                    'ASSIGNED_BY_ID'      => $this->userId,
                ],
            ];
        }
        else
        {
            $presets['rejected'] = [
                'name' => 'Rejected',
                'default' => false,
                'disallow_for_all' => true,
                'fields' => [
                    'CREATED_BY' => $this->userId,
                    'STAGE_ID'   => $rejectedStages
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
        return 'expense-' . parent::getGridId();
    }

    /**
     * @return array
     * @throws \Exception
     */
    protected function getToolbarParameters(): array
    {
        $params = parent::getToolbarParameters();

        if (array_key_exists('buttons', $params))
        {
            if (!is_array($params['buttons']))
            {
                $params['buttons'] = [];
            }
        }
        else
        {
            $params['buttons'] = [];
        }

        $params['buttons'][ButtonLocation::AFTER_TITLE] = [
            new Button($this->getAddButtonParameters())
        ];

        if (Container::getInstance()->getUserPermissions()->canUserUploadRatingFile())
        {
            Toolbar::addRightCustomHtml(
                '<form id="user-rating-csv-form" enctype="multipart/form-data">
                        <label class="ui-ctl ui-ctl-file-btn" style="margin-left: 20px;">
                            <input type="file" class="ui-ctl-element" id="user-rating-csv-input" name="UF_TYB_RATING_FILE">
                            <div class="ui-ctl-label-text" id="user-rating-upload-text">Upload rating</div>
                        </label>
                      </form>'
            );
        }

        if (array_key_exists('views', $params))
        {
            unset($params['views']);
        }

        return $params;
    }

    /**
     * @param bool $isDisabled
     * @return array
     * @throws \Exception
     */
    protected function getAddButtonParameters(bool $isDisabled = false): array
    {
        return [
            'color' => Color::SUCCESS,
            'className' => 'ui-btn ui-btn-success ui-btn-dropdown ui-toolbar-btn-dropdown',
            'text' => Loc::getMessage('CRM_COMMON_ACTION_CREATE'),
            'menu' => [
                'items' => $this->getAddButtonMenu(),
            ],
            'maxWidth' => '400px',
        ];
    }

    /**
     * @return array
     * @throws \Exception
     */
    protected function getAddButtonMenu(): array
    {
        $menu = [];
        foreach ($this->factory->getCategories() as $category)
        {
            if ($category->getCode() === Constants::DYNAMIC_CATEGORY_DAILY_ALLOWANCE_CODE)
            {
                continue;
            }

            if ($category->getCode() === Constants::DYNAMIC_CATEGORY_TR_YOUR_BUDGET_CODE)
            {
                if (!Container::getInstance()->getUserPermissions()->checkAddPermissions($this->entityTypeId, $category->getId()))
                {
                    continue;
                }
            }

            $link = Container::getInstance()->getRouter()->getItemDetailUrl(
                $this->entityTypeId, 0, $category->getId()
            )->getUri();

            $menu[] = [
                'id' => 'toolbar-category-' . $category->getId(),
                'categoryId' => $category->getId(),
                'text' => htmlspecialcharsbx($category->getName()),
                'href' => $link,
            ];
        }

        if ($this->userPermissions->isAdmin())
        {
            $menu[] = [
                'delimiter' => true,
            ];
            $menu[] = [
                'text' => Loc::getMessage('CRM_TYPE_CATEGORY_SETTINGS'),
                'href' => Container::getInstance()->getRouter()->getCategoryListUrl($this->entityTypeId),
            ];
        }

        return $menu;
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

        if ($userPermissions->isAdmin())
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