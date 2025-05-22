<?php

namespace App\Util;

use App\Entity\BaseEntity;
use App\Entity\Setting;
use App\Entity\Team;
use App\Entity\TimeZone;
use App\Exceptions\ValidationException;
use App\Repository\TimeZoneRepository;
use Carbon\Carbon;
use DateTime;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

class DateHelper
{
    public const DAY = 'day';
    public const WEEK = 'week';
    public const MONTH = 'month';
    public const HOUR_TO_SECONDS = 3600;
    public const DAY_TO_SECONDS = 86400;
    public const MONTH_TO_SECONDS = 2592000;
    public const MINUTE_TO_SECONDS = 60;
    public const FORMAT_DATE_SHORT_TIME = 'Y-m-d H:i';
    public const NTF_DEFAULT_FORMAT_DATE = 'd-m-Y H:i';
    public const FORMAT_DATE_STRING = 'Y-m-d H:i:s';


    /**
     * @param \DateTimeInterface|null $date
     * @param string $format
     * @param null $timezone
     * @return string|null
     * @throws \Exception
     */
    public static function formatDate($date = null, $format = 'c', $timezone = null): ?string
    {
        if ($date) {
            if (!($date instanceof \DateTimeInterface)) {
                $date = new \DateTime($date);
            }
            if ($timezone) {
                $date->setTimezone(new \DateTimeZone($timezone));
            }

            $date = $date->format($format);
        }

        return $date;
    }

    /**
     * @param \DateTime $date
     * @return int
     * @throws \Exception
     */
    public static function getDaysCountBeforeDate(\DateTime $date)
    {
        $diff = (new \DateTime())->diff($date);

        if ($diff->format('%r%a') === '0' && $diff->format('%h') > 0) {
            return 1;
        } else {
            return (int)$diff->format('%r%a');
        }
    }

    /**
     * @param $type
     * @param $count
     * @param Carbon $originalDate
     * @return array
     * @throws \Exception
     */
    public static function getRanges($type, $count, Carbon $originalDate)
    {
        $ranges = [];
        for ($i = 0; $i < $count; $i++) {
            $date = clone $originalDate;
            switch ($type) {
                case self::DAY:
                    $day = $date->subDays($i);
                    $ranges[] = [
                        'start' => self::formatDate(clone $day->startOfDay()),
                        'end' => self::formatDate($day->endOfDay())
                    ];
                    break;

                case self::WEEK:
                    $week = $date->subWeek($i);
                    $ranges[] = [
                        'start' => self::formatDate(clone $week->startOfWeek()),
                        'end' => self::formatDate($week->endOfWeek())
                    ];
                    break;

                case self::MONTH:
                    $week = $date->subMonthNoOverflow($i);
                    $ranges[] = [
                        'start' => self::formatDate(clone $week->startOfMonth()),
                        'end' => self::formatDate($week->endOfMonth())
                    ];
                    break;
            }
        }

        return $ranges;
    }

    /**
     * @param \DateTime $date
     *
     * @return \DateTime
     */
    public static function getFirstDayOfMonth(DateTime $date): DateTime
    {
        $newDate = clone $date;

        return $newDate
            ->setDate($newDate->format('Y'), $newDate->format('m'), 1)
            ->setTime(0, 0);
    }

    /**
     * @param \DateTime $date
     *
     * @return \DateTime
     */
    public static function getLastDayOfMonth(DateTime $date): DateTime
    {
        $newDate = clone $date;

        return $newDate
            ->setDate($newDate->format('Y'), $newDate->format('m'), 1)
            ->setTime(23, 59, 59)
            ->modify('+1 month')
            ->modify('-1 day');
    }

    public static function getTokenExpireAtString($ttl)
    {
        return DateHelper::formatDate((new DateTime)->setTimestamp(time() + $ttl), 'c');
    }

