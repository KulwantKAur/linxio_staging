<?php

namespace App\EventListener\Asset;

use App\Entity\Asset;
use App\Service\Asset\AssetService;
use Doctrine\ORM\Event\LifecycleEventArgs;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

class AssetEntityListener
{
    private $tokenStorage;
    private $assetService;

    /**
     * AssetEntityListener constructor.
     * @param TokenStorageInterface $tokenStorage
     * @param AssetService $assetService
     */
    public function __construct(TokenStorageInterface $tokenStorage, AssetService $assetService)
    {
        $this->tokenStorage = $tokenStorage;
        $this->assetService = $assetService;
    }

    /**
     * @param Asset $asset
     * @param LifecycleEventArgs $args
     * @return Asset
     */
    public function postLoad(Asset $asset, LifecycleEventArgs $args)
    {
        $asset->setAssetService($this->assetService);

        return $asset;
    }

    /**
     * @param Asset $asset
     * @param LifecycleEventArgs $args
     * @return Asset
     */
    public function postPersist(Asset $asset, LifecycleEventArgs $args)
    {
        $asset->setAssetService($this->assetService);

        return $asset;
    }
}