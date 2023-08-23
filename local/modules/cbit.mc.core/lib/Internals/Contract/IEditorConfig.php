<?php
/**
 * ==================================================
 * Developer: Alexey Nazarov
 * E-mail: alsnazarov@1cbit.ru
 * Copyright (c) 2019 - 2023
 * ==================================================
 * mc-portal - IEditorConfig.php
 * 19.02.2023 16:03
 * ==================================================
 */
namespace Cbit\Mc\Core\Internals\Contract;

/**
 * Interface IEditorConfig
 * @package Cbit\Mc\Core\Internals\Contract
 */
interface IEditorConfig
{
    /**
     * @return array
     */
    public function getEditorConfigTemplate(): array;

    /**
     * @return array
     */
    public function getHiddenFields(): array;

    /**
     * @return array
     */
    public function getReadonlyFields(): array;

    /**
     * @return array
     */
    public function getRequiredFields(): array;
}
