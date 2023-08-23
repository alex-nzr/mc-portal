<?php
namespace Cbit\Mc\Staffing\Component;

if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die();

use Bitrix\Main\Localization\Loc;
use Bitrix\Main\Type\Date;
use Cbit\Mc\Core\Component\BaseComponent;
use Cbit\Mc\Core\Helper\Iblock\IblockElement;
use Cbit\Mc\Core\Helper\Main\User;
use Cbit\Mc\Core\Service\Integration\Zup\Fields;
use Cbit\Mc\Staffing\Internals\Control\ServiceManager;
use Cbit\Mc\Staffing\Entity\Dynamic;
use Cbit\Mc\Staffing\Internals\Model\UserProjectTable;
use Cbit\Mc\Staffing\Service\Container;
use Exception;

/**
 * Class UserEmployment
 * @package Cbit\Mc\Staffing\Component
 */
class UserEmployment extends BaseComponent
{
    public   string $moduleId;
    private  int    $userId = 0;

    public function __construct($component = null)
    {
        $this->moduleId = ServiceManager::getModuleId();
        parent::__construct($component);
    }

    public function onPrepareComponentParams($arParams): array
    {
        if (!empty($arParams['USER_ID']))
        {
            $this->userId = (int)$arParams['USER_ID'];
        }

        return parent::onPrepareComponentParams($arParams);
    }

    /**
     * @return array
     * @throws \Exception
     */
    public function getResult(): array
    {
        $typeId = Dynamic::getInstance()->getTypeId();
        $data = UserProjectTable::query()
            ->setSelect([
                'ID', 'USER_ID', 'PROJECT_ID', 'USER_ROLE', 'USER_EMPLOYMENT_PERCENT',
                'USER_EMPLOYMENT_TYPE', 'STAFFING_DATE_FROM', 'STAFFING_DATE_TO',
                'NAME' => 'USER.NAME',
                'LAST_NAME' => 'USER.LAST_NAME',
                Fields::getFioEnUfCode() => 'USER.'.Fields::getFioEnUfCode(),
                'PROJECT_TITLE'          => 'PROJECT.TITLE',
                'PROJECT_CLIENT'         => 'PROJECT.UF_CRM_'.$typeId.'_CLIENT',
                'PROJECT_DESCRIPTION'    => 'PROJECT.UF_CRM_'.$typeId.'_DESCRIPTION',
                'PROJECT_INDUSTRY'       => 'PROJECT.UF_CRM_'.$typeId.'_INDUSTRY',
                'PROJECT_ED'             => 'PROJECT.UF_CRM_'.$typeId.'_ED',
            ])
            ->setFilter([
                '=USER_ID' => $this->userId,
                '!=DELETION_MARK' => 'Y',
                '<=STAFFING_DATE_FROM' => new Date(),
                '>=STAFFING_DATE_TO'   => new Date(),
            ])
            ->setOrder(['PROJECT_ID' => 'ASC', 'STAFFING_DATE_FROM' => 'ASC'])
            ->fetchAll();

        $result = [
            'ITEMS' => [],
            'HAS_STAFFING_PERMS' => Container::getInstance()->getUserPermissions()->hasPdStaffingPermissions()
        ];

        foreach ($data as $key => $item)
        {
            $item['NUMBER'] = (int)$key + 1;
            $item['PROJECT_LINK'] = Container::getInstance()->getRouter()->getItemDetailUrl(
                Dynamic::getInstance()->getEntityTypeId(), $item['PROJECT_ID']
            );

            $item['PROJECT_INDUSTRY'] = !empty($item['PROJECT_INDUSTRY'])
                                        ? IblockElement::getElementById((int)$item['PROJECT_INDUSTRY'])['NAME']
                                        : '';

            $item['PROJECT_ED'] = !empty($item['PROJECT_ED'])
                ? "<a href='".User::getUserProfileLink((int)$item['PROJECT_ED'])."'>".User::getUserNameById((int)$item['PROJECT_ED'])."</a>"
                : '';

            if (($item['STAFFING_DATE_FROM'] instanceof Date) && ($item['STAFFING_DATE_TO'] instanceof Date))
            {
                $workDaysCount = (int)$item['STAFFING_DATE_TO']->getDiff($item['STAFFING_DATE_FROM'])->days + 1;
                $item['WEEKS_ON_PROJECT'] = round($workDaysCount / 7);
            }

            $result['ITEMS'][] = $item;
        }

        return $result;
    }

    /**
     * @return bool
     * @throws \Exception
     */
    function checkRequirements(): bool
    {
        if (!($this->userId > 0))
        {
            throw new Exception(Loc::getMessage($this->moduleId."_COMPONENT_ERROR_USER_ID"));
        }
        return true;
    }
}