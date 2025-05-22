<?php

namespace App\Entity;

use App\Util\DateHelper;
use App\Util\StringHelper;

abstract class BaseEntity
{
    public const EXPORT_DATE_FORMAT = 'd/m/Y H:i';
    public const EXPORT_DATE_WITHOUT_TIME_FORMAT = 'd/m/Y';
    public const EXPORT_TIME_FORMAT = 'H:i';
    public const EXPORT_MAX_COUNT = 10000;
    public const STATUS_ALL = 'all';
    public const STATUS_DELETED = 'deleted';
    public const STATUS_DISABLED = 'disabled';
    public const STATUS_ACTIVE = 'active';
    public const STATUS_BLOCKED = 'blocked';
    public const STATUS_BLOCKED_OVERDUE = 'blocked_overdue';
    public const STATUS_ENABLED = 'enabled';
    public const STATUS_ARCHIVE = 'archive';
    public const STATUS_DELETED_NUM = 0;
    public const STATUS_ACTIVE_NUM = 1;
    public const STATUS_UNAVAILABLE = 'unavailable';

    public const VALIDATION_TYPE_WARNING = 'warning';
    public const VALIDATION_TYPE_ERROR = 'error';

    public const ALLOWED_STATUSES = [];

    public const LIST_STATUSES = [];

    abstract public function toArray(): array;

    /**
     * @param \DateTimeInterface|null $date
     * @param string $format
     * @param mixed $timezone
     * @return string|null
     * @throws \Exception
     */
    protected function formatDate($date = null, $format = 'c', $timezone = null): ?string
    {
        return DateHelper::formatDate($date, $format, $timezone);
    }

    /**
     * @param array|null $coordinates
     * @return string|null
     */
    public function convertCoordinatesToPoint(?array $coordinates): ?string
    {
        if ($coordinates) {
            $lat = $coordinates['lat'];
            $lng = $coordinates['lng'];

            if ($lat && $lng) {
                $value = "POINT($lng $lat)";
            }
        }

        return $value ?? null;
    }

    /**
     * @param string|null $point
     * @return array
     */
    public function convertPointToCoordinates(?string $point)
    {
        if ($point) {
            list($longitude, $latitude) = sscanf($point, 'POINT(%f %f)');

            return [
                'lat' => $latitude,
                'lng' => $longitude
            ];
        } else {
            return [
                'lat' => null,
                'lng' => null
            ];
        }
    }

    /**
     * @param string $prefix
     * @param array $include
     * @param array $defaultInclude
     * @return array|null
     */
    public function getNestedIncludeByPrefix(string $prefix, array $include = [], array $defaultInclude = []): ?array
    {
        $nestedInclude = [];
        $prefix = $prefix . '.';

        foreach ($include as $item) {
            $prefixPosition = strpos($item, $prefix);

            if ($prefixPosition !== false) {
                $prefixLength = strlen($prefix) + $prefixPosition;

                $nestedInclude[] = substr($item, strpos($item, $prefix) + $prefixLength);
            }
        }

        return $nestedInclude ? array_unique($nestedInclude) : $defaultInclude;
    }

    public function getNestedFields($prefix, $include, $data, ?User $user = null)
    {
        if (!empty($this->getNestedIncludeByPrefix($prefix, $include))) {
            $method = 'get' . ucfirst($prefix);

            if (method_exists($this, $method)) {
                $object = call_user_func(array($this, $method));

                if ($user) {
                    $nestedData = $object?->toArray($this->getNestedIncludeByPrefix($prefix, $include), $user);
                } else {
                    $nestedData = $object?->toArray($this->getNestedIncludeByPrefix($prefix, $include));
                }

                $dataValue = array_merge($data[$prefix] ?? [], $nestedData ?? []);
                $data[$prefix] = empty($dataValue) ? null : $dataValue;
            }
        }

        return $data;
    }

    public function __toString()
    {
        return method_exists($this, 'getId') ? strval($this->getId()) : '';
    }

    public static function handleStatusParams(array $params, $key = 'status')
    {
        if (isset($params[$key])) {
            $params[$key] = $params[$key] === self::STATUS_ALL ? static::ALLOWED_STATUSES : $params[$key];
        } else {
            $params[$key] = static::LIST_STATUSES;
        }

        if (isset($params['showArchived']) && StringHelper::stringToBool($params['showArchived'])) {
            if (is_array($params[$key])) {
                $params[$key][] = self::STATUS_ARCHIVE;
            }
        } elseif ($params[$key] === self::STATUS_ARCHIVE) {
            $params[$key] = '';
        }

        if (isset($params['showUnavailable']) && StringHelper::stringToBool($params['showUnavailable'])) {
            if (is_array($params[$key])) {
                $params[$key][] = self::STATUS_UNAVAILABLE;
            }
        } elseif ($params[$key] === self::STATUS_UNAVAILABLE) {
            $params[$key] = '';
        }

        return $params;
    }
}