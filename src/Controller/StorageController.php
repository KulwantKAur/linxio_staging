<?php

namespace App\Controller;

use App\Entity\Storage;
use Doctrine\ORM\EntityManager;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/storage')]
class StorageController extends BaseController
{
    private $em;

    public function __construct(EntityManager $entityManager)
    {
        $this->em = $entityManager;
    }

    #[Route('/{key}', methods: ['GET'])]
    public function getData(Request $request, $key)
    {
        $data = $this->getStorageByKey($key);

        return $this->viewItem($data);
    }

    #[Route('/', methods: ['POST'])]
    public function setData(Request $request)
    {
        try {
            $storageEntity = $this->getStorageByKey($request->request->get('key'));

            if ($storageEntity) {
                $storageEntity->setAttributes($request->request->all());
            } else {
                $storageEntity = new Storage($request->request->all());
                $storageEntity->setUser($this->getUser());
                $this->em->persist($storageEntity);
            }

            $this->em->flush();
        } catch (\Exception $ex) {
            return $this->viewException($ex, Response::HTTP_BAD_REQUEST);
        }

        return $this->viewItem($storageEntity);
    }

    /**
     * @param $key
     * @return object|null
     */
    private function getStorageByKey($key)
    {
        return $this->em->getRepository(Storage::class)->findOneBy([
            'user' => $this->getUser(),
            'key' => $key
        ]);
    }
}
