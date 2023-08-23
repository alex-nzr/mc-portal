<?php
/**
 * ==================================================
 * Developer: Alexey Nazarov
 * E-mail: alsnazarov@1cbit.ru
 * Copyright (c) 2019 - 2022
 * ==================================================
 * mc-portal - RIUserProvider.php
 * 21.12.2022 11:09
 * ==================================================
 */
namespace Cbit\Mc\Core\Service\Integration\UI\EntitySelector;

use Bitrix\Main\Entity\Query;
use Bitrix\Main\EO_User;
use Bitrix\Main\Type\Date;
use Bitrix\Main\UserGroupTable;
use Bitrix\Socialnetwork\Integration\UI\EntitySelector\UserProvider;
use Bitrix\UI\EntitySelector\Item;
use Cbit\Mc\Core\Config\Configuration;
use Cbit\Mc\Core\Helper\Iblock\IblockElement;
use Cbit\Mc\Core\Helper\Main\User;
use CUser;

/**
 * Class RIUserProvider
 * @package Cbit\Mc\Core\Service\Integration\UI\EntitySelector
 */
class RIUserProvider extends UserProvider
{
    const ENTITY_ID = 'ri-user';
    const OFFICE_TAB_ID = 'Office';
    const OUTSOURCE_TAB_ID = 'Outsource';

    /**
     * @return bool
     */
    public function isAvailable(): bool
    {
        return true;
    }

    /**
     * @param array $options
     * @return \Bitrix\Main\Entity\Query
     * @throws \Exception
     */
    protected static function getQuery(array $options = []): Query
    {
        $query = parent::getQuery($options);
        $query->addSelect('LAST_ACTIVITY_DATE');
        $query->addSelect('UF_COVERED_INDUSTRIES');

        $riUsers = UserGroupTable::query()
            ->setSelect(['USER_ID'])
            ->setFilter([
                'GROUP_ID' => array_merge(
                    Configuration::getInstance()->getRiManagersGroupIds(),
                    Configuration::getInstance()->getRiAnalystsGroupIds(),
                )
            ])
            ->fetchAll();

        $riUsersIds = [];
        foreach ($riUsers as $riUser) {
            $riUsersIds[] = $riUser['USER_ID'];
        }

        $query->addFilter('ID', $riUsersIds);

        return $query;
    }

    /**
     * @param \Bitrix\Main\EO_User $user
     * @param array $options
     * @return \Bitrix\UI\EntitySelector\Item
     * @throws \Exception
     */
    public static function makeItem(EO_User $user, array $options = []): Item
    {
        $item = parent::makeItem($user, $options);
        $item->setLink(User::getUserProfileLink($item->getId()));
        $item->setLinkTitle('Open');
        //$item->addTab(); self::OFFICE_TAB_ID/self::OUTSOURCE_TAB_ID
        //$item->getCustomData()->set('isOnVacation', true);

        $badges = [];

        $lastActivityDate = $user->get('LAST_ACTIVITY_DATE');
        $lastActivityDateFormatted = $lastActivityDate instanceof Date ? $lastActivityDate->format('Y-m-d H:i:s') : '';
        $status = CUser::GetOnlineStatus($user->getId(), MakeTimeStamp($lastActivityDateFormatted, "YYYY-MM-DD HH-MI-SS"));
        if (!empty($status))
        {
            $statusName = $status['STATUS_TEXT'];
            $statusColor = $status['IS_ONLINE'] ? 'lightgreen' : 'lightgrey';
            $badges[] = [
                'title' => [
                    'text' => '
                        <span class="ui-selector-item-status-wrapper">
                            <i class="ui-selector-item-status-icon" style="background-color:'.$statusColor.'"></i>
                            <span> '.$statusName.'</span>
                        </span>',
                    'type' => 'html'
                ],
                'textColor' => '#000',
                'bgColor' => 'transparent',
            ];
        }


        $industryIds = $user->get('UF_COVERED_INDUSTRIES');
        if (is_array($industryIds))
        {
            if (count($industryIds) > 3)
            {
                $industryIds = array_slice($industryIds, 0, 3);
            }
            foreach ($industryIds as $industryId)
            {
                $data = IblockElement::getElementById($industryId);
                if (!empty($data))
                {
                    $badges[] = [
                        'title' => $data['NAME'],
                        'textColor' => '#000',
                        'bgColor' => $data['INDUSTRY_COLOR'],
                    ];
                }
            }
        }

        if (!empty($badges))
        {
            $item->setBadges($badges);
        }

        return $item;
    }
}