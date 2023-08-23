<?php
/**
 * ==================================================
 * Developer: Alexey Nazarov
 * E-mail: alsnazarov@1cbit.ru
 * Copyright (c) 2019 - 2022
 * ==================================================
 * mc-portal - DataFiller.php
 * 13.11.2022 19:43
 * ==================================================
 */


namespace Cbit\Mc\Profile\Service\Field;

use Bitrix\Iblock\Model\Section;
use Bitrix\Main\UserTable;
use Cbit\Mc\Profile\Config\Constants;
use COption;
use CUser;

/**
 * Class DataFiller
 * @package Cbit\Mc\Profile\Service\Field
 */
class DataFiller
{
    private static ?DataFiller $instance = null;
    private array $assistantsBeforeUpdate;

    private function __construct(){}

    /**
     * @return \Cbit\Mc\Profile\Service\Field\DataFiller|null
     */
    public static function getInstance(): ?DataFiller
    {
        if (static::$instance === null)
        {
            static::$instance = new static();
        }
        return static::$instance;
    }

    /**
     * @param array $assistants
     */
    public function saveCurrentAssistants(array $assistants): void
    {
        $this->assistantsBeforeUpdate = $assistants;
    }

    /**
     * @param int $userId
     * @param string|null $email
     * @throws \Exception
     */
    public function setUploadedDocsLink(int $userId, string $email = null): void
    {
        if ($email === null)
        {
            $user = UserTable::query()
                ->setFilter(['ID' => $userId])
                ->setSelect(['EMAIL'])
                ->fetch();

            $email = $user ? (string)$user['EMAIL'] : null;
        }


        if($email)
        {
            (new CUser)->Update($userId, [
                'UF_UPLOADED_DOCS' => str_replace(
                    '#ENCODED_EMAIL#',
                    urlencode($email),
                    Constants::LINK_TO_UPLOADED_DOCS
                )
            ]);
        }
    }

    /**
     * @throws \Exception
     */
    public function setUploadedDocsLinkForAll(): void
    {
        $users = UserTable::query()
            ->setFilter(['ACTIVE' => 'Y'])
            ->setSelect(['ID', 'EMAIL'])
            ->fetchAll();

        foreach ($users as $user) {
            $this->setUploadedDocsLink((int)$user['ID'], (string)$user['EMAIL']);
        }
    }

    /**
     * @param int $userId
     * @return void
     * @throws \Exception
     */
    public function setDefaultDepartment(int $userId): void
    {
        $deptIblockId = COption::GetOptionInt('intranet', 'iblock_structure');

        if ($deptIblockId > 0)
        {
            $class = Section::compileEntityByIblock($deptIblockId);
            $res = $class::query()
                ->setSelect([ 'ID', 'NAME' ])
                ->setOrder(['ID' => 'ASC'])
                ->setLimit(1)
                ->fetch();

            if(!empty($res))
            {
                (new CUser)->Update($userId, [
                    'UF_DEPARTMENT' => [ $res['ID'] ]
                ]);
            }
        }
    }

    private function __clone(){}
    public function __wakeup(){}

    /**
     * @param int $userId
     * @param array $assistants
     * @throws \Exception
     */
    public function setExecutiveFieldByAssistant(int $userId, array $assistants): void
    {
        $deleteFromExecutiveOfUsers = array_diff($this->assistantsBeforeUpdate, $assistants);
        $addToExecutiveOfUsers      = array_diff($assistants, $this->assistantsBeforeUpdate);

        $this->updateExecutiveField($deleteFromExecutiveOfUsers, $userId, true);
        $this->updateExecutiveField($addToExecutiveOfUsers, $userId, false);
    }

    /**
     * @param array $targetUsersIds
     * @param int $userId
     * @param bool $deleteAction
     * @throws \Exception
     */
    private function updateExecutiveField(array $targetUsersIds, int $userId, bool $deleteAction): void
    {
        $targetUsersData = UserTable::query()
            ->setFilter(['ID' => $targetUsersIds])
            ->setSelect(['ID', 'UF_EXECUTIVE'])
            ->fetchAll();

        foreach ($targetUsersData as $targetUser)
        {
            $executiveIds = $targetUser['UF_EXECUTIVE'];

            if($deleteAction)
            {
                $index = array_search($userId, $executiveIds);
                if (($index !== false) && is_numeric($index))
                {
                    unset($executiveIds[$index]);
                }
            }
            else
            {
                $executiveIds[] = $userId;
            }

            (new CUser)->Update($targetUser['ID'], [
                'UF_EXECUTIVE' => $executiveIds
            ]);
        }
    }
}