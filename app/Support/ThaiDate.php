<?php

namespace App\Support;

use Carbon\Carbon;
use DateTimeInterface;

class ThaiDate
{
    public const MONTHS_SHORT = [
        1 => 'ม.ค.', 2 => 'ก.พ.', 3 => 'มี.ค.', 4 => 'เม.ย.',
        5 => 'พ.ค.', 6 => 'มิ.ย.', 7 => 'ก.ค.', 8 => 'ส.ค.',
        9 => 'ก.ย.', 10 => 'ต.ค.', 11 => 'พ.ย.', 12 => 'ธ.ค.',
    ];

    public const MONTHS_FULL = [
        1 => 'มกราคม', 2 => 'กุมภาพันธ์', 3 => 'มีนาคม', 4 => 'เมษายน',
        5 => 'พฤษภาคม', 6 => 'มิถุนายน', 7 => 'กรกฎาคม', 8 => 'สิงหาคม',
        9 => 'กันยายน', 10 => 'ตุลาคม', 11 => 'พฤศจิกายน', 12 => 'ธันวาคม',
    ];

    public const DAYS_FULL = [
        0 => 'อาทิตย์', 1 => 'จันทร์', 2 => 'อังคาร', 3 => 'พุธ',
        4 => 'พฤหัสบดี', 5 => 'ศุกร์', 6 => 'เสาร์',
    ];

    /**
     * Get current Carbon instance strictly in Asia/Bangkok timezone.
     */
    public static function now(): Carbon
    {
        return Carbon::now('Asia/Bangkok');
    }

    /**
     * Parse any date into Asia/Bangkok Carbon instance.
     */
    public static function parse(DateTimeInterface|string|int|null $date = null): Carbon
    {
        if ($date === null || $date === '') {
            return self::now();
        }
        if ($date instanceof Carbon) {
            return $date->copy()->setTimezone('Asia/Bangkok');
        }
        if ($date instanceof DateTimeInterface) {
            return Carbon::instance($date)->setTimezone('Asia/Bangkok');
        }
        if (is_numeric($date)) {
            return Carbon::createFromTimestamp($date, 'Asia/Bangkok');
        }
        return Carbon::parse($date)->setTimezone('Asia/Bangkok');
    }

    /**
     * Format date in Thai Buddhist Era (พ.ศ.) e.g. "11 พ.ย. 2569" or "11 พ.ย. 2569 14:30 น."
     */
    public static function format(DateTimeInterface|string|int|null $date = null, bool $includeTime = false, bool $fullMonth = false, bool $includeSeconds = false): string
    {
        $dt = self::parse($date);
        $day = $dt->day;
        $month = $fullMonth ? (self::MONTHS_FULL[$dt->month] ?? '') : (self::MONTHS_SHORT[$dt->month] ?? '');
        $yearBE = $dt->year + 543;
        // Handle case where input year was already stored as BE (e.g. > 2400)
        if ($dt->year > 2400) {
            $yearBE = $dt->year;
        }

        $res = "{$day} {$month} {$yearBE}";
        if ($includeTime) {
            $timeFormat = $includeSeconds ? 'H:i:s' : 'H:i';
            $res .= ' ' . $dt->format($timeFormat) . ' น.';
        }

        return $res;
    }

    /**
     * Format as full Thai date with day of week: "วันพุธที่ 11 พฤศจิกายน พ.ศ. 2569"
     */
    public static function formatFull(DateTimeInterface|string|int|null $date = null, bool $includeTime = false): string
    {
        $dt = self::parse($date);
        $dayName = self::DAYS_FULL[$dt->dayOfWeek] ?? '';
        $day = $dt->day;
        $month = self::MONTHS_FULL[$dt->month] ?? '';
        $yearBE = $dt->year > 2400 ? $dt->year : ($dt->year + 543);

        $res = "วัน{$dayName}ที่ {$day} {$month} พ.ศ. {$yearBE}";
        if ($includeTime) {
            $res .= ' เวลา ' . $dt->format('H:i') . ' น.';
        }

        return $res;
    }

    /**
     * Format compact slash date in BE: "11/11/2569"
     */
    public static function formatSlash(DateTimeInterface|string|int|null $date = null, bool $includeTime = false): string
    {
        $dt = self::parse($date);
        $yearBE = $dt->year > 2400 ? $dt->year : ($dt->year + 543);
        $res = $dt->format('d/m/') . $yearBE;
        if ($includeTime) {
            $res .= ' ' . $dt->format('H:i');
        }
        return $res;
    }
}

