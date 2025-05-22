<?php

namespace App\Controller;

use App\Entity\Permission;
use App\Entity\Setting;
use App\Entity\Team;
use App\Entity\User;
use App\Service\Setting\SettingService;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\EntityManager;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Contracts\Translation\TranslatorInterface;

class SettingController extends BaseController
{
    public function __construct(
        private readonly SettingService $settingService,
        private readonly TranslatorInterface $translator,
        private readonly EntityManager $em
    ) {
    }

    #[Route('/settings/team/{teamId}', requirements: ['teamId' => '\d+'], methods: ['GET'])]
    public function getTeamSettings($teamId): JsonResponse
    {
        return $this->viewItemsArray($this->settingService->getTeamSettings($teamId));
    }

    #[Route('/settings/{key}', methods: ['GET'])]
    public function getSettingByKey($key): JsonResponse
    {
        return $this->viewItem(
            $this->settingService->getSettingByKey($key, $this->getUser())
        );
    }

    #[Route('/settings', methods: ['GET'])]
    public function getSettingByKeyArray(Request $request): JsonResponse
    {
        $keys = $request->query->all('keys') ?? [];

        $settings = $this->settingService->getSettingByKey($keys, $this->getUser());

        //TODO refactoring
        if (in_array('currency', $keys)) {
            $currency = $this->getUser()->getTeam()->getPlatformSettingByTeam()?->getCurrency();
            if ($currency) {
                $currencyItem = ['name' => 'currency', 'value' => $currency->getCode()];
                if ($settings instanceof ArrayCollection) {
                    $settings->add($currencyItem);
                }
            }
        }

        return $this->viewItemsArray($settings);
    }

    #[Route('/settings/team/{teamId}', requirements: ['teamId' => '\d+'], methods: ['PATCH'])]
    public function setTeamSettings(Request $request, $teamId): JsonResponse
    {
        $settings = [];
        try {
            /** @var Team $team */
            $team = $this->em->getRepository(Team::class)->find($teamId);
            if ($team) {
                $this->denyAccessUnlessGranted(null, $team);
//                $this->denyAccessUnlessGranted(Permission::SETTING_SET, Setting::class);
                if ($this->getUser()->isInClientTeam() && !($this->getUser()->isAdminClient() || $this->getUser()->isManagerClient())) {
                    throw new AccessDeniedException();
                }
                $settings = $this->settingService->setTeamSettings($teamId, $request->request->all(), $this->getUser());
            }
        } catch (\Exception $ex) {
            return $this->viewException($ex, Response::HTTP_BAD_REQUEST);
        }
        return $this->viewItemsArray($settings);
    }

    #[Route('/settings/user/{userId}', requirements: ['userId' => '\d+'], methods: ['PATCH'])]
    public function setUserSetting(Request $request, $userId): JsonResponse
    {
        $currentUser = $this->getUser();
        try {
            $user = $this->em->getRepository(User::class)->find($userId);
            if (!$user) {
                throw new NotFoundHttpException($this->translator->trans('auth.user.not_found'));
            }

            if ($currentUser->isDriverClient() && $currentUser->getId() !== $user->getId()) {
                throw new AccessDeniedHttpException();
            }
            $this->denyAccessUnlessGranted(null, $user->getTeam());
//            $this->denyAccessUnlessGranted(Permission::SETTING_SET, Setting::class);

            $this->settingService->setUserSetting($user, $request->request->all());
            $this->em->refresh($user);
        } catch (\Exception $ex) {
            return $this->viewException($ex, Response::HTTP_BAD_REQUEST);
        }
        return $this->viewItem($user);
    }

    #[Route('/settings/user/{userId}/{key}', requirements: ['userId' => '\d+'], methods: ['GET'])]
    public function getUserSettingByKey(Request $request, $userId, $key): JsonResponse
    {
        $currentUser = $this->getUser();
        $setting = null;
        try {
            $user = $this->em->getRepository(User::class)->find($userId);
            if (!$user) {
                throw new NotFoundHttpException($this->translator->trans('auth.user.not_found'));
            }

            if ($currentUser->isDriverClient() && $currentUser->getId() !== $user->getId()) {
                throw new AccessDeniedHttpException();
            }
            $this->denyAccessUnlessGranted(null, $user->getTeam());

            $setting = $this->settingService->getUserSettingByKey($user, $key);
        } catch (\Exception $ex) {
            return $this->viewException($ex, Response::HTTP_BAD_REQUEST);
        }
        return $this->viewItem($setting);
    }

    #[Route('/settings/language/list', methods: ['GET'])]
    public function getLanguageList(Request $request): JsonResponse
    {
        return $this->viewItem(Setting::LANGUAGE_LIST);
    }
}