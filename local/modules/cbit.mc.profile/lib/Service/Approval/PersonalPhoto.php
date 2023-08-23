<?php
/**
 * ==================================================
 * Developer: Alexey Nazarov
 * E-mail: alsnazarov@1cbit.ru
 * Copyright (c) 2019 - 2022
 * ==================================================
 * mc-portal - PersonalPhoto.php
 * 07.11.2022 18:05
 * ==================================================
 */


namespace Cbit\Mc\Profile\Service\Approval;

use Bitrix\Main\Error;
use Bitrix\Main\Localization\Loc;
use Bitrix\Main\Result;
use Bitrix\Main\UserTable;
use Cbit\Mc\Core\Helper\Im\Notify;
use Cbit\Mc\Profile\Internals\Model\Approval\PhotoQueueTable;
use Cbit\Mc\Profile\Internals\Control\ServiceManager;
use Cbit\Mc\Profile\Internals\Model\User\UserPhotoTable;
use Cbit\Mc\Profile\Service\Access\Permission;
use CFile;
use CUser;
use Exception;

/**
 * Class PersonalPhoto
 * @package Cbit\Mc\Profile\Service\Approval
 */
class PersonalPhoto
{
    private static ?PersonalPhoto $instance = null;
    private string $moduleId;

    private function __construct(){
        $this->moduleId = ServiceManager::getModuleId();
    }

    public static function getInstance(): ?PersonalPhoto
    {
        if (static::$instance === null)
        {
            static::$instance = new static();
        }
        return static::$instance;
    }

    /**
     * @param int $userId
     * @param array $photoFileArray
     * @throws \Exception
     */
    public function startApprovingProcess(int $userId, array $photoFileArray): void
    {
        $this->deleteUserRequestIfAlreadyExists($userId);

        if (empty($photoFileArray["MODULE_ID"]))
        {
            $photoFileArray["MODULE_ID"] = $this->moduleId;
        }

        $oldFileId = $photoFileArray['old_file'];
        if (!empty($oldFileId))
        {
            unset($photoFileArray['old_file'], $photoFileArray['del']);
        }

        $newFileId = (int)CFile::SaveFile($photoFileArray, $this->moduleId, false, false, '', true);
        if ($newFileId > 0)
        {
            $isModerator = $this->canUserApprovePhoto($userId);

            if ($isModerator)
            {
                $result = $this->approveNewProfilePhoto($userId, $newFileId, true);
                if (!$result->isSuccess())
                {
                    throw new Exception($result->getErrorMessages()[0]);
                }
            }
            else
            {
                Notify::createTextNotify(
                    $userId,
                    Loc::getMessage($this->moduleId.'_PHOTO_SENT_TO_APPROVAL_TITLE'),
                    Loc::getMessage($this->moduleId.'_PHOTO_SENT_TO_APPROVAL_DESC')
                );

                $link = $this->getApprovePageLink($userId, $newFileId, (int)$oldFileId);

                $approverIds = $this->getApproverIds();
                foreach ($approverIds as $approverId)
                {
                    Notify::createLinkNotify(
                        $approverId,
                        Loc::getMessage($this->moduleId.'_NEW_PHOTO_FOR_APPROVING_TITLE'),
                        Loc::getMessage($this->moduleId.'_NEW_PHOTO_FOR_APPROVING_LINK_TEXT'),
                        $link
                    );
                }

                PhotoQueueTable::add([
                    "OLD_FILE_ID"   => (int)$oldFileId,
                    "NEW_FILE_ID"   => $newFileId,
                    "USER_ID"       => $userId,
                ]);
            }
        }
        else
        {
            throw new Exception('Can not save new profile photo file.');
        }
    }

    /**
     * @param int $userId
     * @param int $newFileId
     * @param bool $ignoreQueue
     * @return \Bitrix\Main\Result
     */
    public function approveNewProfilePhoto(int $userId, int $newFileId, bool $ignoreQueue = false): Result
    {
        $result = new Result();
        $user   = new CUser;
        try
        {
            if (!$ignoreQueue && !$this->isPhotoInQueue($userId, $newFileId)){
                throw new Exception(Loc::getMessage($this->moduleId.'_PHOTO_APPROVING_NO_FILE_IN_QUEUE'));
            }

            $newFilePath = CFile::GetPath($newFileId);
            $newFileData = !empty($newFilePath) ? CFile::MakeFileArray($newFilePath) : [];
            if (empty($newFileData))
            {
                throw new Exception(Loc::getMessage($this->moduleId.'_PHOTO_APPROVING_NO_FILE_ERROR'));
            }

            $updated = $user->Update($userId, [
                'PERSONAL_PHOTO' => $newFileData,
                'IS_APPROVED'    => "Y"
            ]);

            if (!$updated)
            {
                throw new Exception($user->LAST_ERROR);
            }
            else
            {
                Notify::createTextNotify(
                    $userId,
                    Loc::getMessage($this->moduleId.'_PHOTO_APPROVED_SUCCESS_TITLE'),
                    Loc::getMessage($this->moduleId.'_PHOTO_APPROVED_SUCCESS_DESC')
                );

                $this->deletePhotoFromQueue($userId, $newFileId);
                CFile::Delete($newFileId);

                $userData = UserTable::query()
                    ->setSelect(['PERSONAL_PHOTO'])
                    ->setFilter(['ID' => $userId])
                    ->fetch();
                if (is_array($userData) && (int)$userData['PERSONAL_PHOTO'] > 0)
                {
                    $this->deletePhotoFromCollection($userId, $newFileId);
                    $this->savePhotoToCollection($userId, (int)$userData['PERSONAL_PHOTO']);
                }
            }
        }
        catch (Exception $e)
        {
            $result->addError(new Error(
                Loc::getMessage($this->moduleId.'_PHOTO_APPROVING_ERROR') ." - " . $e->getMessage()
            ));
        }

        return $result;
    }

