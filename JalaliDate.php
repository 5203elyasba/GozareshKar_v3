<?php

class JalaliDate {

    /**
     * Converts a Gregorian DateTime object or a date string to a formatted Jalali date string.
     * @param DateTime|string $date The date to convert.
     * @return string The formatted Jalali date string in 'YYYY/MM/DD' format.
     */
    public static function toJalali($date): string {
        if (is_string($date)) {
            try {
                $date = new DateTime($date);
            } catch (Exception $e) {
                return ''; // Return empty string for invalid date format
            }
        }
        if (!($date instanceof DateTime)) {
            return '';
        }

        $formatter = new IntlDateFormatter(
            'fa_IR@calendar=persian',
            IntlDateFormatter::FULL,
            IntlDateFormatter::NONE,
            'Asia/Tehran',
            IntlDateFormatter::TRADITIONAL,
            'yyyy/MM/dd'
        );
        return self::convertNumbersToLatin($formatter->format($date));
    }

    /**
     * Converts a Jalali date string (e.g., "1404/05/21") to a Gregorian DateTime object.
     * @param string $jalaliDateString The Jalali date string.
     * @return DateTime|false A DateTime object on success, or false on failure.
     */
    public static function fromJalaliToDateTime(string $jalaliDateString) {
        $normalizedDate = self::convertNumbersToLatin($jalaliDateString);

        $formatter = new IntlDateFormatter(
            'fa_IR@calendar=persian',
            IntlDateFormatter::NONE,
            IntlDateFormatter::NONE,
            'Asia/Tehran',
            IntlDateFormatter::TRADITIONAL,
            'yyyy/M/d' // More flexible pattern for parsing
        );

        $timestamp = $formatter->parse($normalizedDate);

        if ($timestamp === false) {
            // Try another common pattern
            $formatter->setPattern('yyyy/MM/dd');
            $timestamp = $formatter->parse($normalizedDate);
            if ($timestamp === false) {
                return false;
            }
        }

        return (new DateTime())->setTimestamp($timestamp);
    }

    /**
     * Helper function to convert Persian (Farsi) number characters to Latin (English) digits.
     * @param string $string The string containing Persian numbers.
     * @return string The string with Latin digits.
     */
    private static function convertNumbersToLatin(string $string): string {
        $persian = ['۰', '۱', '۲', '۳', '۴', '۵', '۶', '۷', '۸', '۹'];
        $latin = ['0', '1', '2', '3', '4', '5', '6', '7', '8', '9'];
        return str_replace($persian, $latin, $string);
    }
}
