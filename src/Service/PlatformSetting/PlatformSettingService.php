<?php

namespace App\Service\PlatformSetting;

use App\Entity\PlatformSetting;
use App\Entity\Team;
use App\Entity\User;
use App\Service\BaseService;
use App\Service\File\LocalFileService;
use Doctrine\ORM\EntityManager;
use Symfony\Contracts\Translation\TranslatorInterface;

class PlatformSettingService extends BaseService
{
    use PlatformSettingServiceFieldsTrait;

    protected $translator;
    private $em;
    private $fileService;

    /**
     * @param TranslatorInterface $translator
     * @param EntityManager $em
     * @param LocalFileService $fileService
     */
    public function __construct(TranslatorInterface $translator, EntityManager $em, LocalFileService $fileService)
    {
        $this->translator = $translator;
        $this->em = $em;
        $this->fileService = $fileService;
    }

    public function create(array $data, User $currentUser, Team $team): PlatformSetting
    {
        $data = $this->prepareCreateData($data);

        $platformSetting = new PlatformSetting($data);
        $platformSetting->setCreatedBy($currentUser);
        $platformSetting->setTeam($team);

        $this->em->persist($platformSetting);
        $this->em->flush();

        return $platformSetting;
    }

    public function edit(PlatformSetting $platformSetting, array $data, User $currentUser): PlatformSetting
    {
        $data = $this->prepareCreateData($data, $platformSetting);

        $platformSetting->setAttributes($data);
        $platformSetting->setUpdatedBy($currentUser);
        $platformSetting->setUpdatedAt(new \DateTime());
        $this->em->flush();

        return $platformSetting;
    }

    public function setByTeam(array $data, User $currentUser, Team $team)
    {
        if (!$team->getPlatformSetting()) {
            return $this->create($data, $currentUser, $team);
        } else {
            return $this->edit($team->getPlatformSetting(), $data, $currentUser);
        }
    }

    public function getByDomain(?string $domain): ?PlatformSetting
    {
        return $this->em->getRepository(PlatformSetting::class)->getByDomain($domain);
    }
}
