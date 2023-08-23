<?php
/**
 * ==================================================
 * Developer: Alexey Nazarov
 * E-mail: alsnazarov@1cbit.ru
 * Copyright (c) 2019 - 2022
 * ==================================================
 * mc-portal - DateTimeCalculator.php
 * 01.12.2022 14:46
 * ==================================================
 */
namespace Cbit\Mc\Core\Helper\Main;

use Bitrix\Main\Loader;
use Bitrix\Main\Type\Date;
use Bitrix\Main\Type\DateTime;
use Bitrix\Tasks\Integration\Calendar;
use Cbit\Mc\Core\Internals\Debug\Logger;
use Exception;

/**
 * @class DateTimeCalculator
 * @package Cbit\Mc\Core\Helper\Main
 */
class DateTimeCalculator
{
    protected static ?DateTimeCalculator $instance = null;
    protected array $weekDaysMap = ['SU', 'MO', 'TU', 'WE', 'TH', 'FR', 'SA'];
    protected int $workDayStartHours;
    protected int $workDayEndHours;
    protected array $holidays = [];
    protected array $weekend = [];

    private function __construct()
    {
        $calendarSettings        = Calendar::getSettings();
        $settingsWorkTimeStart   = (int)$calendarSettings['work_time_start'];
        $settingsWorkTimeEnd     = (int)$calendarSettings['work_time_end'];
        $this->workDayStartHours = $settingsWorkTimeStart > 0 ? $settingsWorkTimeStart : 9;
        $this->workDayEndHours   = $settingsWorkTimeEnd > 0 ? $settingsWorkTimeEnd : 18;
        $strHolidays             = $calendarSettings['year_holidays'];
        $weekend                 = $calendarSettings['week_holidays'];

        if (is_array($weekend))
        {
            foreach($weekend as $day)
            {
                $index = array_search($day, $this->weekDaysMap);
                if ($index !== false)
                {
                    $this->weekend[] = (int)$index;
                }
            }
        }

        if(!empty($strHolidays) && is_string($strHolidays))
        {
            $holidays = explode(',', $strHolidays);
            if(is_array($holidays) && !empty($holidays))
            {
                foreach($holidays as $holiday)
                {
                    $holiday = trim($holiday);
                    list($day, $month) = explode('.', $holiday);
                    $day = intval($day);
                    $month = intval($month);

                    if($day > 0 && $month > 0)
                    {
                        $day = $day < 10 ? '0'.$day : $day;
                        $month = $month < 10 ? '0'.$month : $month;
                        $this->holidays[] = $day.'.'.$month;
                    }
                }
            }
        }
    }
    private function __clone(){}
    public function __wakeup(){}

    /**
     * @return \Cbit\Mc\Core\Helper\Main\DateTimeCalculator|null
     */
    public static function getInstance(): ?DateTimeCalculator
    {
        if (static::$instance === null)
        {
            static::$instance = new static;
        }
        return static::$instance;
    }

    /**
     * @param \Bitrix\Main\Type\Date $date
     * @param int $addSeconds
     * @return \Bitrix\Main\Type\Date
     */
    public function add(Date $date, int $addSeconds): Date
    {
        $resultDateTime = $this->isWorkDay($date)
            ? DateTime::createFromTimestamp($date->getTimestamp())
            : $this->findNextWorkDay($date);
        $hoursToAdd     = floor($addSeconds / 3600);
        $minutesToAdd   = round(($addSeconds % 3600) / 60);

        $hours = (int)$resultDateTime->format('H');
        if ($hours < $this->workDayStartHours)
        {
            $resultDateTime->setTime($this->workDayStartHours, 0);
        }
        elseif ($hours >= $this->workDayEndHours)
        {
            $resultDateTime = $this->findNextWorkDay($resultDateTime->add('1D'));
        }

        $minutes = (int)$resultDateTime->format('i');
        for ($i = 1; $i <= $hoursToAdd; $i++)
        {
            $resultDateTime->add('1 hour');
            if ((int)$resultDateTime->format('H') === $this->workDayEndHours)
            {
                if ($minutes > 0)
                {
                    $resultDateTime = $this->findNextWorkDay($resultDateTime->add('1D'));
                    $resultDateTime->setTime($this->workDayStartHours, $minutes);
                }
                else
                {
                    if ($i < $hoursToAdd)
                    {
                        $resultDateTime = $this->findNextWorkDay($resultDateTime->add('1D'));
                    }
                }
            }
        }

        $resultDateTime->add($minutesToAdd . ' minutes');
        $resultHours   = (int)$resultDateTime->format('H');
        $resultMinutes = (int)$resultDateTime->format('i');
        if (($minutes > 0 && $resultHours === $this->workDayEndHours) || ($resultHours > $this->workDayEndHours))
        {
            $resultDateTime = $this->findNextWorkDay($resultDateTime->add('1D'));
            $resultDateTime->add(($resultHours - $this->workDayEndHours) . ' hours ' . $resultMinutes . ' minutes');
        }

        return $resultDateTime;
    }

