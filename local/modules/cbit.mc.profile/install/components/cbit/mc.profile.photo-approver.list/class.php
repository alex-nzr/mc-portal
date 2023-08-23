<?php
namespace Cbit\Mc\Profile\Component;

if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die();

use Bitrix\Main\Loader;
use Bitrix\Main\Localization\Loc;
use Bitrix\Main\UI\PageNavigation;
use Cbit\Mc\Core\Component\BaseComponent;
use Cbit\Mc\Profile\Internals\Model\Approval\PhotoQueueTable;
use Cbit\Mc\Profile\Service\Approval\PersonalPhoto;
use Exception;

/**
 * Class PhotoApproverList
 * @package Cbit\Mc\Profile\Component
 */
class PhotoApproverList extends BaseComponent
{
    public string $moduleId;

    public function __construct($component = null)
    {
        $this->moduleId = 'cbit.mc.profile';
        parent::__construct($component);
    }

    public function onPrepareComponentParams($arParams): array
    {
        return array_merge($arParams, [
            "CACHE_TYPE" => $arParams["CACHE_TYPE"] ?? "N",
            "CACHE_TIME" => $arParams["CACHE_TIME"] ?? 0,
        ]);
    }

    /**
     * @return array
     * @throws \Bitrix\Main\ArgumentException
     * @throws \Bitrix\Main\ObjectPropertyException
     * @throws \Bitrix\Main\SystemException
     */
    public function getResult(): array
    {
        $nav = new PageNavigation("nav-photo-approver");
        $nav->allowAllRecords(true)
            ->setPageSize(20)
            ->initFromUri();

        $query = PhotoQueueTable::query()
            ->setSelect(['ID', 'OLD_FILE_ID', 'NEW_FILE_ID', 'USER_ID', 'NAME' => 'USER.NAME', 'LAST_NAME' => 'USER.LAST_NAME'])
            ->setOrder(['ID' => 'ASC'])
            ->setOffset($nav->getOffset())
            ->setLimit($nav->getLimit())
            ->countTotal(true)
            ->exec();

        $nav->setRecordCount($query->getCount());

        $result = [
            'ITEMS'      => [],
            'NAV_OBJECT' => $nav
        ];

        $number = $nav->getOffset();
        while($elem = $query->fetch())
        {
            $number++;
            $fullName = $elem['NAME'] . " " . $elem['LAST_NAME'];
            $result['ITEMS'][] = [
                'NUMBER' => $number,
                'DETAIL_PAGE'       => PersonalPhoto::getInstance()->getApprovePageLink(
                    $elem['USER_ID'], $elem['NEW_FILE_ID'], $elem['OLD_FILE_ID']
                ),
                'USER_FULL_NAME'    => $fullName,
                'USER_PROFILE_LINK' => "/company/personal/user/" . $elem['USER_ID'] . "/"
            ];
        }

        return $result;
    }

    /**
     * @return bool
     * @throws \Exception
     */
    public function checkRequirements(): bool
    {
        if (!Loader::includeModule($this->moduleId))
        {
            throw new Exception("Can not include module " . $this->moduleId);
        }

        if (!PersonalPhoto::getInstance()->canCurrentUserApprovePhoto())
        {
            throw new Exception(Loc::getMessage($this->moduleId."_COMPONENT_ERROR_PERMISSIONS"));
        }

        return true;
    }
}