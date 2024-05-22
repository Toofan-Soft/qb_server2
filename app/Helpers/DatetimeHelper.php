<?php

namespace App\Helpers;


class DatetimeHelper
{


    /**
     * $date1 and date2 are objects of datetime
     * return long
     * it using in lecturer online exam -> getAlgorithmData
     */
    public static function getDifferenceInSeconds($date1, $date2) // check 
    {
        return ($date1->getTimestamp() - $date2->getTimestamp());
    }

     /**
     * $date1 and date2 are objects of datetime
     * return long
     * it using in lecturer online exam -> getAlgorithmData
     */
    public static function convertSecondsToDays($seconds) // check 
    {
        return ($seconds / (24 * 3600));
    }

}
