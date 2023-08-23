<?php
/**
 * ==================================================
 * Developer: Alexey Nazarov
 * E-mail: jc1988x@gmail.com
 * Copyright (c) 2019 - 2023
 * ==================================================
 * mc-portal - RatingParser.php
 * 27.04.2023 18:42
 * ==================================================
 */

namespace Cbit\Mc\Expense\Service\Csv;

require_once($_SERVER['DOCUMENT_ROOT'].BX_ROOT.'/modules/main/classes/general/csv_data.php');

use Bitrix\Main\Application;
use CCSVData;
use CFile;
use Throwable;

/**
 * @class RatingParser
 * @package Cbit\Mc\Expense\Service\Csv
 */
class RatingParser
{
    /**
     * @param int $fileId
     * @return array|null
     */
    public static function getArrayFromCsvFile(int $fileId): ?array
    {
        try
        {
            $csvReader     = new CCSVData("R", false);
            $filePath      = Application::getDocumentRoot() . CFile::GetPath($fileId);
            if (!is_file($filePath))
            {
                return null;
            }

            $convertedData = iconv("WINDOWS-1251", "UTF-8", file_get_contents($filePath));
            file_put_contents($filePath, $convertedData);

            $csvReader->LoadFile($filePath);
            $csvReader->SetDelimiter();

            $result = [];
            while ($arRes = $csvReader->Fetch())
            {
                if (str_contains($arRes[0], 'FMNO') || str_contains($arRes[1], 'RATE'))
                {
                    continue;
                }
                $result[$arRes[0]] = $arRes[1];
            }

            return $result;
        }
        catch (Throwable)
        {
            return null;
        }
    }
}