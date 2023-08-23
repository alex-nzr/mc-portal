<?php
$menuSrc = __DIR__.DIRECTORY_SEPARATOR.'public'.DIRECTORY_SEPARATOR.'.top.menu_ext.php';

$search = DIRECTORY_SEPARATOR.
    'local'.DIRECTORY_SEPARATOR
    .'modules'.DIRECTORY_SEPARATOR
    .'cbit.mc.profile'.DIRECTORY_SEPARATOR.'install';

$menuDst = str_replace($search, '', __DIR__) . DIRECTORY_SEPARATOR .'.top.menu_ext.php';

if (!copy($menuSrc, $menuDst))
{
    echo "Can not replace menu file";
}
else
{
    echo 'Replaced successful';
}