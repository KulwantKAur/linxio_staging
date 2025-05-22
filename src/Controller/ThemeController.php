<?php

namespace App\Controller;

use App\Entity\Setting;
use App\Entity\Theme;
use App\Entity\User;
use App\Service\Theme\ThemeService;
use Doctrine\ORM\EntityManager;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorage;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

/**
 * Class ThemeController
 * @package App\Controller
 */
class ThemeController extends BaseController
{
    private $service;
    private $tokenStorage;

    public function __construct(ThemeService $service, TokenStorageInterface $tokenStorage)
    {
        $this->service = $service;
        $this->tokenStorage = $tokenStorage;
    }

    #[Route('/themes', methods: ['GET'])]
    public function getThemesList(Request $request): JsonResponse
    {
        return $this->viewItem(
            $this->service->getThemes($this->getUser(), (array)$request->query->all('fields'))
        );
    }

    #[Route('/themes/my', methods: ['GET'])]
    public function getThemesMy(EntityManager $em): JsonResponse
    {
        /** @var User $user */
        $user = $this->tokenStorage->getToken()->getUser();

        /** @var Setting|null $setting */
        $themeSetting = $user->getSettingByName(Setting::THEME_SETTING);

        $themeRepo = $em->getRepository(Theme::class);

        return $this->viewItem(
            !$themeSetting
                ? $themeRepo->findOneBy(['alias' => Theme::DEFAULT_THEME_ALIAS])
                : $themeRepo->find($themeSetting->getValue())
        );
    }
}