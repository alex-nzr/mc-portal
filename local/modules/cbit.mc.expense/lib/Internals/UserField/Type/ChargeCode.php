<?php
/**
 * ==================================================
 * Developer: Alexey Nazarov
 * E-mail: alsnazarov@1cbit.ru
 * Copyright (c) 2019 - 2023
 * ==================================================
 * mc-portal - ChargeCode.php
 * 01.02.2023 12:51
 * ==================================================
 */


namespace Cbit\Mc\Expense\Internals\UserField\Type;

use Bitrix\Crm\Item;
use Bitrix\Main\UserField\Types\BaseType;
use Cbit\Mc\Expense\Config\Configuration;
use Cbit\Mc\Expense\Service\Container;
use Cbit\Mc\Expense\Service\Integration\UI\EntitySelector\ChargeCodeProvider;
use CUserTypeManager;
use CUtil;

/**
 * @class ChargeCode
 * @package Cbit\Mc\Expense\Internals\UserField\Type
 */
class ChargeCode extends BaseType
{
    public const USER_TYPE_ID = ChargeCodeProvider::ENTITY_TYPE;
    public const RENDER_COMPONENT = '';

    /**
     * @return array
     */
    public static function getDescription(): array
    {
        return [
            'DESCRIPTION'   => '(cbit)Charge code(expense)',
            'BASE_TYPE'     => CUserTypeManager::BASE_TYPE_ENUM,
            'EDIT_CALLBACK' => [static::class, 'renderEdit'],
            'VIEW_CALLBACK' => [static::class, 'renderView'],
            'USE_FIELD_COMPONENT' => false
        ];
    }

    /**
     * @return string
     */
    public static function getDbColumnType(): string
    {
        return 'int(18)';
    }

    /**
     * @param $userField
     * @param array|null $additionalParameters
     * @param $varsFromForm
     * @return string
     */
    public static function getSettingsHtml($userField, ?array $additionalParameters, $varsFromForm): string
    {
        return '';
    }

    /**
     * @param array $userField
     * @param array|null $additionalParameters
     * @return string
     * @throws \Exception
     */
    public static function renderView(array $userField, ?array $additionalParameters = []): string
    {
        if (!empty($userField['VALUE']))
        {
            $items = static::getItemsByUfValue(is_array($userField['VALUE']) ? $userField['VALUE'] : [$userField['VALUE']]);

            if (empty($items))
            {
                return 'no items to display';
            }

            ob_start();
            foreach ($items as $item)
            {
                ?>
                <div style="margin-top: 10px">
                    <a href="<?=$item['LINK']?>"><?=$item['TITLE']?></a>
                </div>
                <?php
            }
            $content = ob_get_contents();
            ob_end_clean();
            return $content;
        }

        return 'not filled';
    }

    /**
     * @param array $userField
     * @param array|null $additionalParameters
     * @return string
     * @throws \Exception
     */
    public static function renderEdit(array $userField, ?array $additionalParameters = []): string
    {
        if($userField['EDIT_IN_LIST'] === 'Y')
        {
            $containerId = 'container_' . $userField['FIELD_NAME'];
            $placementId = 'placement_' . $userField['FIELD_NAME'];
            if (!empty($userField['VALUE']))
            {
                $userField['VALUE_ITEMS'] = static::getItemsByUfValue(is_array($userField['VALUE']) ? $userField['VALUE'] : [$userField['VALUE']]);
            }

            $userField['CONTAINER_ID'] = $containerId;
            $userField['PLACEMENT_ID'] = $placementId;
            ob_start();
            ?>
            <div id="<?=$containerId?>" data-has-input="no">
                <div id="<?=$placementId?>"></div>
            </div>
            <script>
                BX.ready(function ()
                {
                    if (!BX.Cbit?.Mc?.Expense?.ChargeCodeSelector)
                    {
                        console.error('Extension of userField "<?=$userField['USER_TYPE_ID']?>" not found');
                    }
                    else
                    {
                        BX.Cbit?.Mc?.Expense?.ChargeCodeSelector?.showSelector(<?=CUtil::PhpToJSObject($userField)?>);
                    }
                });
            </script>
            <?php
            $content = ob_get_contents();
            ob_end_clean();
            return $content;
        }

        return 'not editable';
    }

    /**
     * @param array $ids
     * @return array
     * @throws \Exception
     */
    private static function getItemsByUfValue(array $ids): array
    {
        $items = [];
        $staffingEntityTypeId = Configuration::getInstance()->getStaffingEntityTypeId();
        $staffingFactory = $staffingEntityTypeId ? Container::getInstance()->getFactory($staffingEntityTypeId) : null;

        if ($staffingFactory !== null)
        {
            $result = $staffingFactory->getDataClass()::query()
                ->setFilter(['ID' => $ids])
                ->setSelect([Item::FIELD_NAME_ID, Item::FIELD_NAME_TITLE])
                ->fetchAll();

            foreach ($result as $item)
            {
                $items[] = [
                    'ID'    => $item[Item::FIELD_NAME_ID],
                    'TITLE' => $item[Item::FIELD_NAME_TITLE],
                    'LINK'  => Container::getInstance()->getRouter()->getItemDetailUrl(
                        $staffingEntityTypeId, $item[Item::FIELD_NAME_ID]
                    ),
                ];
            }
        }

        return $items;
    }
}