    /**
     * @param $ss
     * @return string|null
     */
    public static function seconds2human($ss)
    {
        if (!$ss) {
            return "";
        }

        $time = [];
        $string = "";

        $s = $ss % self::MINUTE_TO_SECONDS;
        $m = floor(($ss % self::HOUR_TO_SECONDS) / self::MINUTE_TO_SECONDS);
        $h = floor(($ss % self::DAY_TO_SECONDS) / self::HOUR_TO_SECONDS);
        $d = floor(($ss % self::MONTH_TO_SECONDS) / self::DAY_TO_SECONDS);
        $M = floor($ss / self::MONTH_TO_SECONDS);

        if ($M) {
            $time[] = $M . "m ";
        }
        if ($d) {
            $time[] = $d . "d ";
        }
        if ($h) {
            $time[] = $h . "h ";
        }
        if ($m) {
            $time[] = $m . "m ";
        }
        if ($s) {
            $time[] = $s . "s";
        }

        if (isset($time[0])) {
            $string .= $time[0];
        }
        if (isset($time[1])) {
            $string .= $time[1];
        }

        return rtrim($string);
    }

    public static function seconds2period($seconds)
    {
        $t = round($seconds ?? 0);

        return sprintf('%02d:%02d:%02d', ($t / 3600), (floor($t / 60) % 60), $t % 60);
    }

    /**
     * @param array $data
     * @param array $fields
     * @param string $format
     * @param null $timezone
     * @return array
     * @throws \Exception
     */
    public static function formatValuesFromArray(
        array $data,
        array $fields,
        $timezone = null,
        string $format = BaseEntity::EXPORT_DATE_FORMAT
    ) {
        foreach ($data as $key => $value) {
            if (in_array($key, $fields)) {
                $data[$key] = DateHelper::formatDate($value, $format, $timezone);
            }
        }

        return $data;
    }

    public static function getTimeZone(array $data, EntityManagerInterface $em, TranslatorInterface $translator)
    {
        /** @var TimeZoneRepository $tzRepo */
        $tzRepo = $em->getRepository(TimeZone::class);

        if (isset($data['timezone']) && $data['timezone']) {
            $tz = $tzRepo->find($data['timezone']);

            if (null === $tz) {
                throw (new ValidationException())->setErrors(
                    [
                        'timezone' => ['wrong_value' => $translator->trans('validation.errors.field.wrong_value')]
                    ]
                );
            }
        } else {
            $tz = $tzRepo->findOneBy(['name' => TimeZone::DEFAULT_TIMEZONE['name']]);
        }

        return $tz;
    }

    public static function getDiffInDaysNow(DateTime $ts): int
    {
        $ts = new Carbon($ts);
        $now = new Carbon();

        return $ts->diffInDays($now);
    }

    public static function fieldsToPeriod(array $items, array $fields): array
    {
        foreach ($items as $key => $value) {
            if (in_array($key, $fields)) {
                $items[$key] = self::seconds2period($value);
            }
        }

        return $items;
    }

    public static function fieldsToHours(array $items, array $fields): array
    {
        foreach ($items as $key => $value) {
            if (in_array($key, $fields)) {
                $items[$key] = round($value / 3600, 1) . 'h';
            }
        }

        return $items;
    }

    public static function getDateTimeWithTimeZone(Team $team, \DateTime $dt, EntityManagerInterface $em)
    {
        $dateTime = clone $dt;
        $timeZoneSetting = $team->getSettingsByName(Setting::TIMEZONE_SETTING);
        $timeZone = $timeZoneSetting
            ? $em->getRepository(TimeZone::class)->find($timeZoneSetting->getValue())
            : null;
        if ($timeZoneSetting && $timeZone) {
            $dateTime->setTimezone(new \DateTimeZone($timeZone->getName()));
        }

        return $dateTime;
    }
    
    /**
     * @param $seconds
     * @return string|null
     */
    public static function toHours($seconds)
    {
        if (empty($seconds)) {
            return "";
        }

        $hours = floatval($seconds / self::HOUR_TO_SECONDS);

        return number_format($hours, 2).'h';
    }
}
