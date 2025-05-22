<?php

namespace App\Service\FuelCard\Mapper;

class FileMapperManager
{
    /**
     * @param array $fieldsForMapping
     * @param $columns
     * @return BaseFileMapper|mixed|null
     */
    public function getMapperObj(array $fieldsForMapping, $columns)
    {
        /** @var BaseFileMapper $fieldsMapper */
        foreach ($fieldsForMapping as $fieldsMapper) {
            $counter = 0;
            if ($fieldsMapper->isSearchHeader() === true) {
                foreach ($columns as $column) {
                    $in = $fieldsMapper->getInternalMappedFields();
                    $searchHeader = array_uintersect(
                        $column,
                        $fieldsMapper->getInternalMappedFields(),
                        "strcasecmp"
                    );

                    if (count($searchHeader) === count($fieldsMapper->getInternalMappedFields())) {
                        $fieldsMapper->setHeader($searchHeader);
                        return $fieldsMapper;
                    }

                    if ($counter === $fieldsMapper->headingSearchDepth()) {
                        break;
                    }
                    ++$counter;
                }
            }
        }

        return $fieldsMapper ?? null;
    }
}
