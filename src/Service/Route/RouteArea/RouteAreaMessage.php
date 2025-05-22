<?php

namespace App\Service\Route\RouteArea;

class RouteAreaMessage
{
    private int $routeId;

    public function __construct(int $routeId)
    {
        $this->routeId = $routeId;
    }

    /**
     * @return false|string
     */
    public function __toString()
    {
        return json_encode([
            'route_id' => $this->routeId
        ]);
    }
}
