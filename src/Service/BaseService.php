<?php

namespace App\Service;

use App\Entity\BaseEntity;
use App\Entity\User;
use App\Exceptions\ValidationException;
use App\Report\Core\Formatter\Header\HeaderFormatterInterface;
use App\Service\ElasticSearch\ElasticSearch;
use App\Service\ElasticSearch\Traits\ElasticScriptableTrait;
use App\Util\TranslateHelper;
use Carbon\Carbon;
use Doctrine\ORM\UnitOfWork;
use Symfony\Contracts\Translation\TranslatorInterface;
use Symfony\Component\Validator\ConstraintViolation;
use Symfony\Component\Validator\Validator\ValidatorInterface;

abstract class BaseService
{
    use ElasticScriptableTrait;

    public const ELASTIC_NESTED_FIELDS = [];
    public const ELASTIC_SIMPLE_FIELDS = [];
    public const ELASTIC_RANGE_FIELDS = [];
    public const ELASTIC_AGGREGATIONS_FIELDS = [];
    public const ELASTIC_SCRIPT_FIELDS = [];
    public const ELASTIC_CASE_OR = 'caseOr';
    public const ELASTIC_SORT_FIELDS = [];

    public const ACTION_CREATE = 'create';
    public const ACTION_EDIT = 'edit';

    /**
     * @param $fullSearchData
     * @return array|string|null
     * @throws \Exception
     */
    private function prepareFullSearchData($fullSearchData)
    {
        if (!$fullSearchData) {
            return $fullSearchData;
        }

        if (!defined(static::class . '::ELASTIC_FULL_SEARCH_FIELDS')) {
            throw new \Exception('You need to init const ELASTIC_FULL_SEARCH_FIELDS in class ' . static::class);
        }

        return is_array($fullSearchData)
            ? $fullSearchData
            : [
                'key' => static::ELASTIC_FULL_SEARCH_FIELDS,
                'value' => $fullSearchData
            ];
    }

    /**
     * @param array $params
     * @return array
     */
    protected function prepareElasticFields(array $params): array
    {
        $fields = [];
        $fields['page'] = $params['page'] ?? 1;
        if (isset($params['limit']) && $params['limit']) {
            $fields['limit'] = $params['limit'] === 'all' ? ElasticSearch::MAX_PAGE_LIMIT : $params['limit'];
        } else {
            $fields['limit'] = 10;
        }
//        $fields['limit'] = $params['limit'] ?? 10;
        $fields['aggregations'] = $params['aggregations'] ?? [];
        $fields['fields'] = [];
        $fields['fullSearch'] = isset($params['fullSearch']) ? $this->prepareFullSearchData($params['fullSearch']) : null;
        $sortFields = isset($params['sort']) && $params['sort'] ? explode(',', $params['sort']) : [];
        $fields['sort'] = [];
        $fields['_source'] = $params['fields'] ?? [];
        unset($params['fields']);

        foreach ($sortFields as $sortItem) {
            $prefix = !str_contains($sortItem, '-') ? '' : '-';
            if ($prefix) {
                $sortItem = str_replace('-', '', $sortItem);
            }
            if (isset(static::ELASTIC_SIMPLE_FIELDS[$sortItem])) {
                $fields['sort']['simple'] = $prefix . static::ELASTIC_SIMPLE_FIELDS[$sortItem];
            }
            if (isset(static::ELASTIC_RANGE_FIELDS[$sortItem])) {
                $fields['sort']['simple'] = $prefix . static::ELASTIC_RANGE_FIELDS[$sortItem];
            }
            if (isset(static::ELASTIC_NESTED_FIELDS[$sortItem])) {
                $fields['sort']['nested'] = $prefix . static::ELASTIC_NESTED_FIELDS[$sortItem];
            }
            if (isset(static::ELASTIC_AGGREGATIONS_FIELDS[$sortItem])) {
                $fields['sort']['aggregations'] = $prefix . static::ELASTIC_AGGREGATIONS_FIELDS[$sortItem];
            }
            if (isset(static::ELASTIC_SCRIPT_FIELDS[$sortItem])) {
                $fields['sort']['script'] = $prefix . static::ELASTIC_SCRIPT_FIELDS[$sortItem];
            }
            if (isset(static::ELASTIC_SORT_FIELDS[$sortItem])) {
                $fields['sort']['simple'] = $prefix . static::ELASTIC_SORT_FIELDS[$sortItem];
            }
        };
        foreach ($params as $key => $param) {
            if (isset(static::ELASTIC_NESTED_FIELDS[$key])) {
                $fields['nested'][static::ELASTIC_NESTED_FIELDS[$key]] = $param;
            } elseif (isset(static::ELASTIC_SIMPLE_FIELDS[$key])) {
                $fields['fields'][static::ELASTIC_SIMPLE_FIELDS[$key]] = $param;
            } elseif (isset(static::ELASTIC_RANGE_FIELDS[$key])) {
                $fields['range'][static::ELASTIC_RANGE_FIELDS[$key]] = $param;
            } elseif (isset(static::ELASTIC_AGGREGATIONS_FIELDS[$key])) {
                $fields['aggregations'][static::ELASTIC_AGGREGATIONS_FIELDS[$key]] = $param;
            } elseif (isset(static::ELASTIC_SCRIPT_FIELDS[$key])) {
                $fields['script'][static::ELASTIC_SCRIPT_FIELDS[$key]] = $param;
            } elseif ($key === self::ELASTIC_CASE_OR) {
                $fields[self::ELASTIC_CASE_OR] = $param;
            }
        }

        return $fields;
    }

