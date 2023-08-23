<?php
/**
 * ==================================================
 * Developer: Alexey Nazarov
 * E-mail: alsnazarov@1cbit.ru
 * Copyright (c) 2019 - 2022
 * ==================================================
 * mc-portal - BaseOptionManager.php
 * 23.01.2023 9:46
 * ==================================================
 */
namespace Cbit\Mc\Core\Config;

use Bitrix\Main\Config\Option;
use Bitrix\Main\Context;
use Bitrix\Main\Localization\Loc;
use Bitrix\Main\Request;
use Bitrix\Main\Web\Json;
use CAdminTabControl;
use CFile;
use Exception;

Loc::loadMessages($_SERVER['DOCUMENT_ROOT'] . BX_ROOT . "/modules/main/options.php");
Loc::loadMessages(__FILE__);

/**
 * @class BaseOptionManager
 * @package Cbit\Mc\Core\Config
 */
abstract class BaseOptionManager{

    protected Request $request;
    protected string  $moduleId;
    protected array   $tabs;
    protected string $formAction;
    public CAdminTabControl $tabControl;

    /**
     * @param string $moduleId
     * @throws \Exception
     */
    public function __construct(string $moduleId)
    {
        $this->request  = Context::getCurrent()->getRequest();
        $this->moduleId = $moduleId;
        $this->setTabs();
        $this->tabControl = new CAdminTabControl('tabControl', $this->tabs);
        $this->formAction = $this->request->getRequestedPage() . "?" . http_build_query([
            'mid'  => htmlspecialcharsbx($this->request->get('mid')),
            'lang' => $this->request->get('lang')
        ]);
    }

    /**
     * @return void
     */
    abstract protected function setTabs(): void;

    /**
     * @return void
     */
    public function processRequest(): void
    {
        try {
            if ($this->request->isPost() && $this->request->getPost('Update') && check_bitrix_sessid())
            {
                foreach ($this->tabs as $arTab)
                {
                    foreach ($arTab['OPTIONS'] as $arOption)
                    {
                        if(!is_array($arOption) || !empty($arOption['note']))
                        {
                            continue;
                        }
                        $optionName = $arOption[0];
                        $optionValue = $this->request->getPost($optionName);

                        $fileOptionPostfix = Constants::OPTION_TYPE_FILE_POSTFIX;
                        if (str_ends_with($optionName, $fileOptionPostfix))
                        {
                            $currentValue = Option::get($this->moduleId, $optionName);
                            $optionValue = $this->request->getFile($optionName);

                            if (empty($optionValue['name']) && !empty($currentValue)){
                                continue;
                            }

                            $arFile = $optionValue;
                            $arFile["MODULE_ID"] = $this->moduleId;

                            if (strlen($arFile["name"]) > 0)
                            {
                                $fid = CFile::SaveFile($arFile, $arFile["MODULE_ID"]);
                                $optionValue = (int)$fid > 0 ? $fid : '';
                            }
                        }
                        Option::set(
                            $this->moduleId,
                            $optionName,
                            is_array($optionValue) ? json_encode($optionValue) : $optionValue
                        );
                    }
                }
            }
        }
        catch (Exception $e){
            ShowError($e->getMessage());
        }
    }

    /**
     * @return void
     */
    public function startDrawHtml(): void
    {
        $this->tabControl->Begin();
        ?>
        <form method="POST" action="<?=$this->formAction?>" name="<?=$this->moduleId?>_settings" enctype="multipart/form-data">
        <?php
        foreach ($this->tabs as $arTab)
        {
            if(is_array($arTab['OPTIONS']))
            {
                $this->tabControl->BeginNextTab();
                $this->drawSettingsList($this->moduleId, $arTab['OPTIONS']);
            }
        }
    }

    /**
     * @return void
     */
    public function endDrawHtml(): void
    {
        $this->tabControl->Buttons();?>
        <?=bitrix_sessid_post();?>
        <input type="submit" name="Update" value="<?=Loc::getMessage('MAIN_SAVE')?>" class="adm-btn-save">
        <input type="reset"  name="reset" value="<?=Loc::getMessage('MAIN_RESET')?>">
        </form>
        <?php
        $this->tabControl->End();
    }

    /**
     * @param string $module_id
     * @param $option
     */
    protected function drawSettingsRow(string $module_id, $option): void
    {
        if(empty($option))return;

        if(!is_array($option))
        {
            echo "<tr class='heading'><td colspan='2'>$option</td></tr>";
        }
        elseif(isset($option["note"]))
        {
            echo    "<tr>
                        <td colspan='2'>
                            <div class='adm-info-message-wrap'>
                                <div class='adm-info-message'>{$option["note"]}</div>
                            </div>
                        </td>
                    </tr>";
        }
        else
        {
            $currentVal = Option::get($module_id, $option[0], $option[2]);
            echo "<tr>";
            $this->renderTitle($option[1]);
            $this->renderInput($option, $currentVal ?? '');
            echo "</tr>";
        }
    }

