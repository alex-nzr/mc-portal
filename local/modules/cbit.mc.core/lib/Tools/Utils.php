<?php
/**
 * ==================================================
 * Developer: Alexey Nazarov
 * E-mail: alsnazarov@1cbit.ru
 * Copyright (c) 2019 - 2022
 * ==================================================
 * mc-portal - Utils.php
 * 19.12.2022 16:25
 * ==================================================
 */


namespace Cbit\Mc\Core\Tools;

/**
 * Class Utils
 * @package Cbit\Mc\Core\Tools
 */
class Utils
{
    /**
     * @throws \Exception
     */
    public static function generateRandomColor(): string
    {
        $letters = ['0', '1', '2','3', '4', '5', '6', '7', '8', '9', 'A', 'B', 'C', 'D', 'E', 'F'];
        $color = '#';
        for ($i = 0; $i < 6; $i++) {
            $color .= $letters[random_int(0, 15)];
        }
        return $color;
    }
}