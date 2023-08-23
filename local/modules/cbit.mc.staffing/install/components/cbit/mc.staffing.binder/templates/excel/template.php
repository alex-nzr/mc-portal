<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED!==true)die();

/**
 * @var array $arParams
 * @var array $arResult
 * @var \CBitrixComponentTemplate $this
 * @global \CMain $APPLICATION
 * @global \CUser $USER
 * @global \CDatabase $DB
 */
$APPLICATION->RestartBuffer();

Header('Content-Type: application/vnd.ms-excel');
Header('Content-Disposition: attachment;filename='.$arResult['FILE_NAME'].'.xls');
Header('Content-Type: application/octet-stream');
Header('Content-Transfer-Encoding: binary');
?>
<html lang="<?=LANGUAGE_ID;?>">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
    <title></title>
</head>
<body>
<table border="1">
    <thead>
    <tr>
        <?php foreach ($arResult['HEADERS'] as $header):?>
            <th><b><?=$header?></b></th>
        <?php endforeach;?>
    </tr>
    </thead>
    <tbody>
    <?php foreach ($arResult['ITEMS'] as $item):?>
        <tr>
            <?php foreach ($arResult['HEADERS'] as $header):?>
                <td><?=$item[$header]?></td>
            <?php endforeach;?>
        </tr>
    <?php endforeach;?>
    </tbody>
</table>
</body>
</html>