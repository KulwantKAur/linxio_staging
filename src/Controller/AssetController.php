<?php

namespace App\Controller;

use App\Entity\Asset;
use App\Entity\Permission;
use App\Service\Asset\AssetService;
use App\Service\Sensor\SensorService;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Contracts\Translation\TranslatorInterface;

class AssetController extends BaseController
{
    private AssetService $assetService;
    private TranslatorInterface $translator;
    private SensorService $sensorService;

    public function __construct(AssetService $assetService, TranslatorInterface $translator, SensorService $sensorService)
    {
        $this->assetService = $assetService;
        $this->translator = $translator;
        $this->sensorService = $sensorService;
    }

    #[Route('/assets', methods: ['POST'])]
    public function create(Request $request): JsonResponse
    {
        $this->denyAccessUnlessGranted(Permission::ASSET_NEW, Asset::class);

        try {
            $area = $this->assetService->create($request->request->all(), $this->getUser());
        } catch (\Exception $ex) {
            return $this->viewException($ex, Response::HTTP_BAD_REQUEST);
        }

        return $this->viewItem($area, Asset::DEFAULT_DISPLAY_VALUES);
    }

    #[Route('/assets/{id}', requirements: ['id' => '\d+'], methods: ['GET'])]
    public function getAssetById(Request $request, $id): JsonResponse
    {
        $this->denyAccessUnlessGranted(Permission::ASSET_LIST, Asset::class);

        try {
            $asset = $this->assetService->getById($id, $this->getUser());
        } catch (\Throwable $ex) {
            return $this->viewException($ex, Response::HTTP_BAD_REQUEST);
        }

        $include = array_merge(Asset::DEFAULT_DISPLAY_VALUES, $request->query->get('fields') ?? []);

        return $this->viewItem($asset, $include);
    }

    #[Route('/assets', methods: ['GET'])]
    public function assetList(Request $request): JsonResponse
    {
//        $this->denyAccessUnlessGranted(Permission::ASSET_LIST, Asset::class);

        try {
            $assets = $this->assetService->assetList($request->query->all(), $this->getUser());
        } catch (\Throwable $ex) {
            return $this->viewException($ex, Response::HTTP_BAD_REQUEST);
        }

        return $this->viewItem($assets);
    }

    #[Route('/assets/{id}', requirements: ['id' => '\d+'], methods: ['PATCH'])]
    public function edit(Request $request, $id): JsonResponse
    {
        $this->denyAccessUnlessGranted(Permission::ASSET_EDIT, Asset::class);

        try {
            $asset = $this->assetService->getById($id, $this->getUser());
            if ($asset) {
                $asset = $this->assetService->edit($request->request->all(), $this->getUser(), $asset);
            }
        } catch (\Throwable $ex) {
            return $this->viewException($ex, Response::HTTP_BAD_REQUEST);
        }

        return $this->viewItem($asset, Asset::DEFAULT_DISPLAY_VALUES);
    }

    #[Route('/assets/{id}/sensors/{sensorId}/pair', requirements: ['id' => '\d+', 'sensorId' => '\d+'], methods: ['POST'])]
    public function pairWithSensor(Request $request, $id, $sensorId): JsonResponse
    {
        $this->denyAccessUnlessGranted(Permission::ASSET_INSTALL_UNINSTALL, Asset::class);

        try {
            $asset = $this->assetService->getById($id, $this->getUser());

            if (!$asset) {
                throw new NotFoundHttpException(
                    $this->translator->trans(
                        'entities.asset.id_does_not_exist',
                        [
                            '%id%' => $id
                        ]
                    )
                );
            }

            $sensor = $this->sensorService->getSensorById($sensorId);

            if (!$sensor) {
                throw new NotFoundHttpException(
                    $this->translator->trans(
                        'entities.sensor.id_does_not_exist',
                        [
                            '%id%' => $id
                        ]
                    )
                );
            }

            // @todo change permission
            $this->denyAccessUnlessGranted(Permission::ASSET_EDIT, $asset);
            $asset = $this->assetService->pairWithSensor(
                $asset,
                $sensor,
                $this->getUser()
            );
        } catch (\Throwable $ex) {
            return $this->viewException($ex, Response::HTTP_BAD_REQUEST);
        }

        return $this->viewItem($asset, Asset::DEFAULT_DISPLAY_VALUES);
    }

    #[Route('/assets/{id}/sensors/{sensorId}/unpair', requirements: ['id' => '\d+', 'sensorId' => '\d+'], methods: ['POST'])]
    public function unpairWithSensor(Request $request, $id, $sensorId): JsonResponse
    {
        $this->denyAccessUnlessGranted(Permission::ASSET_INSTALL_UNINSTALL, Asset::class);

        try {
            $asset = $this->assetService->getById($id, $this->getUser());

            if (!$asset) {
                throw new NotFoundHttpException(
                    $this->translator->trans(
                        'entities.asset.id_does_not_exist',
                        [
                            '%id%' => $id
                        ]
                    )
                );
            }

            $sensor = $this->sensorService->getSensorById($sensorId);

            if (!$sensor) {
                throw new NotFoundHttpException(
                    $this->translator->trans(
                        'entities.sensor.id_does_not_exist',
                        [
                            '%id%' => $sensorId
                        ]
                    )
                );
            }

            if ($asset->getSensor() != $sensor) {
                throw new AccessDeniedHttpException($this->translator->trans('entities.asset.sensor_is_wrong'));
            }

            // @todo change permission
            $this->denyAccessUnlessGranted(Permission::ASSET_EDIT, $asset);
            $asset = $this->assetService->unpairWithSensor(
                $asset,
                $sensor,
                $this->getUser()
            );
        } catch (\Throwable $ex) {
            return $this->viewException($ex, Response::HTTP_BAD_REQUEST);
        }

        return $this->viewItem($asset, Asset::DEFAULT_DISPLAY_VALUES);
    }

    #[Route('/assets/{id}', requirements: ['id' => '\d+'], methods: ['DELETE'])]
    public function delete(Request $request, $id): JsonResponse
    {
        $this->denyAccessUnlessGranted(Permission::ASSET_DELETE, Asset::class);

        try {
            $asset = $this->assetService->getById($id, $this->getUser());
            if ($asset) {
                $this->assetService->remove($asset, $this->getUser());
            }
        } catch (\Throwable $ex) {
            return $this->viewException($ex, Response::HTTP_BAD_REQUEST);
        }

        return $this->viewItem('', [], Response::HTTP_NO_CONTENT);
    }

    #[Route('/assets/{id}/restore', requirements: ['id' => '\d+'], methods: ['PATCH'])]
    public function restore(Request $request, $id): JsonResponse
    {
        $this->denyAccessUnlessGranted(Permission::ASSET_EDIT, Asset::class);
        try {
            $asset = $this->assetService->getById($id, $this->getUser());
            if ($asset) {
                $this->assetService->restore($asset, $this->getUser());
            }
        } catch (\Throwable $ex) {
            return $this->viewException($ex, Response::HTTP_BAD_REQUEST);
        }

        return $this->viewItem($asset, Asset::DEFAULT_DISPLAY_VALUES);
    }
}
