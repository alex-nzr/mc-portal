<?php
/**
 * ==================================================
 * Developer: Alexey Nazarov
 * E-mail: alsnazarov@1cbit.ru
 * Copyright (c) 2019 - 2023
 * ==================================================
 * mc-portal - Converter.php
 * 30.01.2023 11:52
 * ==================================================
 */

namespace Cbit\Mc\Expense\Helper\Currency;

use Bitrix\Currency\CurrencyManager;
use Bitrix\Main\Text\Encoding;
use Bitrix\Main\Type\Date;
use Bitrix\Main\Web\HttpClient;
use CDataXML;
use Exception;
use Throwable;

/**
 * @class Converter
 * @package Cbit\Mc\Expense\Helper\Currency
 */
class Converter
{
    /**
     * @param string $currencyCode
     * @param float $value
     * @param \Bitrix\Main\Type\Date|null $date
     * @return float
     * @throws \Exception
     */
    public static function convertToRUB(string $currencyCode, float $value, Date $date = null): float
    {
        $currencyList = CurrencyManager::getCurrencyList();

        if (!array_key_exists('RUB', $currencyList))
        {
            throw new Exception("Currency 'RUB' not found. Conversion failed");
        }

        if (array_key_exists($currencyCode, $currencyList))
        {
            return round($value * static::getCurrencyRateByDate($currencyCode, $date), 2);
        }
        else
        {
            throw new Exception("Currency with code '$currencyCode' not found");
        }
    }

    /**
     * @param string $currencyCode
     * @param \Bitrix\Main\Type\Date|null $date
     * @return float
     * @throws \Exception
     */
    public static function getCurrencyRateByDate(string $currencyCode, ?Date $date): float
    {
        try
        {
            $date = $date instanceof Date ? $date->format("d.m.Y") : date("d.m.Y");
            $url = 'https://www.cbr.ru/scripts/XML_daily.asp?date_req='.$date;
            $http = new HttpClient();
            $http->setRedirect(true);
            $data = $http->get($url);

            if ($data === false)
            {
                throw new Exception("Request to CBR failed");
            }

            $charset = 'windows-1251';
            $matches = [];
            if (preg_match("/<"."\?XML[^>]+encoding=[\"']([^>\"']+)[\"'][^>]*\?".">/i", $data, $matches))
            {
                $charset = trim($matches[1]);
            }
            $data = preg_replace("#<!DOCTYPE[^>]+?>#i", '', $data);
            $data = preg_replace("#<"."\\?XML[^>]+?\\?".">#i", '', $data);
            $data = Encoding::convertEncoding($data, $charset, SITE_CHARSET);

            $objXML = new CDataXML();
            $loaded = $objXML->LoadString($data);
            $data   = ($loaded !== false) ? $objXML->GetArray() : false;

            $result = 0;
            if (is_array($data) && !empty($data))
            {
                if (is_array($data["ValCurs"])
                    && is_array($data["ValCurs"]["#"])
                    && is_array($data["ValCurs"]["#"]["Valute"])
                ){
                    $currencyList = $data["ValCurs"]["#"]["Valute"];
                    foreach ($currencyList as $currencyRate)
                    {
                        if ($currencyRate["#"]["CharCode"][0]["#"] === $currencyCode)
                        {
                            $result = (float)str_replace(",", ".", $currencyRate["#"]["Value"][0]["#"]);
                            break;
                        }
                    }
                    unset($currencyRate, $currencyList);
                }
            }
            else
            {
                throw new Exception("Error on reading data from CBR");
            }

            if ($result === 0)
            {
                throw new Exception("Rate for $currencyCode not found");
            }

            return $result;
        }
        catch (Throwable $e)
        {
            throw new Exception($e->getMessage());
        }
    }
}