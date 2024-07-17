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










    public static function now() {
        return (new DateTime('now', new DateTimeZone('Etc/GMT-3')))->format('Y-m-d H:i:s');
    }

    public static function convertLongToDateTime($value)
    {
        if ($value == null) {
            return null;
        }

        $valueInSeconds = $value;

        // Create a DateTime object from the seconds
        $date = new DateTime("@$valueInSeconds"); // The @ symbol specifies that $valueInSeconds is a Unix timestamp
        $date->setTimezone(new DateTimeZone('Etc/GMT-3')); // UTC+3 is represented as GMT-3 in the Etc format

        // return $date->format('H:i:s');
        // return $date->format('Y-m-d');
        return $date->format('Y-m-d H:i:s');
    }
    
    public static function convertLongToTime($value)
    {
        if ($value == null) {
            return null;
        }

        $midnight = Carbon::today(); // Start of today
        $timeFromSeconds = $midnight->copy()->addSeconds($value);

        return $timeFromSeconds->format('H:i:s');
    }

    public static function convertDateTimeToLong($value)
    {
        if ($value == null) {
            return null;
        }

        // Create a DateTime object from the timestamp string
        $date = DateTime::createFromFormat('Y-m-d H:i:s', $value, new DateTimeZone('Etc/GMT-3'));
        if (!$date) {
            throw new \InvalidArgumentException("Invalid datetime format: $value");
        }

        // Convert to Unix timestamp in seconds
        $timestampInSeconds = $date->getTimestamp();

        return $timestampInSeconds;
    }

    public static function convertTimeToLong($value)
    {
        if ($value === null) {
            return null;
        }

        // Create a Carbon instance from the time string
        $time = Carbon::createFromFormat('H:i:s', $value);
        if (!$time) {
            throw new \InvalidArgumentException("Invalid time format: $value");
        }

        // Calculate the seconds since midnight
        $secondsSinceMidnight = $time->diffInSeconds(Carbon::today());

        return $secondsSinceMidnight;
    }


    public static function convertDateToLong($value)
    {
        if ($value === null) {
            return null;
        }

        // Create a DateTime object from the LocalDate string
        $date = DateTime::createFromFormat('Y-m-d', $value);
        if (!$date) {
            throw new \InvalidArgumentException("Invalid LocalDate format: $value");
        }

        // Calculate the number of days since Unix epoch
        $epochDays = $date->getTimestamp() / (60 * 60 * 24); // Convert seconds to days

        return $epochDays;
    }

    public static function convertLongToDate($value)
    {
        if ($value === null) {
            return null;
        }

        // Create a DateTime object from the epoch days
        $date = DateTime::createFromFormat('U', $value * 60 * 60 * 24); // Convert days to seconds
        if (!$date) {
            throw new \InvalidArgumentException("Invalid epoch days format: $value");
        }

        // Format as LocalDate (Y-m-d)
        $localDate = $date->format('Y-m-d');

        return $localDate;
    }
}
