<?php

namespace PlantPath\Bundle\VDIFNBundle\Geo;

class DateUtils
{
    /**
     * Given a day, return the beginning of it.
     *
     * @param  DateTime $day
     *
     * @return DateTime
     */
    public function getBeginningOfDay(\DateTime $day)
    {
        $beginning = clone $day;
        $beginning->setTime(0, 0, 0);

        return $beginning;
    }

    /**
     * Given a day, return the end of it.
     *
     * @param  DateTime $day
     *
     * @return DateTime
     */
    public function getEndOfDay(\DateTime $day)
    {
        $end = clone $day;
        $end->setTime(0, 0, 0);
        $end->modify('+1 day');

        return $end;
    }

    /**
     * Makes a new DateTime object based upon a given day and hour.
     *
     * @param  DateTime $day
     * @param  int      $hour
     *
     * @return DateTime
     */
    public function getDailyHour(\DateTime $day, $hour)
    {
        $dt = clone $day;
        $dt->setTime($hour, 0, 0);

        return $dt;
    }
}
