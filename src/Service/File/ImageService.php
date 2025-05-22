<?php

namespace App\Service\File;

use App\Service\BaseService;
use Liip\ImagineBundle\Binary\BinaryInterface;
use Liip\ImagineBundle\Imagine\Cache\CacheManager;
use Liip\ImagineBundle\Imagine\Data\DataManager;
use Liip\ImagineBundle\Imagine\Filter\FilterManager;

class ImageService extends BaseService
{
    private CacheManager $cacheManager;
    private DataManager $dataManager;
    private FilterManager $filterManager;

    /**
     * ImageService constructor.
     * @param CacheManager $cacheManager
     * @param DataManager $dataManager
     * @param FilterManager $filterManager
     */
    public function __construct(CacheManager $cacheManager, DataManager $dataManager, FilterManager $filterManager)
    {
        $this->cacheManager = $cacheManager;
        $this->dataManager = $dataManager;
        $this->filterManager = $filterManager;
    }

    /**
     * @return FilterManager
     */
    public function getFilterManager(): FilterManager
    {
        return $this->filterManager;
    }

    /**
     * @return CacheManager
     */
    public function getCacheManager(): CacheManager
    {
        return $this->cacheManager;
    }

    /**
     * @return DataManager
     */
    public function getDataManager(): DataManager
    {
        return $this->dataManager;
    }

    /**
     * @param BinaryInterface $binary
     * @param $filter
     * @param array $runtimeConfig
     * @return BinaryInterface
     */
    public function applyFilter(BinaryInterface $binary, $filter, array $runtimeConfig = []): BinaryInterface
    {
        return $this->getFilterManager()->applyFilter($binary, $filter, $runtimeConfig);
    }

    /**
     * @param BinaryInterface $binary
     * @param $path
     * @param $filter
     * @param $resolver
     */
    public function store(BinaryInterface $binary, $path, $filter, $resolver = null)
    {
        $this->getCacheManager()->store($binary, $path, $filter, $resolver);
    }

    /**
     * @param $path
     * @param $filter
     * @param $resolver
     * @return string
     */
    public function resolve($path, $filter, $resolver = null): string
    {
        return $this->getCacheManager()->resolve($path, $filter, $resolver);
    }
}