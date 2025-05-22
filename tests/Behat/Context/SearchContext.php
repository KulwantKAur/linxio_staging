<?php

namespace App\Tests\Behat\Context;


use Behatch\Json\Json;

/**
 * Defines application features from the specific context.
 */
class SearchContext extends BasicContext
{
    /**
     * @When I want make full search with query :query
     */
    public function iWantMakeFullSearch($query)
    {
        $params = http_build_query(['query' => $query]);
        $this->get('/api/search?' . $params);
    }
}
