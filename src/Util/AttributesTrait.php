<?php

namespace App\Util;

trait AttributesTrait
{
    /**
     * @param array $fields
     */
    public function setAttributes(array $fields)
    {
        foreach ($fields as $key => $field) {
            if ($fields[$key] === '') {
                $fields[$key] = null;
            }
            if (defined("self::EDITABLE_FIELDS")) {
                if (property_exists(self::class, $key) && in_array($key, self::EDITABLE_FIELDS)) {
                    $this->$key = $fields[$key];
                }
            } elseif (property_exists(self::class, $key)) {
                $this->$key = $fields[$key];
            }
        }
    }
}