    /**
     * @param \Bitrix\Main\Type\Date $date
     * @param int $addSeconds
     * @return \Bitrix\Main\Type\Date
     */
    public function addBack(Date $date, int $addSeconds): Date
    {
        $resultDateTime = $this->isWorkDay($date)
            ? DateTime::createFromTimestamp($date->getTimestamp())
            : $this->findPrevWorkDay($date);
        $hoursToAdd     = floor($addSeconds / 3600);
        $minutesToAdd   = round(($addSeconds % 3600) / 60);

        $hours = (int)$resultDateTime->format('H');
        if ($hours < $this->workDayStartHours)
        {
            $resultDateTime->setTime($this->workDayStartHours, 0);
        }
        elseif ($hours >= $this->workDayEndHours)
        {
            $resultDateTime->setTime($this->workDayEndHours, 0);
        }

        $minutes = (int)$resultDateTime->format('i');
        for ($i = 1; $i <= $hoursToAdd; $i++)
        {
            $resultDateTime->add('-1 hour');
            if ((int)$resultDateTime->format('H') === $this->workDayStartHours)
            {
                $resultDateTime = $this->findPrevWorkDay($resultDateTime->add('-1D'));
                if ($minutes > 0)
                {
                    $resultDateTime->add(($minutes - 60) . ' minutes');
                }
            }
        }

        $resultDateTime->add('-'. $minutesToAdd . ' minutes');
        $resultHours   = (int)$resultDateTime->format('H');
        $resultMinutes = (int)$resultDateTime->format('i');
        if ($resultHours < $this->workDayStartHours)
        {
            $resultDateTime = $this->findPrevWorkDay($resultDateTime->add('-1D'));
            $resultDateTime->add(($resultMinutes - 60) . ' minutes');
        }

        return $resultDateTime;
    }

    /**
     * @param \Bitrix\Main\Type\DateTime $start
     * @param \Bitrix\Main\Type\DateTime $end
     * @param string|null $currentValue
     * @return string
     * @throws \Exception
     */
    public function formatTime(DateTime $start, DateTime $end, ?string $currentValue = null): string
    {
        if ($start->format('d.m.Y') === $end->format('d.m.Y'))
        {
            $workTime = $this->filterWorkTimeFromOneDay($start, $end);
        }
        else
        {
            $workDays = $this->filterWorkDays($start, $end);
            $workTime = $this->filterWorkTime($workDays);
        }

        $hours   = $workTime['H'];
        $minutes = $workTime['M'];

        if (!empty($currentValue))
        {
            list($curHours, $curMinutes) = explode(':', $currentValue);

            $curHours   = intval($curHours);
            $curMinutes = intval($curMinutes);
            $hours      = $hours + $curHours;
            $minutes    = $minutes + $curMinutes;
        }

        if ($minutes >= 60)
        {
            $hours   = $hours + floor($minutes / 60);
            $minutes = $minutes % 60;
        }

        $hours   = ($hours < 10) ? '0'.$hours : $hours;
        $minutes = ($minutes < 10) ? '0'.$minutes : $minutes;

        return $hours.':'.$minutes;
    }

    /**
     * @param \Bitrix\Main\Type\DateTime $start
     * @param \Bitrix\Main\Type\DateTime $end
     * @return array
     */
    private function filterWorkDays(DateTime $start, DateTime $end): array
    {
        $counter = clone $start;
        $counter->setTime(0,0);
        $res = [];

        while($counter <= $end)
        {
            if($this->isWorkDay($counter))
            {
                if ($counter < $start)
                {
                    $res[] = $start->toString();
                }
                elseif ($counter->format('d.m.Y') === $end->format('d.m.Y'))
                {
                    $res[] = $end->toString();
                }
                else
                {
                    $res[] = $counter->toString();
                }
            }
            $counter->add('1D');
        }

        return $res;
    }

