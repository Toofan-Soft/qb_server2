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
    public static function getDifferenceInSeconds($datetime1, $datetime2): int
    {
        try {
            $carbon1 = Carbon::parse($datetime1);
            $carbon2 = Carbon::parse($datetime2);

            return $carbon1->diffInSeconds($carbon2);
        } catch (\Exception $e) {
            throw $e;
        }
    }
    /**
     * $date1 and date2 are objects of datetime
     * return long
     * it using in lecturer online exam -> getAlgorithmData
     */
    public static function getDifferenceInDays($date1, $timestamp)
    {
        try {
            // Create a Carbon instance from the timestamp
            $date2 = Carbon::createFromTimestamp($timestamp);

            // Calculate the difference in days
            return $date1->diffInDays($date2);
        } catch (\Exception $e) {
            throw $e;
        }
    }

    /**
     * $date1 and date2 are objects of datetime
     * return long
     * it using in lecturer online exam -> getAlgorithmData
     */
    public static function convertSecondsToDays($seconds) // check 
    {
        try {
            return ($seconds / (24 * 3600));
        } catch (\Exception $e) {
            throw $e;
        }
    }


    public static function now()
    {
        try {
            return (new DateTime('now', new DateTimeZone('Etc/GMT-3')))->format('Y-m-d H:i:s');
        } catch (\Exception $e) {
            throw $e;
        }
    }

    public static function convertLongToDateTime($value)
    {
        try {
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
        } catch (\Exception $e) {
            throw $e;
        }
    }

    public static function convertLongToTime($value)
    {
        try {
            if ($value == null) {
                return null;
            }

            $midnight = Carbon::today(); // Start of today
            $timeFromSeconds = $midnight->copy()->addSeconds($value);

            return $timeFromSeconds->format('H:i:s');
        } catch (\Exception $e) {
            throw $e;
        }
    }

    public static function convertDateTimeToLong($value)
    {
        try {
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
        } catch (\Exception $e) {
            throw $e;
        }
    }

    public static function convertDatetimeToTimeToLong($value)
    {
        try {
            if ($value === null) {
                return null;
            }

            try {
                // Create a Carbon instance from the datetime string
                $datetime = Carbon::createFromFormat('Y-m-d H:i:s', $value);
            } catch (\Exception $e) {
                throw new \InvalidArgumentException("Invalid datetime format: $value");
            }

            // Extract the time part
            $timeString = $datetime->format('H:i:s');

            try {
                // Create a Carbon instance from the time string
                $time = Carbon::createFromFormat('H:i:s', $timeString);
            } catch (\Exception $e) {
                throw new \InvalidArgumentException("Invalid time format: $timeString");
            }

            // Calculate the seconds since midnight
            $secondsSinceMidnight = $time->diffInSeconds(Carbon::today());

            return $secondsSinceMidnight;
        } catch (\Exception $e) {
            throw $e;
        }
    }


    public static function convertTimeToLong($value)
    {
        try {
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
        } catch (\Exception $e) {
            throw $e;
        }
    }


    public static function convertDateToLong($value)
    {
        try {
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
        } catch (\Exception $e) {
            throw $e;
        }
    }

    public static function convertLongToDate($value)
    {
        try {
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
        } catch (\Exception $e) {
            throw $e;
        }
    }

    public static function checkEndedExam($dateTime, $duration)
    {
        try {
            $date = $dateTime + $duration;

            $now = now()->getTimestamp();

            if ($date < $now) {
                return true;
            }
            return false;
        } catch (\Exception $e) {
            throw $e;
        }
    }
}
