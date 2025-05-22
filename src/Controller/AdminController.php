<?php

namespace App\Controller;

use App\Entity\AdminTeamInfo;
use App\Entity\Permission;
use App\Entity\PlatformSetting;
use App\Entity\Team;
use App\Service\Admin\AdminTeamService;
use App\Service\PlatformSetting\PlatformSettingService;
use Doctrine\ORM\EntityManager;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

class AdminController extends BaseController
{
    private $platformSettingService;

    /**
     * AdminController constructor.
     * @param PlatformSettingService $platformSettingService
     */
    public function __construct(PlatformSettingService $platformSettingService)
    {
        $this->platformSettingService = $platformSettingService;
    }

    #[Route('/admin/platform-settings', methods: ['GET'])]
    public function getPlatformSettings(Request $request): JsonResponse
    {
        try {
            $platformSetting = $this->getUser()->getTeam()->getPlatformSettingByTeam();
        } catch (\Exception $ex) {
            return $this->viewException($ex, Response::HTTP_BAD_REQUEST);
        }

        return $this->viewItem($platformSetting, PlatformSetting::DEFAULT_DISPLAY_VALUES);
    }

    #[Route('/admin/platform-settings', methods: ['POST'])]
    public function setPlatformSettings(Request $request): JsonResponse
    {
        try {
            $platformSetting = null;
            $this->denyAccessUnlessGranted(Permission::PLATFORM_SETTING_ADMIN_EDIT, $this->getUser());
            $this->denyAccessUnlessGranted(null, $this->getUser()->getTeam());

            $data = $request->request->all();
            $data['logo'] = $request->files->get('logo') ?? $request->request->get('logo');
            $data['favicon'] = $request->files->get('favicon') ?? $request->request->get('favicon');

            $platformSetting = $this->platformSettingService
                ->setByTeam($data, $this->getUser(), $this->getUser()->getTeam());
        } catch (\Exception $ex) {
            return $this->viewException($ex, Response::HTTP_BAD_REQUEST);
        }

        return $this->viewItem($platformSetting, PlatformSetting::DEFAULT_DISPLAY_VALUES);
    }

    #[Route('/admin/info', methods: ['GET'])]
    public function getAdminInfo(Request $request, EntityManager $em): JsonResponse
    {
        try {
            $adminTeam = $em->getRepository(Team::class)->findOneBy(['type' => Team::TEAM_ADMIN]);
            $adminInfo = $em->getRepository(AdminTeamInfo::class)->findOneBy(['team' => $adminTeam]);
        } catch (\Exception $ex) {
            return $this->viewException($ex, Response::HTTP_BAD_REQUEST);
        }

        return $this->viewItem($adminInfo);
    }

    #[Route('/admin/info', methods: ['POST'])]
    public function setAdminInfo(Request $request, AdminTeamService $adminTeamService, EntityManager $em): JsonResponse
    {
        try {
            if (!$this->getUser()->isControlAdmin()) {
                throw new AccessDeniedException();
            }
            $data = $request->request->all();
            /** @var AdminTeamInfo $adminInfo */
            $adminInfo = $em->getRepository(AdminTeamInfo::class)->findOneBy(['team' => $this->getUser()->getTeam()]);
            if ($adminInfo) {
                $adminInfo->setAttributes($data);
                $em->flush();
            } else {
                $adminInfo = $adminTeamService->createAdminTeamInfo($data, $this->getUser()->getTeam());
            }

        } catch (\Exception $ex) {
            return $this->viewException($ex, Response::HTTP_BAD_REQUEST);
        }

        return $this->viewItem($adminInfo);
    }
}