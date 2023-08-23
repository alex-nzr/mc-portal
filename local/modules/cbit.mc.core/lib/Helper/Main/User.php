<?php
/**
 * ==================================================
 * Developer: Alexey Nazarov
 * E-mail: alsnazarov@1cbit.ru
 * Copyright (c) 2019 - 2022
 * ==================================================
 * mc-portal - User.php
 * 28.11.2022 15:14
 * ==================================================
 */


namespace Cbit\Mc\Core\Helper\Main;

use Bitrix\Main\UserTable;
use Cbit\Mc\Core\Service\Integration\Zup\Fields;
use CFile;
use Exception;

/**
 * Class User
 * @package Cbit\Mc\Core\Helper\Main
 */
class User
{
    /**
     * @param int $id
     * @return string
     */
    public static function getUserNameById(int $id): string
    {
        try
        {
            $user = UserTable::query()
                ->setSelect([
                    Fields::getFioEnUfCode(),
                    'NAME', 'LAST_NAME'
                ])
                ->where('ID', '=', $id)
                ->fetch();

            if ($user)
            {
                return $user[Fields::getFioEnUfCode()] ?? $user['NAME'] . " " . $user['LAST_NAME'];
            }
            else
            {
                return 'Not found';
            }
        }
        catch(Exception $e)
        {
            return 'Error ' . $e->getMessage();
        }
    }

    /**
     * @param array $userData
     * @return string
     */
    public static function getUserNameByFields(array $userData): string
    {
        return $userData[Fields::getFioEnUfCode()] ?? $userData['NAME'] . " " . $userData['LAST_NAME'];
    }

    /**
     * @param int $id
     * @param array $data
     * @return string
     */
    public static function getProfileViewLink(int $id, array $data = []): string
    {
        $userLink = static::getUserProfileLink($id);

        if (!empty($data))
        {
            $userName = static::getUserNameByFields($data);
        }
        else
        {
            $userName = static::getUserNameById($id);
        }

        return "<a href='".$userLink."'>".$userName."</a>";
    }

    /**
     * @param int $id
     * @return string
     */
    public static function getUserProfileLink(int $id): string
    {
        return "/company/personal/user/".$id."/";
    }

    /**
     * @param int $fileId
     * @return string|null
     */
    public static function getResizedAvatarByFileId(int $fileId): ?string
    {
        $file = CFile::GetFileArray($fileId);
        if (!empty($file))
        {
            $fileTmp = CFile::ResizeImageGet(
                $file,
                ["width" => 100, "height" => 100],
                BX_RESIZE_IMAGE_PROPORTIONAL,
                false,
                false,
                true
            );

            return $fileTmp["src"];
        }
        return null;
    }

    /**
     * @param string $fmno
     * @return int|null
     * @throws \Exception
     */
    public static function getUserIdByFMNO(string $fmno): ?int
    {
        $user = UserTable::query()
            ->setSelect(['ID'])
            ->where(Fields::getFmnoUfCode(), '=', $fmno)
            ->fetch();

        if ($user)
        {
            return (int)$user['ID'];
        }
        return null;
    }

    /**
     * @param int $userId
     * @return string
     * @throws \Exception
     */
    public static function getUserPerDiem(int $userId): string
    {
        $user = UserTable::query()
            ->setSelect([Fields::getBasePerDiemUfCode()])
            ->where('ID', '=', $userId)
            ->fetch();

        if ($user)
        {
            return (string)$user[Fields::getBasePerDiemUfCode()];
        }
        return '';
    }

    /**
     * @param int $userId
     * @return string
     * @throws \Exception
     */
    public static function getUserEmailById(int $userId): string
    {
        $user = UserTable::query()
            ->setSelect(['EMAIL', 'UF_EMAIL'])
            ->where('ID', '=', $userId)
            ->fetch();

        if ($user)
        {
            return (string)(!empty($user['EMAIL']) ? $user['EMAIL'] : $user['UF_EMAIL']);
        }
        return '';
    }

    /**
     * @param int $userId
     * @return int
     * @throws \Exception
     */
    public static function getUserRatingById(int $userId): int
    {
        $user = UserTable::query()
            ->setSelect(['UF_TYB_RATING'])
            ->where('ID', '=', $userId)
            ->fetch();

        if (is_array($user) && !empty($user))
        {
            return (int)$user['UF_TYB_RATING'];
        }

        return 0;
    }

    /**
     * @param int $userId
     * @return string
     * @throws \Exception
     */
    public static function getUserPositionEnById(int $userId): string
    {
        $fieldCode = Fields::getPositionEnUfCode();
        if (!empty($fieldCode))
        {
            $user = UserTable::query()
            ->setSelect([$fieldCode])
            ->where('ID', '=', $userId)
            ->fetch();

            if ($user)
            {
                return (string)$user[$fieldCode];
            }
        }
        return '';
    }

    /**
     * @param int $userId
     * @return string
     * @throws \Exception
     */
    public static function getUserTenureCompanyById(int $userId): string
    {
        $fieldCode = Fields::getTenureCompanyUfCode();
        if (!empty($fieldCode))
        {
            $user = UserTable::query()
                ->setSelect([$fieldCode])
                ->where('ID', '=', $userId)
                ->fetch();

            if ($user)
            {
                return (string)$user[$fieldCode];
            }
        }
        return '';
    }

    /**
     * @param int $userId
     * @return array
     * @throws \Exception
     */
    public static function getUserTenureCompanyArrayById(int $userId): array
    {
        $tenureArray  = [];
        $tenureString = static::getUserTenureCompanyById($userId);
        if (!empty($tenureString) && str_contains($tenureString, '+'))
        {
            list($years, $months) = explode('+', $tenureString);
            $tenureArray['Y'] = (int)$years;
            $tenureArray['M'] = (int)$months;
        }
        return $tenureArray;
    }

    /**
     * @param int $userId
     * @return string
     * @throws \Exception
     */
    public static function getUserFMNOById(int $userId): string
    {
        $fieldCode = Fields::getFmnoUfCode();
        if (!empty($fieldCode))
        {
            $user = UserTable::query()
                ->setSelect([$fieldCode])
                ->where('ID', '=', $userId)
                ->fetch();

            if ($user)
            {
                return (string)$user[$fieldCode];
            }
        }
        return '';
    }

    /**
     * @param int $userId
     * @return string
     * @throws \Exception
     */
    public static function getUserCspOspById(int $userId): string
    {
        $fieldCode = Fields::getCspOspUfCode();
        if (!empty($fieldCode))
        {
            $user = UserTable::query()
                ->setSelect([$fieldCode])
                ->where('ID', '=', $userId)
                ->fetch();

            if ($user)
            {
                return (string)$user[$fieldCode];
            }
        }
        return '';
    }
}