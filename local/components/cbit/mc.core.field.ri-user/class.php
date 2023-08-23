<?php
namespace Cbit\Mc\Core\Component;
if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die();

use Bitrix\Main\Component\BaseUfComponent;
use Bitrix\Intranet\UserField\Types\EmployeeType;
use Bitrix\Main\Localization\Loc;

Loc::loadMessages(__FILE__);

class CbitRIUserUfComponent extends BaseUfComponent
{
	public const SELECTOR_CONTEXT = 'USERFIELD_TYPE_RI_USER';

	protected static function getUserTypeId(): string
	{
		return EmployeeType::USER_TYPE_ID;
	}
}