    public function translateEntityArrayForExport(
        $items,
        $fields = [],
        $entityName = null,
        ?User $user = null,
        ?HeaderFormatterInterface $headerFormatter = null
    ) {
        return TranslateHelper::translateEntityArrayForExport(
            $items, $this->translator, $fields, $entityName, $user, [], $headerFormatter
        );
    }

    /**
     * @param string|\DateTime $date
     * @return Carbon
     */
    public static function parseDateToUTC($date, ?string $timezone = null): Carbon
    {
        $date = match (true) {
            $date instanceof \DateTime => Carbon::instance($date),
            default => Carbon::parse($date, $timezone),
        };

        return $date->setTimezone('UTC');
    }

    /**
     * @param string $date
     * @return Carbon
     */
    public static function parseUrlDateToUTC(string $date)
    {
        return self::parseDateToUTC(urldecode($date));
    }

    /**
     * @param ValidatorInterface $validator
     * @param object $entity
     * @param array $groups
     * @param TranslatorInterface $translator
     */
    public function validate(
        ValidatorInterface $validator,
        $entity,
        $groups = [],
        ?TranslatorInterface $translator = null
    ): void {
        $constraints = $validator->validate($entity, null, $groups);
        $errors = [];

        /** @var ConstraintViolation $constraint */
        foreach ($constraints as $constraint) {
            $property = $constraint->getPropertyPath();
            $errors[$property] = $constraint->getMessage();
        }

        if (count($errors)) {
            throw (new ValidationException())->setErrors($errors);
        }
    }

    /**
     * @param $needle
     * @param array $haystack
     * @return array
     */
    public function getSubstringsInArray($needle, array $haystack)
    {
        $filtered = array_filter($haystack, function ($item) use ($needle) {
            return false !== strpos($item, $needle);
        });

        return $filtered;
    }

    /**
     * @param $needle
     * @param array $haystack
     * @return array
     */
    public function getSubstringsValuesInArray($needle, array $haystack)
    {
        $data = $this->getSubstringsInArray($needle, $haystack);

        return array_map(function ($item) use ($needle) {
            return str_replace($needle, '', $item);
        }, $data);
    }

