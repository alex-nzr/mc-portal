<?php
/**
 * ==================================================
 * Developer: Alexey Nazarov
 * E-mail: alsnazarov@1cbit.ru
 * Copyright (c) 2019 - 2022
 * ==================================================
 * mc-portal - ExternalParticipant.php
 * 30.01.2023 15:07
 * ==================================================
 */
namespace Cbit\Mc\Expense\Internals\UserField\Type;

use Bitrix\Main\UserField\Types\BaseType;
use Cbit\Mc\Expense\Internals\Model\ExternalParticipantsTable;
use Cbit\Mc\Expense\Service\Integration\UI\EntitySelector\ExternalParticipantsProvider;
use CUserTypeManager;
use CUtil;

/**
 * @class ExternalParticipant
 * @package Cbit\Mc\Expense\Internals\UserField\Type
 */
class ExternalParticipant extends BaseType
{
    public const USER_TYPE_ID = ExternalParticipantsProvider::ENTITY_TYPE;
    public const RENDER_COMPONENT = '';

    /**
     * @return array
     */
    public static function getDescription(): array
    {
        return [
            'DESCRIPTION' => '(cbit)Внешний участник(expense)',
            'BASE_TYPE'   => CUserTypeManager::BASE_TYPE_ENUM,
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
     * @param array|bool $userField
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
     * @param $value
     * @return string|null
     */
    public static function onBeforeSave(array $userField, $value): ?string
    {
        return $value;
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
                    <span><?=$item['NAME']?> </span>
                    <span><?=$item['SECOND_NAME']?> </span>
                    <span><?=$item['LAST_NAME']?>, </span>
                    <span><?=$item['COMPANY']?>, </span>
                    <span><?=$item['POSITION']?></span>
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
                    if (!BX.Cbit?.Mc?.Expense?.ExternalParticipant)
                    {
                        console.error('Extension of userField "<?=$userField['USER_TYPE_ID']?>" not found');
                    }
                    else
                    {
                        BX.Cbit?.Mc?.Expense?.ExternalParticipant?.showSelector(<?=CUtil::PhpToJSObject($userField)?>);
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
     * @throws \Bitrix\Main\ArgumentException
     * @throws \Bitrix\Main\ObjectPropertyException
     * @throws \Bitrix\Main\SystemException
     */
    private static function getItemsByUfValue(array $ids): array
    {
        return ExternalParticipantsTable::query()
            ->setFilter(['ID' => $ids])
            ->setSelect(['*'])
            ->fetchAll();
    }
}