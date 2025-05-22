<?php

namespace App\Util;

use App\Entity\BaseEntity;
use App\Entity\User;
use App\Report\Core\Formatter\Header\HeaderFormatterInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

class TranslateHelper
{
    private static function translateEntityFields(
        array $fields,
        string $entityName,
        TranslatorInterface $translator,
        string $domain = 'entities',
        ?array $allowedFields = null,
        bool $isHeader = false,
        array $fieldsWithoutTranslate = []
    ) {
        $data = [];
        $entityName = class_exists($entityName) ? strtolower((new \ReflectionClass($entityName))->getShortName()) : $entityName;

        foreach ($fields as $key => $field) {
            if (!$allowedFields || ($allowedFields && in_array($key, $allowedFields))) {
                if ($isHeader) {
                    if (in_array($key, $fieldsWithoutTranslate)) {
                        $data[$key] = $key;
                    } else {
                        $data[$entityName . '.' . $key] = $translator->trans($entityName . '.' . $key, [], $domain);
                    }
                } else {
                    if (in_array($key, $fieldsWithoutTranslate)) {
                        $data[$key] = $field;
                    } else {
                        $data[$entityName . '.' . $key] = $field;
                    }
                }
            }
        }
        return $data;
    }

    public static function translateEntityArrayForExport(
        $items,
        TranslatorInterface $translator,
        $fields = [],
        $entityName = null,
        ?User $user = null,
        array $fieldsWithoutTranslate = [],
        ?HeaderFormatterInterface $headerFormatter = null
    ) {
        $data = [];
        $i = 0;
        foreach ($items as $item) {
            $itemData = !is_array($item) && $item instanceof BaseEntity ? $item->toExport($fields, $user) : $item;
            if ($i == 0) {
                $data[] = self::translateEntityFields(
                    $itemData,
                    $entityName ?? get_class($item),
                    $translator,
                    'entities',
                    $fields,
                    true,
                    $fieldsWithoutTranslate
                );
                if ($headerFormatter) {
                    $data[0] = $headerFormatter->format($data[0]);
                }
            }
            $data[] = self::translateEntityFields(
                $itemData, $entityName ?? get_class($item), $translator, 'entities', $fields,
                false, $fieldsWithoutTranslate
            );
            $i++;
        }

        return $data;
    }
}