    /**
     * @param UnitOfWork $uow
     * @param $entity
     * @param string $field
     * @return bool
     */
    public function isEntityFieldChanged(UnitOfWork $uow, $entity, string $field): bool
    {
        $uow->computeChangeSets();
        $changeSet = $uow->getEntityChangeSet($entity);

        return isset($changeSet[$field]);
    }

    /**
     * @param UnitOfWork $uow
     * @param $entity
     * @param string $field
     * @return array|null
     */
    public function getEntityFieldChangeSet(UnitOfWork $uow, $entity, string $field): ?array
    {
        $uow->computeChangeSets();
        $changeSet = $uow->getEntityChangeSet($entity);

        return $changeSet[$field] ?? null;
    }

    /**
     * @param UnitOfWork $uow
     * @param $entity
     * @param string $field
     * @return mixed|null
     */
    public function getEntityFieldOldValue(UnitOfWork $uow, $entity, string $field)
    {
        $changeSet = $this->getEntityFieldChangeSet($uow, $entity, $field);

        return $changeSet ? $changeSet[0] : null;
    }

    /**
     * @param array $params
     * @return array
     */
    public static function handleDepotAndGroupsParams(array $params)
    {
        if (isset($params['depot']) && is_array($params['depot']) && count($params['depot']) > 1 && in_array('null',
                $params['depot'], true)) {
            $params[self::ELASTIC_CASE_OR]['depot.id'][] = array_diff($params['depot'], ['null']);
            $params[self::ELASTIC_CASE_OR]['depot.id'][] = null;
            unset($params['depot']);
        } elseif (isset($params['depot']) && is_array($params['depot']) && in_array('null', $params['depot'], true)) {
            $params['depot'] = null;
        }

        if (isset($params['groups']) && is_array($params['groups']) && count($params['groups']) > 1 && in_array('null',
                $params['groups'], true)) {
            $params[self::ELASTIC_CASE_OR]['groups.id'][] = array_diff($params['groups'], ['null']);
            $params[self::ELASTIC_CASE_OR]['groups.id'][] = null;
            unset($params['groups']);
        } elseif (isset($params['groups']) && is_array($params['groups']) && in_array('null', $params['groups'],
                true)) {
            $params['groups'] = null;
        }

        return $params;
    }

    /**
     * @param array $items
     * @param array $include
     * @param User|null $user
     * @return array
     */
    public function formatNestedItemsToArray(array $items, $include = [], ?User $user = null): array
    {
        return array_map(
            function (BaseEntity $entity) use ($include, $user) {
                return $entity->toArray($include, $user);
            },
            $items
        );
    }

    /**
     * @param int $deviceId
     * @param int $queuesNumber
     * @param string $prefix
     * @return string
     * @throws \Exception
     */
    public function getRoutingKeyByDeviceIdQueuesNumberAndPrefix(
        int $deviceId,
        int $queuesNumber,
        string $prefix
    ): string {
        $processNumber = $queuesNumber - ($deviceId % $queuesNumber);

        return $prefix . $processNumber;
    }

    /**
     * @param string $input
     * @return string|null
     */
    public static function camelToSnake(string $input): ?string
    {
        return strtolower(preg_replace('/(?<!^)[A-Z]/', '_$0', $input));
    }

    /**
     * @param string $input
     * @return string|null
     */
    public static function snakeToCamel(string $input): ?string
    {
        return lcfirst(str_replace(' ', '', ucwords(str_replace('_', ' ', $input))));
    }

    /**
     * @param array $data
     * @return array
     */
    public static function replaceNestedArrayKeysToCamelCase(array $data): array
    {
        foreach ($data as $key => $set) {
            $newSet = [];

            foreach ($set as $setKey => $item) {
                $newSetKey = self::snakeToCamel($setKey);
                $newSet[$newSetKey] = $item;
            }

            $data[$key] = $newSet;
        }

        return $data;
    }
}