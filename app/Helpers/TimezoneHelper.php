<?php
// app/Helpers/TimezoneHelper.php

namespace App\Helpers;

use Carbon\Carbon;

class TimezoneHelper
{
    /**
     * WITA Timezone identifier
     */
    const WITA_TIMEZONE = 'Asia/Makassar';

    /**
     * Get current date in WITA timezone
     *
     * @return Carbon
     */
    public static function getWitaDate(): Carbon
    {
        return Carbon::now(self::WITA_TIMEZONE);
    }

    /**
     * Get start of today in WITA timezone
     *
     * @return Carbon
     */
    public static function getWitaTodayStart(): Carbon
    {
        return self::getWitaDate()->startOfDay();
    }

    /**
     * Get end of today in WITA timezone
     *
     * @return Carbon
     */
    public static function getWitaTodayEnd(): Carbon
    {
        return self::getWitaDate()->endOfDay();
    }

    /**
     * Get start of yesterday in WITA timezone
     *
     * @return Carbon
     */
    public static function getWitaYesterdayStart(): Carbon
    {
        return self::getWitaDate()->subDay()->startOfDay();
    }

    /**
     * Get end of yesterday in WITA timezone
     *
     * @return Carbon
     */
    public static function getWitaYesterdayEnd(): Carbon
    {
        return self::getWitaDate()->subDay()->endOfDay();
    }

    /**
     * Get start of this week in WITA timezone (Monday)
     *
     * @return Carbon
     */
    public static function getWitaThisWeekStart(): Carbon
    {
        return self::getWitaDate()->startOfWeek();
    }

    /**
     * Get start of this month in WITA timezone
     *
     * @return Carbon
     */
    public static function getWitaThisMonthStart(): Carbon
    {
        return self::getWitaDate()->startOfMonth();
    }

    /**
     * Convert UTC datetime to WITA timezone
     *
     * @param string|Carbon $datetime
     * @return Carbon
     */
    public static function convertToWita($datetime): Carbon
    {
        if (is_string($datetime)) {
            $datetime = Carbon::parse($datetime);
        }
        
        return $datetime->setTimezone(self::WITA_TIMEZONE);
    }

    /**
     * Check if given datetime is today in WITA timezone
     *
     * @param string|Carbon $datetime
     * @return bool
     */
    public static function isToday($datetime): bool
    {
        $witaDate = self::convertToWita($datetime);
        $todayStart = self::getWitaTodayStart();
        $todayEnd = self::getWitaTodayEnd();

        return $witaDate->between($todayStart, $todayEnd);
    }

    /**
     * Check if given datetime is yesterday in WITA timezone
     *
     * @param string|Carbon $datetime
     * @return bool
     */
    public static function isYesterday($datetime): bool
    {
        $witaDate = self::convertToWita($datetime);
        $yesterdayStart = self::getWitaYesterdayStart();
        $yesterdayEnd = self::getWitaYesterdayEnd();

        return $witaDate->between($yesterdayStart, $yesterdayEnd);
    }

    /**
     * Check if given datetime is this week in WITA timezone
     *
     * @param string|Carbon $datetime
     * @return bool
     */
    public static function isThisWeek($datetime): bool
    {
        $witaDate = self::convertToWita($datetime);
        $weekStart = self::getWitaThisWeekStart();
        $now = self::getWitaDate();

        return $witaDate->between($weekStart, $now);
    }

    /**
     * Get time-based greeting for WITA timezone
     *
     * @return array
     */
    public static function getTimeBasedGreeting(): array
    {
        $hour = (int) self::getWitaDate()->format('H');
        
        if ($hour >= 5 && $hour < 12) {
            return [
                'greeting' => 'Pagi Ini',
                'period' => 'morning',
                'icon' => 'sunrise',
                'color' => 'orange'
            ];
        } elseif ($hour >= 12 && $hour < 17) {
            return [
                'greeting' => 'Siang Ini',
                'period' => 'afternoon',
                'icon' => 'sun',
                'color' => 'blue'
            ];
        } elseif ($hour >= 17 && $hour < 21) {
            return [
                'greeting' => 'Sore Ini',
                'period' => 'evening',
                'icon' => 'sunset',
                'color' => 'purple'
            ];
        } else {
            return [
                'greeting' => 'Malam Ini',
                'period' => 'night',  
                'icon' => 'moon',
                'color' => 'indigo'
            ];
        }
    }

    /**
     * Format date for Indonesian locale with WITA timezone
     *
     * @param string|Carbon $datetime
     * @param string $format
     * @return string
     */
    public static function formatIndonesian($datetime, string $format = 'd F Y, H:i'): string
    {
        $witaDate = self::convertToWita($datetime);
        
        // Indonesian month names
        $months = [
            1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 4 => 'April',
            5 => 'Mei', 6 => 'Juni', 7 => 'Juli', 8 => 'Agustus',
            9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Desember'
        ];

        // Indonesian day names
        $days = [
            0 => 'Minggu', 1 => 'Senin', 2 => 'Selasa', 3 => 'Rabu',
            4 => 'Kamis', 5 => 'Jumat', 6 => 'Sabtu'
        ];

        $formatted = $witaDate->format($format);
        
        // Replace month numbers with Indonesian names
        foreach ($months as $num => $name) {
            $formatted = str_replace($witaDate->format('F'), $name, $formatted);
        }

        // Replace day names if present in format
        if (strpos($format, 'l') !== false) {
            $formatted = str_replace($witaDate->format('l'), $days[$witaDate->dayOfWeek], $formatted);
        }

        return $formatted . ' WITA';
    }

    /**
     * Get human readable time difference in Indonesian
     *
     * @param string|Carbon $datetime
     * @return string
     */
    public static function getHumanDiff($datetime): string
    {
        $witaDate = self::convertToWita($datetime);
        $now = self::getWitaDate();
        
        $diffInMinutes = $now->diffInMinutes($witaDate);
        $diffInHours = $now->diffInHours($witaDate);
        $diffInDays = $now->diffInDays($witaDate);

        if ($diffInMinutes < 60) {
            return $diffInMinutes . ' menit yang lalu';
        } elseif ($diffInHours < 24) {
            return $diffInHours . ' jam yang lalu';
        } elseif ($diffInDays == 1) {
            return 'Kemarin';
        } elseif ($diffInDays < 7) {
            return $diffInDays . ' hari yang lalu';
        } else {
            return self::formatIndonesian($datetime, 'd F Y');
        }
    }

    /**
     * Get business hours status in WITA
     *
     * @return array
     */
    public static function getBusinessHoursStatus(): array
    {
        $hour = (int) self::getWitaDate()->format('H');
        $dayOfWeek = self::getWitaDate()->dayOfWeek;
        
        // Check if it's weekend (Saturday = 6, Sunday = 0)
        $isWeekend = in_array($dayOfWeek, [0, 6]);
        
        // Business hours: 8 AM - 5 PM on weekdays
        $isBusinessHours = !$isWeekend && $hour >= 8 && $hour < 17;
        
        return [
            'is_business_hours' => $isBusinessHours,
            'is_weekend' => $isWeekend,
            'current_hour' => $hour,
            'day_of_week' => $dayOfWeek,
            'status' => $isBusinessHours ? 'Jam Kerja' : ($isWeekend ? 'Akhir Pekan' : 'Di Luar Jam Kerja')
        ];
    }
}