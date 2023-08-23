<?php
/**
 * ==================================================
 * Developer: Alexey Nazarov
 * E-mail: jc1988x@gmail.com
 * Copyright (c) 2019 - 2023
 * ==================================================
 * mc-portal - IEventManager.php
 * 24.02.2023 20:29
 * ==================================================
 */

namespace Cbit\Mc\Core\Internals\Contract;

/**
 * @interface IEventManager
 * @package Cbit\Mc\Core\Internals\Contract
 */
interface IEventManager
{
    /**
     * @return void
     */
    public static function addBasicEventHandlers(): void;

    /**
     * @return void
     */
    public static function addRuntimeEventHandlers(): void;

    /**
     * @param array $events
     * @param bool $register
     * @return void
     */
    public static function addEventHandlersFromArray(array $events, bool $register = false): void;

    /**
     * @return void
     */
    public static function removeBasicEventHandlers(): void;

    /**
     * @return array
     */
    public static function getBasicEvents(): array;

    /**
     * @return array
     */
    public static function getRunTimeEvents(): array;
}