    /**
     * @param string $module_id
     * @param array $arParams
     */
    protected function drawSettingsList(string $module_id, array $arParams): void
    {
        foreach($arParams as $Option)
        {
            $this->drawSettingsRow($module_id, $Option);
        }
    }

    /**
     * @param string $text
     */
    protected function renderTitle(string $text): void
    {
        echo "<td><span>$text</span></td>";
    }

    /**
     * @param array $option
     * @param string $val
     */
    protected function renderInput(array $option, string $val): void
    {
        $name  = $option[0];
        $type  = $option[3];
        ?>
        <td style="width: 50%">
        <?if(!($type[0] === 'role')):?>
            <label for="<?=$name?>" class="module-option-label">
        <?endif;?>
            <?
            switch ($type[0])
            {
                case "checkbox":
                    $checked = ($val === "Y") ? "checked" : '';
                    echo "<input type='checkbox' id='$name' name='$name' value='Y' $checked>";
                    break;
                case "text":
                case "password":
                    $autocomplete = $type[0] === 'password' ? 'autocomplete="new-password"' : '';
                    echo "<input type='$type[0]' id='$name' name='$name' value='$val' size='$type[1]' maxlength='255' $autocomplete>";
                    break;
                case "number":
                    echo "<input type='number' name='$name' value='$val' size='$type[1]' min='1' max='999999'>";
                    break;
                case "select":
                    $arr = is_array($type[1]) ? $type[1] : [];
                    echo "<select name='$name'>";
                    foreach($arr as $optionVal => $displayVal)
                    {
                        $selected = ($val === $optionVal) ? "selected" : '';
                        echo "<option value='$optionVal' $selected>$displayVal</option>";
                    }
                    echo "</select>";
                    break;
                case "multiselect":
                    $arr = is_array($type[1]) ? $type[1] : [];
                    $name .= '[]';
                    $arr_val = json_decode($val);
                    echo "<select name='$name' size='5' multiple>";
                    foreach($arr as $optionVal => $displayVal)
                    {
                        $selected = (in_array($optionVal, $arr_val)) ? "selected" : '';
                        echo "<option value='$optionVal' $selected>$displayVal</option>";
                    }
                    echo "</select>";
                    break;
                case "textarea":
                    echo "<textarea rows='$type[1]' cols='$type[2]' name='$name'>$val</textarea>";
                    break;
                case "staticText":
                    echo "<span>$val</span>";
                    break;
                case "colorPicker":
                    echo "<input type='text' id='$name' name='$name' value='$val' readonly/>
                              <script>
                                BX.ready(function() {
                                    if (BX.AdminSection?.OptionPage){
                                        BX.AdminSection.OptionPage.bindColorPickerToNode('$name', '$name', '$option[2]');
                                    }
                                });
                              </script>";
                    break;
                case "file":
                    if (is_numeric($val) && (int)$val > 0)
                    {
                        $arFile = CFile::GetFileArray($val);
                        if (!empty($arFile))
                        {
                            $fileLink = $arFile['SRC'];
                            $fileName = $arFile['FILE_NAME'];
                            if (CFile::IsImage($fileName))
                            {
                                echo "<div>
                                        <a href='$fileLink' download='$fileName'><img src='$fileLink' alt='image' width='200'></a>
                                      </div>";
                            }
                            else
                            {
                                echo "<div>
                                        <a href='$fileLink' download='$fileName'>$fileName</a>
                                      </div>";
                            }
                        }
                    }
                    echo "<input type='file' id='$name' name='$name'/>";
                    break;
                case "role":
                    $this->renderRolesList($name.'[]', $type[1], $val);
                    break;
            }
            ?>
        <?if(!($type[0] === 'role')):?>
            </label>
        <?endif;?>
        </td><?
    }

    /**
     * @param string $name
     * @param array $values
     * @param string $val
     * @return void
     * @throws \Bitrix\Main\ArgumentException
     */
    public function renderRolesList(string $name, array $values, string $val): void
    {
        $arr_val = Json::decode($val);
        if (!is_array($arr_val))
        {
            $arr_val = [];
        }

        echo "<div class='role-list-table'><table><tbody>";
        foreach($values as $optionVal => $displayVal)
        {
            $id = $name.$optionVal;
            echo "<tr>";
            $checked = (in_array($optionVal, $arr_val)) ? "checked" : '';
            echo "<td><input type='checkbox' id='$id' name='$name' value='$optionVal' $checked></td>";
            echo "<td><label for='$id'>$displayVal</label></td>";
            echo "</tr>";
        }
        echo "</tbody></table></div>";
    }
}