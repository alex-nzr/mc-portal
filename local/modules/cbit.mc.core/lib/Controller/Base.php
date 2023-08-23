<?php
/**
 * ==================================================
 * Developer: Alexey Nazarov
 * E-mail: alsnazarov@1cbit.ru
 * Copyright (c) 2019 - 2022
 * ==================================================
 * mc-portal - Base.php
 * 27.02.2023 18:45
 * ==================================================
 */
namespace Cbit\Mc\Core\Controller;

use Bitrix\Crm\Service\Container;
use Bitrix\Main\Engine\Action;
use Bitrix\Main\Engine\ActionFilter\Authentication;
use Bitrix\Main\Engine\ActionFilter\Csrf;
use Bitrix\Main\Engine\ActionFilter\HttpMethod;
use Bitrix\Main\Engine\Controller;
use Bitrix\Main\Error;
use Cbit\Mc\Core\Internals\Control\ServiceManager;
use CFile;
use CUserTypeEntity;

/**
 * @class Base
 * @package Cbit\Mc\Core\Controller
 */
class Base extends Controller
{
    /**
     * @param \Bitrix\Main\Engine\Action $action
     * @return bool
     * @throws \Exception
     */
    protected function processBeforeAction(Action $action): bool
    {
        return parent::processBeforeAction($action);
    }

    /**
     * @return array
     * @throws \Exception
     */
    public function uploadFileAction(): array
    {
        $fieldId  = $this->request->get('USER_FIELD_ID');
        $fileData = $this->request->getFile('FILE');
        if (!empty($fieldId) && is_array($fileData))
        {
            if ($this->checkFileByFieldSettings((int)$fieldId, $fileData))
            {
                $fileData['MODULE_ID'] = ServiceManager::getModuleId();
                $fileId = (int)CFile::SaveFile($fileData, ServiceManager::getModuleId() . '/uf-file-input');
                if ($fileId > 0)
                {
                    return CFile::GetByID($fileId)->Fetch();
                }
                else
                {
                    $this->addError(new Error('Error on uploading file'));
                }
            }
        }
        return [];
    }

    /**
     * @param int $fieldId
     * @param array $fileData
     * @return bool
     */
    protected function checkFileByFieldSettings(int $fieldId, array $fileData): bool
    {
        $res = true;
        $userField = CUserTypeEntity::GetByID($fieldId);
        if (!is_array($userField))
        {
            $this->addError(new Error("UserField with id $fieldId not found"));
            $res = false;
        }
        else
        {
            if((int)$userField['SETTINGS']['MAX_ALLOWED_SIZE'] > 0
                &&
                (int)$fileData['size'] > (int)$userField['SETTINGS']['MAX_ALLOWED_SIZE']
            ){
                $this->addError(new Error("Max file size exceeded"));
                $res = false;
            }

            if(is_array($userField['SETTINGS']['EXTENSIONS']) && !empty($userField['SETTINGS']['EXTENSIONS']))
            {
                if (!empty($fileData["name"]) && str_contains($fileData["name"], '.'))
                {
                    $arr = explode('.', $fileData["name"]);
                    $ext = end($arr);
                    if (array_key_exists($ext, $userField['SETTINGS']['EXTENSIONS'])
                        && $userField['SETTINGS']['EXTENSIONS'][$ext]
                    ){
                        return true;
                    }
                    else
                    {
                        $this->addError(new Error("Invalid file extension"));
                        $res = false;
                    }
                }
                else
                {
                    $this->addError(new Error("Invalid file name"));
                    $res = false;
                }
            }
        }

        return $res;
    }

    /**
     * @return array
     */
    protected function getDefaultPreFilters(): array
    {
        return [
            new HttpMethod([HttpMethod::METHOD_POST]),
            new Csrf(),
            new Authentication()
        ];
    }

    /**
     * @return array
     */
    public function configureActions(): array
    {
        return [];
    }
}