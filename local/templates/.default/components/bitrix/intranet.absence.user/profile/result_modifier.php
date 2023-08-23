<?php
/**
 * @var array $arResult
 */
use Bitrix\Main\Type\Date;

try
{
    if(is_array($arResult['ENTRIES']))
    {
        foreach($arResult['ENTRIES'] as $key => $entry)
        {
            if(is_array($entry))
            {
                //из 1с иногда приходят непонятные отсутствия за 1899 год, поэтому проверка
                $dateFrom   = new Date($entry['DATE_FROM']);
                $activeFrom = new Date($entry['DATE_ACTIVE_FROM']);
                if ((int)$dateFrom->format('Y') === 1899 || (int)$activeFrom->format('Y') === 1899)
                {
                    unset($arResult['ENTRIES'][$key]);
                }
            }
        }
    }
}
catch(Exception){}