<?php

namespace App\Helpers;

use DateTime;
use DateTimeZone;
use Illuminate\Support\Carbon;


class DatetimeHelper
{


    /**
     * $date1 and date2 are objects of datetime
     * return long
     * it using in lecturer online exam -> getAlgorithmData
     */
    public static function getDifferenceInSeconds($date1, $date2) // يتم حذفها  
    {
        // $date2 = self::convertMillisecondsToTimestamp($date2);
        return ($date1->getTimestamp() - $date2->getTimestamp());
    }
    /**
     * $date1 and date2 are objects of datetime
     * return long
     * it using in lecturer online exam -> getAlgorithmData
     */
    public static function getDifferenceInDays($date1, $timestamp)
    {
        // Create a Carbon instance from the timestamp
        $date2 = Carbon::createFromTimestamp($timestamp);

        // Calculate the difference in days
        return $date1->diffInDays($date2);
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


    // public static function convertMillisecondsToTimestamp($value)
    // {
    //     if ($value == null) {
    //         return null;
    //     }

    //     $valueInSeconds = $value / 1000;
    //     // return date('Y-m-d H:i:s', $valueInSeconds);


    //     // Create a DateTime object from the seconds
    //     $date = new DateTime("@$valueInSeconds"); // The @ symbol specifies that $valueInSeconds is a Unix timestamp
    //     $date->setTimezone(new DateTimeZone('Etc/GMT-3')); // UTC+3 is represented as GMT-3 in the Etc format

    //     return $date->format('Y-m-d H:i:s');
    // }

    // public static function convertTimestampToMilliseconds($value)
    // {
    //     if ($value == null) {
    //         return null;
    //     }

    //     // Create a DateTime object from the timestamp string
    //     $date = DateTime::createFromFormat('Y-m-d H:i:s', $value, new DateTimeZone('Etc/GMT-3'));
    //     if (!$date) {
    //         throw new \InvalidArgumentException("Invalid datetime format: $value");
    //     }

    //     // Convert to Unix timestamp in seconds
    //     $timestampInSeconds = $date->getTimestamp();

    //     // Convert to milliseconds
    //     return $timestampInSeconds * 1000;
    // }
}
