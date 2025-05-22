<?php

namespace App\Service\FuelStation;

use App\Entity\File;
use App\Entity\Team;
use App\Entity\User;
use App\Service\BaseService;
use App\Service\File\Factory\FileReaderFactory;
use App\Service\File\FileService;
use App\Service\FuelCard\Exception\FileImportException;
use App\Service\FuelCard\Mapper\FileMapperManager;
use App\Service\FuelStation\Factory\FileMapperFactory;
use Doctrine\ORM\EntityManager;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use App\Entity\FuelStation;
use Symfony\Contracts\Translation\TranslatorInterface;

class FuelStationService extends BaseService
{
    protected TranslatorInterface $translator;

    public function __construct(
        private readonly ValidatorInterface $validator,
        private readonly EntityManager $em,
        private readonly PaginatorInterface $paginator,
        private readonly FileService $fileService,
        TranslatorInterface $translator
    ) {
        $this->translator = $translator;
    }

    public function create(array $params, User $user): FuelStation
    {
        $fuelStation = new FuelStation($params);
        $fuelStation->setTeam($user->getTeam());

        $this->validate($this->validator, $fuelStation);

        $this->em->persist($fuelStation);
        $this->em->flush();

        return $fuelStation;
    }

    public function edit(FuelStation $fuelStation, array $params, User $user): FuelStation
    {
        $fuelStation->setAttributes($params);
        $this->validate($this->validator, $fuelStation);

        $this->em->flush();

        return $fuelStation;
    }

    public function delete(FuelStation $fuelStation): void
    {
        $this->em->remove($fuelStation);
        $this->em->flush();
    }

    public function getListByTeam(Team $team, array $params)
    {
        $page = $params['page'] ?? 1;
        $limit = $params['limit'] ?? 10;
        $fuelArray = $this->em->getRepository(FuelStation::class)->getListByTeam($team);
        $pagination = $this->paginator->paginate($fuelArray, $page, $limit, $params);
        $data = [];

        foreach ($pagination as $item) {
            $data[] = $item->toArray();
        }

        $pagination->setItems($data);

        return $pagination;
    }

    public function parseFiles(array $data, User $user): array
    {
        $connection = $this->em->getConnection();
        try {
            $connection->beginTransaction();

            if ($data['files']->get('files') ?? null) {
                foreach ($data['files']->get('files') as $file) {
                    $fileEntity = $this->fileService->uploadFuelCardFile($file, $user);
                    $importData = $this->importFile($fileEntity);
                }
            }
            $connection->commit();

            return \array_merge(
                ['file' => $fileEntity?->toArray()],
                ['data' => $importData ?? []]
            );
        } catch (\Exception $e) {
            if ($connection->isTransactionActive()) {
                $connection->rollback();
            }
            throw $e;
        }
    }

    public function importFile(File $file): array
    {
        $reader = FileReaderFactory::getInstance($file);
        $resource = $file->getPath() . $file->getName();
        $spreadsheet = $reader->load($resource)->getActiveSheet()->toArray();

        $fieldsForMapping = FileMapperFactory::getFieldsForMapping($file, $this->translator);
        $fileMapperObj = (new FileMapperManager())->getMapperObj($fieldsForMapping, $spreadsheet);

        if ($fileMapperObj->getHeader()) {
            foreach ($spreadsheet as $line) {
                foreach ($fileMapperObj->getHeader() as $index => $propertyName) {
                    try {
                        $data[$propertyName] = $line[$index];
                    } catch (\Exception $e) {
                        throw new FileImportException(
                            $this->translator->trans('validation.errors.import.fields_not_recognized')
                        );
                    }
                }
            }
        }

        return $data ?? [];
    }

    public function saveImportFile(File $file, User $user): array
    {
        $data = $this->importFile($file);

        foreach ($data as $item) {
            $fuelStation = new FuelStation($item);
            $fuelStation->setTeam($user->getTeam());
            $this->em->persist($fuelStation);
            $this->em->flush();
        }

        return $data;
    }
}