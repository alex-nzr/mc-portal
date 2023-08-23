<?php

use Cbit\Mc\Staffing\Internals\Control\ServiceManager;
$moduleId = ServiceManager::getModuleId();

$MESS[$moduleId.'_RELEVANT_ROLE_NOT_FOUND_IN_PROJECT'] = 'На данном проекте нет потребности в сотрудниках с ролью #ROLE#';
$MESS[$moduleId.'_PERCENT_IS_MORE_THAN_NEED']          = 'На данном проекте для сотрудника с ролью #ROLE# доступны проценты участия: #PERCENTS#';
$MESS[$moduleId.'_START_DATE_IS_INCORRECT']            = 'Не найдено need c выбранной датой старта. Измените дату старта или предварительно внесите изменения в needs';
$MESS[$moduleId.'_END_DATE_IS_CHANGED']                = 'Сотрудник добавлен. Обратите внимание: дата окончания в need была автоматически изменена c #OLD_END_DATE# на #NEW_END_DATE#.';