<?php

namespace App\Service\Note;

use App\Entity\Client;
use App\Entity\Device;
use App\Entity\Note;
use App\Entity\Reseller;
use App\Entity\User;
use App\Entity\Vehicle;
use App\Repository\NoteRepository;
use Doctrine\Common\Util\ClassUtils;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Contracts\Translation\TranslatorInterface;

class NoteService
{
    private $translator;
    private $em;

    /**
     * NoteService constructor.
     * @param TranslatorInterface $translator
     * @param EntityManagerInterface $em
     */
    public function __construct(TranslatorInterface $translator, EntityManagerInterface $em)
    {
        $this->translator = $translator;
        $this->em = $em;
    }

    /**
     * @param array $fields
     * @return Note
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     */
    public function create(array $fields): Note
    {
        $note = new Note($fields);
        $this->em->persist($note);
        $this->em->flush();

        return $note;
    }

    public function delete(int $id, User $createdBy): void
    {
        $note = $this->em->getRepository(Note::class)->findOneBy(['id' => $id, 'createdBy' => $createdBy]);
        if ($note) {
            $this->em->remove($note);
            $this->em->flush();
        }
    }

    /**
     * @param $entity
     * @param string $type
     * @return array
     */
    public function list($entity, string $type): array
    {
        $type = $this->prepareNoteType($type);

        if (!in_array($type, Note::ALLOWED_TYPES, true)) {
            throw new NotFoundHttpException($this->translator->trans('notes.type.not_found'));
        }

        /** @var NoteRepository $repo */
        $repo = $this->em->getRepository(Note::class);

        switch (ClassUtils::getClass($entity)) {
            case Client::class:
                return $repo->findBy(['client' => $entity, 'noteType' => $type], ['id' => 'DESC']);
            case Device::class:
                return $repo->findBy(['device' => $entity, 'noteType' => $type], ['id' => 'DESC']);
            case Vehicle::class:
                return $repo->findBy(['vehicle' => $entity, 'noteType' => $type], ['id' => 'DESC']);
            case Reseller::class:
                return $repo->findBy(['reseller' => $entity, 'noteType' => $type], ['id' => 'DESC']);
            default:
                return [];
        }
    }

    /**
     * @param string $type
     * @return string
     */
    public function prepareNoteType(string $type): string
    {
        return strtolower($type);
    }
}