    /**
     * @param array $workDays
     * @return array
     * @throws \Exception
     */
    private function filterWorkTime(array $workDays): array
    {
        $result = [
            'H' => 0,
            'M' => 0
        ];

        $sh      = $this->workDayStartHours;
        $eh      = $this->workDayEndHours;
        $fullDay = ($eh - $sh);

        foreach ($workDays as $key => $workDay)
        {
            $date = new DateTime($workDay);
            $hours   = (int)$date->format('H');
            $minutes = (int)$date->format('i');

            if ($key === (count($workDays) - 1))
            {
                //если работаем с днём, который завершает период
                if ($hours < $sh)
                {
                    continue;
                }
                elseif ($hours >= $eh)
                {
                    $result['H'] = $result['H'] + $fullDay;
                }
                else
                {
                    $h = $hours - $sh;
                    $result['H'] = $result['H'] + $h;
                }
            }
            else
            {
                //если работаем с любым днём кроме завершающего период
                if ($hours < $sh)
                {
                    $result['H'] = $result['H'] + $fullDay;
                }
                elseif ($hours >= $eh)
                {
                    continue;
                }
                else
                {
                    $h = ($minutes > 0) ? ($eh - $hours - 1) : ($eh - $hours);
                    $result['H'] = $result['H'] + $h;
                }
            }

            $result['M'] = $result['M'] + $minutes;
        }

        return $result;
    }

    /**
     * @param \Bitrix\Main\Type\DateTime $start
     * @param \Bitrix\Main\Type\DateTime $end
     * @return int[]
     */
    private function filterWorkTimeFromOneDay(DateTime $start, DateTime $end): array
    {
        $result = [
            'H' => 0,
            'M' => 0
        ];

        if($this->isWorkDay($start))
        {
            if ((int)$start->format('H') < $this->workDayStartHours)
            {
                $start->setTime($this->workDayStartHours, 0);
            }
            elseif ((int)$start->format('H') >= $this->workDayEndHours)
            {
                return $result;
            }

            if ((int)$end->format('H') >= $this->workDayEndHours)
            {
                $end->setTime($this->workDayEndHours, 0);
            }
            elseif ((int)$end->format('H') < $this->workDayStartHours)
            {
                return $result;
            }

            $startDate = \DateTime::createFromFormat('d.m.Y H:i:s', $start->format('d.m.Y H:i:s'));
            $endDate   = \DateTime::createFromFormat('d.m.Y H:i:s', $end->format('d.m.Y H:i:s'));

            $interval = $endDate->diff($startDate);
            $result['H'] = $interval->h;
            $result['M'] = $interval->i;
        }

        return $result;
    }

    /**
     * @param \Bitrix\Main\Type\Date $date
     * @return bool
     */
    public function isWorkDay(Date $date): bool
    {
        $weekDay = (int)$date->format('w');
        $dm      = $date->format('d.m');

        return (!in_array($weekDay, $this->weekend) && !in_array($dm, $this->holidays));
    }

    /**
     * @param \Bitrix\Main\Type\Date $date
     * @return \Bitrix\Main\Type\DateTime
     */
    public function findNextWorkDay(Date $date): DateTime
    {
        $newDate = DateTime::createFromTimestamp($date->getTimestamp());
        while(!$this->isWorkDay($newDate))
        {
            $newDate->add('1D');
        }
        $newDate->setTime($this->workDayStartHours, 0);
        return $newDate;
    }

    /**
     * @param \Bitrix\Main\Type\Date|null $date
     * @return bool
     */
    public function isTimeSelected(?Date $date): bool
    {
        if ($date instanceof Date)
        {
            return ($date->format('H:s:i') !== '00:00:00');
        }
        return false;
    }

    /**
     * @param \Bitrix\Main\Type\Date $date1_start
     * @param \Bitrix\Main\Type\Date $date1_end
     * @param \Bitrix\Main\Type\Date $date2_start
     * @param \Bitrix\Main\Type\Date $date2_end
     * @return \Bitrix\Main\Type\Date[]
     */
    public function getDateIntervalsIntersect(Date $date1_start, Date $date1_end, Date $date2_start, Date $date2_end): array
    {
        if(($date1_start < $date2_end) && ($date1_end > $date2_start))
        {
            return [
                'start' => ($date1_start > $date2_start) ? $date1_start : $date2_start,
                'end' => ($date1_end > $date2_end) ? $date2_end : $date1_end
            ];
        }
        else
        {
            return [];
        }
    }

    /**
     * @param \Bitrix\Main\Type\Date $date
     * @return \Bitrix\Main\Type\DateTime
     */
    public function findPrevWorkDay(Date $date): DateTime
    {
        $newDate = DateTime::createFromTimestamp($date->getTimestamp());
        while(!$this->isWorkDay($newDate))
        {
            $newDate->add('-1D');
        }
        $newDate->setTime($this->workDayEndHours, 0);
        return $newDate;
    }
}