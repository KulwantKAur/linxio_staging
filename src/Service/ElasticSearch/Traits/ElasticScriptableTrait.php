<?php

namespace App\Service\ElasticSearch\Traits;


trait ElasticScriptableTrait
{
    /**
     * @param string $field
     * @param int $days
     * @return array
     */
    public function calculateRemainingDays(string $field, $days)
    {
        if (!is_numeric($days)) {
            return [];
        }
        return [$field => "(doc['$field'].value.millis - new Date().getTime()) / 1000 / 60 / 60 / 24  == $days"];
    }
}