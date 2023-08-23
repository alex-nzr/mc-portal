<?php

if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die();

use Bitrix\Main\Localization\Loc;
use Bitrix\Main\UserTable;

/**
 * @var array $arResult
 */

try
{
    foreach($arResult['value'] as $key => $item)
    {
        if (!empty($item['userId']))
        {
            $userData = UserTable::query()
                ->setSelect(['PERSONAL_MOBILE', 'EMAIL'])
                ->setFilter(['ID' => $item['userId']])
                ->fetch();
            if (!empty($userData))
            {
                $arResult['value'][$key]['phone'] = $userData['PERSONAL_MOBILE'];
                $arResult['value'][$key]['email'] = $userData['EMAIL'];
            }
        }
    }
}
catch(\Exception $e){}

?>
<span class="cbit-employee fields employee field-wrap">
	<?php
	foreach($arResult['value'] as $item)
	{
		$style = null;
		if($item['personalPhoto'])
		{
			$style = 'style="background-image:url(\'' . htmlspecialcharsbx($item['personalPhoto']) . '\'); background-size: 30px;"';
		}
		?>
		<span class="fields employee field-item">
			<?php
			if (empty($item['disabled']))
			{
				?>
				<span class="crm-widget-employee-change">
					<?= Loc::getMessage('INTRANET_FIELD_EMPLOYEE_CHANGE') ?>
				</span>
				<?php
			}
			?>
			<a
				class="uf-employee-wrap"
				href="<?= $item['href'] ?>"
				target="_blank"
			>
				<span
					class="uf-employee-image"
					<?= ($style ?? '') ?>
				>
				</span>
				<span class="uf-employee-data">
					<span class="uf-employee-name">
						<?= $item['name'] ?>
					</span>
					<span class="uf-employee-position">
						<?=Loc::getMessage('EMPLOYEE_FIELD_EMAIL')?> <b style="color:#000;opacity: .8;"><?= $item['email'] ?></b>
					</span>
                    <span class="uf-employee-position">
						<?=Loc::getMessage('EMPLOYEE_FIELD_PHONE')?> <b style="color:#000;opacity: .8;"><?= $item['phone'] ?></b>
					</span>
				</span>
			</a>
		</span>
	<?php } ?>
</span>
