<?php

namespace App\Util;


class PaginationHelper
{
    public static function paginationToEntityArray($pagination, $include = [])
    {
        $data = [];
        foreach ($pagination as $item) {
            $data[] = $item->toArray($include);
        }

        $pagination->setItems($data);

        return $pagination;
    }
}