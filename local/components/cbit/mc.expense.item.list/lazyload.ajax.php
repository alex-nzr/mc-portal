<?php

use Bitrix\Crm\Service\Container;
use Bitrix\Main\Context;

const NO_KEEP_STATISTIC = 'Y';
const NO_AGENT_STATISTIC = 'Y';
const NO_AGENT_CHECK = true;
const PUBLIC_AJAX_MODE = true;
const DisableEventsCheck = true;

$siteID = isset($_REQUEST['site'])? mb_substr(preg_replace('/[^a-z0-9_]/i', '', $_REQUEST['site']), 0, 2) : '';
if($siteID !== '')
{
	define('SITE_ID', $siteID);
}

require_once($_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/main/include/prolog_before.php');
if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true)
{
	die();
}

if (!CModule::IncludeModule('crm') || !CCrmSecurityHelper::IsAuthorized() || !check_bitrix_sessid())
{
	die();
}

$entityTypeId       = (int)Context::getCurrent()->getRequest()->get('entityTypeId');
$parentEntityTypeId = (int)Context::getCurrent()->getRequest()->get('parentEntityTypeId');
$parentEntityId     = (int)Context::getCurrent()->getRequest()->get('parentEntityId');

if (!CCrmOwnerType::isUseDynamicTypeBasedApproach($entityTypeId) || $parentEntityTypeId <= 0 || $parentEntityId <= 0)
{
	die();
}

if (!Container::getInstance()->getUserPermissions()->checkReadPermissions($parentEntityTypeId, $parentEntityId))
{
	die();
}

global $APPLICATION;
Header('Content-Type: text/html; charset='.LANG_CHARSET);
$APPLICATION->ShowAjaxHead();

$APPLICATION->IncludeComponent('cbit:mc.ri.item.list',
	'',
	[
		'entityTypeId' => $entityTypeId,
		'parentEntityTypeId' => $parentEntityTypeId,
		'parentEntityId' => $parentEntityId,
		'backendUrl' => Container::getInstance()->getRouter()->getChildrenItemsListUrl(
			$entityTypeId,
			$parentEntityTypeId,
			$parentEntityId
		),
	],
	false,
	[
		'HIDE_ICONS' => 'Y',
		'ACTIVE_COMPONENT' => 'Y',
	]
);

require_once($_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/main/include/epilog_after.php');
CMain::FinalActions();