    /**
     * @param int $userId
     * @param int $newFileId
     * @param string $reason
     * @return \Bitrix\Main\Result
     */
    public function declineNewProfilePhoto(int $userId, int $newFileId, string $reason): Result
    {
        $result = new Result();

        try
        {
            if (!$this->isPhotoInQueue($userId, $newFileId)){
                throw new Exception(Loc::getMessage($this->moduleId.'_PHOTO_APPROVING_NO_FILE_IN_QUEUE'));
            }

            CFile::Delete($newFileId);

            Notify::createTextNotify(
                $userId,
                Loc::getMessage($this->moduleId.'_PHOTO_APPROVED_DECLINE_TITLE'),
                $reason
            );

            $this->deletePhotoFromQueue($userId, $newFileId);
        }
        catch (Exception $e)
        {
            $result->addError(new Error(
                Loc::getMessage($this->moduleId.'_PHOTO_APPROVING_ERROR') ." - " . $e->getMessage()
            ));
        }

        return $result;
    }

    private function __clone(){}
    public function __wakeup(){}

    /**
     * @return array
     */
    private function getApproverIds(): array
    {
        return Permission::getVgLeadersIds();
    }

    /**
     * @param int|null $userId
     * @return bool
     */
    public function canUserApprovePhoto(?int $userId = null): bool
    {
        return Permission::isUserInVgLeadersGroup($userId);
    }

    /**
     * @return bool
     */
    public function canCurrentUserApprovePhoto(): bool
    {
        return $this->canUserApprovePhoto();
    }

    /**
     * @param int $userId
     * @param int $newFileId
     * @throws \Exception
     */
    private function deletePhotoFromQueue(int $userId, int $newFileId)
    {
        $element = PhotoQueueTable::query()
            ->setSelect(['ID'])
            ->setFilter(['USER_ID' => $userId, 'NEW_FILE_ID' => $newFileId])
            ->fetch();

        if (!empty($element))
        {
            PhotoQueueTable::delete($element['ID']);
        }
    }

    /**
     * @param int $userId
     * @param int $newFileId
     * @return bool
     * @throws \Exception
     */
    public function isPhotoInQueue(int $userId, int $newFileId): bool
    {
        $element = PhotoQueueTable::query()
            ->setSelect(['ID'])
            ->setFilter(['USER_ID' => $userId, 'NEW_FILE_ID' => $newFileId])
            ->fetch();

        return !empty($element);
    }

    /**
     * @param int $userId
     * @param int $newFileId
     * @param int $oldFileId
     * @return string
     */
    public function getApprovePageLink(int $userId, int $newFileId, int $oldFileId): string
    {
        return "/profile/approve/photo/detail.php?" . http_build_query([
                "NEW_ID"   => $newFileId,
                "OLD_ID"   => $oldFileId,
                "USER_ID"  => $userId,
                "IFRAME"   => 'Y',
                "BACK_URL" => "/profile/approve/photo/list.php"
            ]);
    }

    /**
     * @param int $userId
     * @throws \Exception
     */
    private function deleteUserRequestIfAlreadyExists(int $userId): void
    {
        $elements = PhotoQueueTable::query()
            ->setSelect(['ID', 'NEW_FILE_ID'])
            ->setFilter(['USER_ID' => $userId])
            ->fetchCollection();
        foreach ($elements as $element) {
            CFile::Delete($element->get('NEW_FILE_ID'));
            $element->delete();
        }
    }

    /**
     * @param int $userId
     * @param int $newFileId
     * @throws \Exception
     */
    private function savePhotoToCollection(int $userId, int $newFileId): void
    {
        UserPhotoTable::add([
            'USER_ID' => $userId,
            'FILE_ID' => $newFileId,
            'FILE_LINK' => CFile::GetPath($newFileId),
        ]);
    }

    /**
     * @param int $userId
     * @param int $fileId
     * @throws \Exception
     */
    public function deletePhotoFromCollection(int $userId, int $fileId)
    {
        $elements = UserPhotoTable::query()
            ->setFilter(['USER_ID' => $userId, 'FILE_ID' => $fileId])
            ->setSelect(['ID'])
            ->fetchAll();
        foreach ($elements as $element)
        {
            UserPhotoTable::delete($element['ID']);
        }
    }
}