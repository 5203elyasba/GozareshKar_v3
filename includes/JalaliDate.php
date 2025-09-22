<?php

class JalaliDate {

    /**
     * Converts a Gregorian DateTime object to a formatted Jalali date string.
     * Matches the format: "Weekday YYYY/M/D" (e.g., "یکشنبه 1404/3/11").
     *
     * @param DateTime|string $date The date to convert. Can be a DateTime object or a string.
     * @return string The formatted Jalali date string, or an empty string if the input is invalid.
     */
    public static function toJalali($date): string {
        if (is_string($date)) {
            try {
                $date = new DateTime($date);
            } catch (Exception $e) {
                return ''; // Return empty for invalid date strings
            }
        }

        if (!($date instanceof DateTime)) {
            return '';
        }

        $weekdayFormatter = new IntlDateFormatter(
            'fa_IR@calendar=persian',
            IntlDateFormatter::FULL,
            IntlDateFormatter::NONE,
            'Asia/Tehran',
            IntlDateFormatter::TRADITIONAL,
            'eeee' // Full weekday name
        );

        $dateFormatter = new IntlDateFormatter(
            'fa_IR@calendar=persian',
            IntlDateFormatter::FULL,
            IntlDateFormatter::NONE,
            'Asia/Tehran',
            IntlDateFormatter::TRADITIONAL,
            'yyyy/M/d' // Numeric year, month, day
        );

        $weekday = $weekdayFormatter->format($date);
        $jalaliDate = $dateFormatter->format($date);

        // Intl formats numbers in Persian script, so we convert them back to Latin digits.
        $latinDate = self::convertPersianNumbersToLatin($jalaliDate);

        return "{$weekday} {$latinDate}";
    }

    /**
     * Calculates a workday by adding or subtracting a number of workdays from a start date,
     * skipping weekends based on a provided mask.
     *
     * @param DateTime $startDate The starting date.
     * @param int $days The number of workdays to add (positive) or subtract (negative).
     * @param string $weekendMask A 7-character string starting from Monday, where '1' marks a weekend day.
     *                            Default is "0000110" (Thursday/Friday weekend).
     * @return string The final workday formatted as a Jalali date string.
     */
    public static function getWorkday(DateTime $startDate, int $days, string $weekendMask = "0000110"): string {
        $currentDate = clone $startDate;
        $step = $days >= 0 ? 1 : -1;
        $remainingDays = abs($days);

        while ($remainingDays > 0) {
            $currentDate->modify("{$step} day");
            // getDay() equivalent in PHP: N for ISO-8601 day of the week (1=Mon, 7=Sun)
            $dayOfWeek = (int)$currentDate->format('N'); // 1 (for Monday) through 7 (for Sunday)

            // The user's mask starts from Monday, which matches PHP's 'N' format (index 0 for Mon).
            $maskIndex = $dayOfWeek - 1;

            if (isset($weekendMask[$maskIndex]) && $weekendMask[$maskIndex] === '0') {
                $remainingDays--;
            }
        }

        return self::toJalali($currentDate);
    }

    /**
     * Generates a series of Jalali date strings for a given number of days,
     * stopping if the Jalali year changes.
     *
     * @param DateTime $startDate The start date.
     * @param int $maxDays Maximum number of days to generate.
     * @return array An array of formatted Jalali date strings.
     */
    public static function getJalaliSeries(DateTime $startDate, int $maxDays = 370): array {
        $startJalaliYear = self::getJalaliYear($startDate);
        $output = [];

        for ($i = 0; $i < $maxDays; $i++) {
            $currentDate = clone $startDate;
            $currentDate->modify("+$i day");

            if (self::getJalaliYear($currentDate) !== $startJalaliYear) {
                $output[] = '';
            } else {
                $output[] = self::toJalali($currentDate);
            }
        }
        return $output;
    }

    /**
     * Gets the numeric Jalali year from a DateTime object.
     *
     * @param DateTime $date The date to extract the year from.
     * @return int The Jalali year.
     */
    public static function getJalaliYear(DateTime $date): int {
        $formatter = new IntlDateFormatter(
            'fa_IR@calendar=persian',
            IntlDateFormatter::FULL,
            IntlDateFormatter::NONE,
            'Asia/Tehran',
            IntlDateFormatter::TRADITIONAL,
            'y' // Year
        );
        $jalaliYear = $formatter->format($date);
        return (int)self::convertPersianNumbersToLatin($jalaliYear);
    }

    /**
     * Helper function to convert Persian (Farsi) number characters to Latin (English) digits.
     *
     * @param string $string The string containing Persian numbers.
     * @return string The string with numbers converted to Latin digits.
     */
    private static function convertPersianNumbersToLatin(string $string): string {
        $persian = ['۰', '۱', '۲', '۳', '۴', '۵', '۶', '۷', '۸', '۹'];
        $latin = ['0', '1', '2', '3', '4', '5', '6', '7', '8', '9'];
        return str_replace($persian, $latin, $string);
    }

    /**
     * Converts a Jalali date string (e.g., "1404/5/21") to a Gregorian DateTime object.
     *
     * @param string $jalaliDateString The Jalali date in a format like 'yyyy/M/d'.
     * @return DateTime|false A DateTime object on success, or false on failure.
     */
    public static function fromJalaliToDateTime(string $jalaliDateString) {
        // Normalize the input string to use Latin numbers for parsing
        $normalizedDate = self::convertPersianNumbersToLatin($jalaliDateString);

        $formatter = new IntlDateFormatter(
            'fa_IR@calendar=persian',
            IntlDateFormatter::NONE, // No need for specific date/time format for parsing
            IntlDateFormatter::NONE,
            'Asia/Tehran',
            IntlDateFormatter::TRADITIONAL,
            'yyyy/M/d' // Define the exact pattern of the input string
        );

        // The parser returns a timestamp on success, or false on failure.
        $timestamp = $formatter->parse($normalizedDate);

        if ($timestamp === false) {
            // Attempt a different pattern if the first one fails, e.g. with two-digit month/day
            $formatter->setPattern('yyyy/MM/dd');
            $timestamp = $formatter->parse($normalizedDate);
            if ($timestamp === false) {
                 return false;
            }
        }

        return (new DateTime())->setTimestamp($timestamp);
    }
}
