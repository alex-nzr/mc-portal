<?php
/**
 * ==================================================
 * Developer: Alexey Nazarov
 * E-mail: jc1988x@gmail.com
 * Copyright (c) 2019 - 2023
 * ==================================================
 * mc-portal - FileType.php
 * 25.02.2023 13:37
 * ==================================================
 */
namespace Cbit\Mc\Core\Internals\UserField\Type;

use Bitrix\Crm\Item;
use Bitrix\Crm\Service\Container;
use Bitrix\Main\Event;
use Bitrix\Main\EventManager;
use Bitrix\Main\UI\Extension;
use CFile;
use CUserTypeManager;
use CUtil;
use Exception;

/**
 * @class FileType
 * @package Cbit\Mc\Core\Internals\UserField\Type
 */
class FileType extends \Bitrix\Main\UserField\Types\FileType
{
    public const
        USER_TYPE_ID = 'cbit-file',
        RENDER_COMPONENT = '';

    const MODE_ADMIN_SETTINGS = 'main.admin_settings';

    /**
     * @return array
     */
    public static function getDescription(): array
    {
        return [
            'DESCRIPTION' => '(cbit)file',
            'BASE_TYPE' => CUserTypeManager::BASE_TYPE_FILE,
            'EDIT_CALLBACK' => [static::class, 'renderEdit'],
            'VIEW_CALLBACK' => [static::class, 'renderView'],
            'USE_FIELD_COMPONENT' => false
        ];
    }

    /**
     * @param $userField
     * @param array|null $additionalParameters
     * @param $varsFromForm
     * @return string
     */
    public static function getSettingsHtml($userField, ?array $additionalParameters, $varsFromForm): string
    {
        $additionalParameters['mode'] = static::MODE_ADMIN_SETTINGS;
        $additionalParameters['bVarsFromForm'] = $varsFromForm;

        global $APPLICATION;
        ob_start();
        $APPLICATION->IncludeComponent(
            'bitrix:main.field.file',
            '',
            [
                'userField' => $userField,
                'additionalParameters' => $additionalParameters,
            ]
        );
        return ob_get_clean();
    }

    /**
     * @param array $userField
     * @param array|null $additionalParameters
     * @return string
     */
    public static function renderView(array $userField, ?array $additionalParameters = []): string
    {
        if (!empty($userField['VALUE']))
        {
            if (!is_array($userField['VALUE']))
            {
                $userField['VALUE'] = [$userField['VALUE']];
            }

            ob_start();
            foreach ($userField['VALUE'] as $val)
            {
                if (is_numeric($val) && (int)$val > 0)
                {
                    $arFile = CFile::GetFileArray($val);
                    if (!empty($arFile))
                    {
                        $fileLink = $arFile['SRC'];
                        $fileName = $arFile['FILE_NAME'];
                        if (CFile::IsImage($fileName))
                        {
                            echo "<div style='display: flex;align-items: center;margin: 5px 0;white-space: nowrap'>
                                      <img src='$fileLink' alt='image' style='max-width: 85%;max-height:100px;'>
                                      &nbsp;<a href='$fileLink' target='_blank'>Open</a>&nbsp;
                                  </div>";
                        }
                        else
                        {
                            echo "<div style='display: flex;align-items: center;margin: 5px 0;'>
                                      <span 
                                        style='display: inline-block;overflow: hidden;max-width: 100%;text-overflow: ellipsis;'
                                      >
                                        $fileName &nbsp;
                                      </span> <a href='$fileLink' download='$fileName'>Download</a>
                                  </div>";
                        }
                    }
                    else
                    {
                        echo "<div style='color: red;margin: 5px 0;white-space: nowrap'>
                                File '$val' not found. Try to upload again
                              </div>";
                    }
                }
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
        Extension::load(['cbit.mc.core.file-input']);
        if($userField['EDIT_IN_LIST'] === 'Y')
        {
            $userField['VALUE_ITEMS'] = [];

            $fileInputId   = 'input_' . $userField['FIELD_NAME'];
            $containerId   = 'container_' . $userField['FIELD_NAME'];
            $valuesBlockId = 'placement_' . $userField['FIELD_NAME'];
            $ufInputName   = $userField['MULTIPLE'] === 'Y' ? $userField['FIELD_NAME'].'[]' : $userField['FIELD_NAME'];
            if (!empty($userField['VALUE']))
            {
                $values = is_array($userField['VALUE']) ? $userField['VALUE'] : [$userField['VALUE']];
                foreach ($values as $value)
                {
                    if (is_numeric($value))
                    {
                        $fileData = CFile::GetByID($value)->Fetch();
                        if (is_array($fileData) && !empty($fileData))
                        {
                            $userField['VALUE_ITEMS'][] = $fileData;
                        }
                    }
                }
            }

            $userField['CONTAINER_ID']    = $containerId;
            $userField['FILE_INPUT_ID']   = $fileInputId;
            $userField['VALUES_BLOCK_ID'] = $valuesBlockId;
            $userField['UF_INPUT_NAME']   = $ufInputName;

            ob_start();
            ?>
            <div id="<?=$containerId?>">
                <label class="ui-ctl ui-ctl-file-btn">
                    <input type="file" class="ui-ctl-element" id="<?=$fileInputId?>">
                    <span class="ui-ctl-label-text">Add file</span>
                </label>
                <div id="<?=$valuesBlockId?>"></div>
                <input type="hidden" name="<?=$ufInputName?>" data-role="empty-value-input">
            </div>
            <script>
                BX.ready(function ()
                {
                    if (!BX.Cbit?.Mc?.Core?.FileInput)
                    {
                        console.error('Extension FileInput for userField "<?=$userField['USER_TYPE_ID']?>" not found');
                    }
                    else
                    {
                        BX.Cbit?.Mc?.Core?.FileInput?.init(<?=CUtil::PhpToJSObject($userField)?>);
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
     * @param array $userField
     * @param $value
     * @return mixed
     * @throws \Exception
     */
    public static function onBeforeSave(array $userField, $value): mixed
    {
        $entityId = $userField['ENTITY_ID'];
        $typeId = str_replace('CRM_', '', $entityId);
        if (is_numeric($typeId) && !is_null(Container::getInstance()->getType($typeId)))
        {
            EventManager::getInstance()->addEventHandler(
                'crm',
                'onCrmDynamicItemUpdate',
                function (Event $event) use ($userField){
                    FileType::deleteFilesFromDB($userField, $event->getParameter("item"));
                }
            );
            return $value;
        }
        else
        {
            //TODO add logic for not dynamic entities
            throw new Exception(
                    "Field" . static::USER_TYPE_ID ." available only for dynamic entities."
                . "For " . $userField['ENTITY_ID'] . " please use the field of type 'file'"
            );
        }
    }

    /**
     * @param array $userField
     * @param \Bitrix\Crm\Item|null $item
     * @return void
     * @throws \Exception
     */
    protected static function deleteFilesFromDB(array $userField, ?Item $item): void
    {
        if (($item instanceof Item) && $item->hasField($userField['FIELD_NAME']))
        {
            $oldValue = $userField['VALUE'];
            $newValue = $item->get($userField['FIELD_NAME']);
            $filesToDelete = array_diff($oldValue, $newValue);
            if (!empty($filesToDelete))
            {
                foreach ($filesToDelete as $fileId)
                {
                    CFile::Delete($fileId);
                }
            }
        }
    }

    /**
     * @param array $userField
     * @return string|null
     */
    public static function onSearchIndex(array $userField): ?string
    {
        return null;
    }
}