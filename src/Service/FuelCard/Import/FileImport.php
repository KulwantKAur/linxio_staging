<?php

namespace App\Service\FuelCard\Import;

use App\Entity\BaseEntity;
use App\Entity\Client;
use App\Entity\File;
use App\Entity\FuelCard\FuelCard;
use App\Entity\FuelCard\FuelCardTemporary;
use App\Entity\FuelType\FuelIgnoreList;
use App\Entity\FuelType\FuelType;
use App\Entity\Team;
use App\Entity\Vehicle;
use App\Service\BaseService;
use App\Service\FuelCard\Exception\FileImportException;
use App\Service\FuelCard\Factory\FileMapperFactory;
use App\Service\File\Factory\FileReaderFactory;
use App\Service\FuelCard\Mapper\BaseFileMapper;
use App\Service\FuelCard\Mapper\FileMapperManager;
use App\Util\ExceptionHelper;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\NonUniqueResultException;
use Psr\Log\LoggerInterface;
use Symfony\Contracts\Translation\TranslatorInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class FileImport extends BaseService
{
    protected TranslatorInterface $translator;
    private EntityManagerInterface $em;
    private bool $skipLine;
    private array $fuelIgnore = [];
    private ?int $teamId;
    private ?Client $client;
    private ValidatorInterface $validator;
    private LoggerInterface $logger;
    private $fuelCardRepo;

    public function __construct(
        TranslatorInterface $translator,
        EntityManagerInterface $em,
        ValidatorInterface $validator,
        LoggerInterface $logger
    ) {
        $this->translator = $translator;
        $this->em = $em;
        $this->validator = $validator;
        $this->logger = $logger;
        $this->fuelCardRepo = $em->getRepository(FuelCard::class);
    }

    public function import(File $fileEntity, bool $isCheckClientsInFile = false, ?array $ownerTeamId = null)
    {
        try {
            $resource = $fileEntity->getPath() . $fileEntity->getName();
            $this->teamId = $fileEntity->getTeamId();
            $this->client = $this->teamId
                ? $this->em->getRepository(Client::class)->getClientByTeamId($this->teamId)
                : null;
            $reader = FileReaderFactory::getInstance($fileEntity);
            $spreadsheet = $reader->load($resource)->getActiveSheet()->toArray();
            $fieldsForMapping = FileMapperFactory::getFieldsForMapping($fileEntity, $this->translator);
            $fileMapperObj = (new FileMapperManager())->getMapperObj($fieldsForMapping, $spreadsheet);

            if ($isCheckClientsInFile) {
                if ($ownerTeamId) {
                    $chevronResellerTeam = $this->em->getRepository(Team::class)->findBy(['id' => $ownerTeamId]);
                    $clients = $chevronResellerTeam ? $this->em->getRepository(Client::class)->getListClients($chevronResellerTeam) : [];
                } else {
                    $clients = $this->em->getRepository(Client::class)->getListClients();
                }
            }

            if ($fileMapperObj->getHeader()) {
                foreach ($spreadsheet as $line) {
                    $this->skipLine = false;

                    /** @var array $dataImport */
                    $dataImport = $this->prepareRow($fileMapperObj, $line);

                    if (!$this->skipLine) {
                        if ($isCheckClientsInFile) {
                            if ($this->checkClientsExist($clients, $dataImport)) {
                                $this->teamId = null;
                                $foundClients = $this->em->getRepository(Client::class)
                                    ->getClientsByName($dataImport['CustomerName']);

                                foreach ($foundClients as $client) {
                                    $this->teamId = $client['teamId'];
                                    $clientEntity = $this->em->getRepository(Client::class)->find($client['id']);
                                    $dataImport = $this->prepareFields($dataImport, $clientEntity);
                                    $validate = $this->validateFields($dataImport, $clientEntity);

                                    $this->save($fileEntity, $validate, $dataImport, $client['teamId']);
                                }
                            }
                        } else {
                            $dataImport = $this->prepareFields($dataImport, $this->client);
                            $validate = $this->validateFields($dataImport, $this->client);

                            $this->save($fileEntity, $validate, $dataImport, $fileEntity->getTeamId());
                        }
                    }
                }
            } else {
//                throw new FileImportException('No header');
            }
        } catch (\Exception $e) {
            $this->logger->error(ExceptionHelper::convertToJson($e));

            throw new FileImportException(
                $this->translator->trans('validation.errors.import.error_parsing_file')
            );
        }
    }

    public function importChevron(
        File $fileEntity,
        bool $isCheckClientsInFile = false,
        ?array $ownerTeamId = null
    ) {
        try {
            $filePrefix = '0' . substr($fileEntity->getDisplayName(), 0, 2);
            $resource = $fileEntity->getPath() . $fileEntity->getName();
            $this->teamId = $fileEntity->getTeamId();
            $this->client = $this->teamId ? $this->em->getRepository(Client::class)->getClientByTeamId($this->teamId) : null;
    
            $handle = fopen($resource, 'r');
            if (!$handle) {
                throw new \RuntimeException("Unable to open file.");
            }
            
            $headers = fgetcsv($handle);
            if (!$headers) {
                throw new FileImportException('No header');
            }
            
            $fakeSheet = [$headers];
            $fieldsForMapping = FileMapperFactory::getFieldsForMapping($fileEntity, $this->translator);
            $fileMapperObj = (new FileMapperManager())->getMapperObj($fieldsForMapping, $fakeSheet);
            
            $clients = $this->initializeClientsForChecking($isCheckClientsInFile, $ownerTeamId);

            $rowCount = 0;
            $skippedRows = 0;
            $importedRows = 0;
            
            while (($line = fgetcsv($handle)) !== false) {
                $rowCount++;
                $this->skipLine = false;
            
                $dataImport = $this->prepareRow($fileMapperObj, $line);
            
                if ($this->skipLine) {
                    $skippedRows++;
                    continue;
                }
            
                if ($isCheckClientsInFile) {
                    if (!$this->checkChevronClientsExist($clients, $dataImport, $filePrefix)) {
                        continue;
                    }
            
                    $this->handleExistingClients($dataImport, $filePrefix, $fileEntity);
                } else {
                    $this->handleNewClientImport($dataImport, $fileEntity);
                }
                $importedRows++;
            }
            fclose($handle);
            $this->logger->info("File import completed", [
                'rows_total' => $rowCount,
                'rows_imported' => $importedRows,
                'rows_skipped' => $skippedRows,
            ]);
        } catch (\Exception $e) {
            $this->logger->error(ExceptionHelper::convertToJson($e));
            throw new FileImportException($this->translator->trans('validation.errors.import.error_parsing_file'));
        }
    }
    
    // Helper function for client initialization
    private function initializeClientsForChecking(bool $isCheckClientsInFile, ?array $ownerTeamId): array
    {
        if ($isCheckClientsInFile) {
            if ($ownerTeamId) {
                $chevronResellerTeam = $this->em->getRepository(Team::class)->findBy(['id' => $ownerTeamId]);
                return $chevronResellerTeam ? $this->em->getRepository(Client::class)->getListClients($chevronResellerTeam) : [];
            } else {
                return $this->em->getRepository(Client::class)->getListClients();
            }
        }
        return [];
    }
    
    // Handle the logic for existing clients
    private function handleExistingClients(array $dataImport, string $filePrefix, File $fileEntity): void
    {
        $this->teamId = null;
        $foundClients = $this->em->getRepository(Client::class)
            ->getClientsByChevronAccountId($filePrefix . $dataImport['cardAccountNumber']);
    
        foreach ($foundClients as $client) {
            $this->teamId = $client['teamId'];
            $clientEntity = $this->em->getRepository(Client::class)->find($client['id']);
            $dataImport = $this->prepareFields($dataImport, $clientEntity);
            $validate = $this->validateFields($dataImport, $clientEntity);
    
            $this->save($fileEntity, $validate, $dataImport, $this->teamId);
        }
    }
    
    // Handle import for new clients
    private function handleNewClientImport(array $dataImport, File $fileEntity): void
    {
        $dataImport = $this->prepareFields($dataImport, $this->client);
        $validate = $this->validateFields($dataImport, $this->client);
        
        $this->save($fileEntity, $validate, $dataImport, $fileEntity->getTeamId());
    }
    

    /**
     * @param File $fileEntity
     * @param array $validate
     * @param array $dataImport
     * @param int|null $teamId
     * @return void
     * @throws FileImportException
     */
    public function save(File $fileEntity, array $validate, array $dataImport, ?int $teamId)
    {
            try {
                $fuelCardTemporary = new FuelCardTemporary(
                    \array_merge(
                        $dataImport,
                        ['comments' => $validate ?? null]
                    )
                );
                $fuelCardRecord = new FuelCard(
                    \array_merge(
                        $dataImport,
                        [
                            'file' => $fileEntity,
                            'fuelCardTemporary' => $fuelCardTemporary,
                            'teamId' => $teamId ?? null,
                        ]
                    )
                );
    
            // Persist and flush
            $this->em->persist($fuelCardRecord);
            $this->em->persist($fuelCardTemporary);
            $this->em->flush();
        } catch (\Exception $e) {
            $this->logger->error(ExceptionHelper::convertToJson($e));
            throw new FileImportException(
                $this->translator->trans('validation.errors.import.error_parsing_file')
            );
        }
    }

    /**
     * @param BaseFileMapper $fileMapperObj
     * @param $row
     * @return array|bool
     * @throws FileImportException
     * @throws NonUniqueResultException
     */
    public function prepareRow(BaseFileMapper $fileMapperObj, $row): bool|array
    {
        $data = [];
        foreach ($fileMapperObj->getHeader() as $index => $propertyName) {
            try {
                $data[$propertyName] = $row[$index];
            } catch (\Exception $e) {
                throw new FileImportException(
                    $this->translator->trans('validation.errors.import.fields_not_recognized')
                );
            }
        }

        $specialData = $fileMapperObj->specialPrepareFields($data);
        $specialData['isShowTime'] = $fileMapperObj->isShowTime();

        if (!empty($specialData[BaseFileMapper::FIELD_REFUELED_FUEL_TYPE])) {
            $fuelTypeIgnored = $this->getFuelTypeIgnoreValueFromList(
                $this->getFuelIgnoreList(),
                $specialData[BaseFileMapper::FIELD_REFUELED_FUEL_TYPE]
            );
        }

        if ($fileMapperObj->checkSkipLine($specialData) || !empty($fuelTypeIgnored)) {
            return $this->skipLine = true;
        }

        return $specialData;
    }

    public function prepareFields(array $data, ?Client $client = null)
    {
        if (isset($data[BaseFileMapper::FIELD_TRANSACTION_DATE])
            && (is_numeric(strtotime($data[BaseFileMapper::FIELD_TRANSACTION_DATE])))) {
            $transactionDate = isset($data[BaseFileMapper::FIELD_TRANSACTION_TIME])
                ? $data[BaseFileMapper::FIELD_TRANSACTION_DATE] . " " . $data[BaseFileMapper::FIELD_TRANSACTION_TIME]
                : $data[BaseFileMapper::FIELD_TRANSACTION_DATE];
            $data[BaseFileMapper::FIELD_TRANSACTION_DATE] =
                self::parseDateToUTC($transactionDate, $client?->getTimeZoneName());
        }

        if ((isset($data[BaseFileMapper::FIELD_VEHICLE]) || $client?->isChevron()) && !is_null($this->teamId)) {
            $data['vehicleOriginal'] = $data[BaseFileMapper::FIELD_VEHICLE];
            $data[BaseFileMapper::FIELD_VEHICLE] = $this->findByVehicle(
                $data[BaseFileMapper::FIELD_VEHICLE],
                $this->teamId
            );
        }

        if (!empty($data[BaseFileMapper::FIELD_REFUELED_FUEL_TYPE])) {
            $data['refueledFuelTypeOriginal'] = $data[BaseFileMapper::FIELD_REFUELED_FUEL_TYPE];
            $data[BaseFileMapper::FIELD_REFUELED_FUEL_TYPE] = $this->em
                ->getRepository(FuelType::class)
                ->getFuelTypeMapping($data[BaseFileMapper::FIELD_REFUELED_FUEL_TYPE]);
        }

        return $data;
    }

    public function validateFields(array $fields, Client $client): array
    {
        $errors = [];
        $nowTs = (new \DateTime())->getTimestamp();

        /** @var Vehicle $vehicle */
        $vehicle = $fields[BaseFileMapper::FIELD_VEHICLE] ?? null;

        /** @var FuelType $refueledFuelType */
        $refueledFuelType = $fields[BaseFileMapper::FIELD_REFUELED_FUEL_TYPE] ?? null;

        if (!($refueledFuelType ?? null)) {
            $errors[BaseEntity::VALIDATION_TYPE_WARNING][] = $this->translator->trans(
                'validation.warning.import.unknown_fuel_type'
            );
        }

        if (!empty($this->fuelCardRepo->checkDuplicate($fields))) {
            $errors[BaseEntity::VALIDATION_TYPE_WARNING][] = $this->translator->trans(
                'validation.warning.import.duplicate_record'
            );
        }

        if ((!$vehicle && !$client->isChevron()) || (!$vehicle && $client->isChevron() && $fields['vehicleOriginal'])
            || (!is_null($this->teamId) && is_object($vehicle) && ($vehicle->getTeam()->getId() != $this->teamId))
        ) {
            $errors[BaseEntity::VALIDATION_TYPE_WARNING][] = $this->translator->trans(
                'validation.errors.import.unknown_vehicle'
            );
        }

        if (is_object($vehicle) && $refueledFuelType) {
            $vehicleFuelType = $vehicle->getFuelType()?->getId();
            if ($vehicleFuelType && ($vehicleFuelType != $refueledFuelType->getId())) {
                $errors[BaseEntity::VALIDATION_TYPE_ERROR][] = $this->translator->trans(
                    'validation.errors.import.fuel_type_mismatch'
                );
            }
        }

        if (is_object($vehicle) && $fields[BaseFileMapper::FIELD_REFUELED]) {
            $vehicleTankCapacity = (!empty($vehicle->getFuelTankCapacity()) || $vehicle->getFuelTankCapacity() == 0)
                ? $vehicle->getFuelTankCapacity()
                : null;

            if ($vehicleTankCapacity && ($fields['refueled'] > $vehicleTankCapacity)) {
                $errors[BaseEntity::VALIDATION_TYPE_ERROR][] = $this->translator->trans(
                    'validation.errors.import.capacity_mismatch'
                );
            }
        }

        if (!empty($fields[BaseFileMapper::FIELD_TRANSACTION_DATE])
            && $nowTs < $fields[BaseFileMapper::FIELD_TRANSACTION_DATE]->getTimestamp()) {
            $errors[BaseEntity::VALIDATION_TYPE_ERROR][] = $this->translator->trans(
                'validation.errors.import.date_in_future'
            );
        }

        return $errors;
    }

    /**
     * @param $vehicle
     * @param $teamId
     * @return Vehicle|object|null
     */
    public function findByVehicle($vehicle, $teamId)
    {
        return $this->em->getRepository(Vehicle::class)->findOneBy(['regNo' => $vehicle, 'team' => $teamId]);
    }

    /**
     * @param array $fuelTypeIgnored
     * @param string $name
     * @return array|string|null
     */
    public function getFuelTypeIgnoreValueFromList(array $fuelTypeIgnored, string $name)
    {
        $key = strtolower(array_search($name, array_column($fuelTypeIgnored, 'name')));

        return ($key !== false) && isset($fuelTypeIgnored[$key]['name']) ? $fuelTypeIgnored[$key]['name'] : null;
    }

    /**
     * @return array
     */
    public function getFuelIgnoreList(): array
    {
        return !empty($this->fuelIgnore)
            ? $this->fuelIgnore
            : $this->fuelIgnore = array_map(
                static function (FuelIgnoreList $t) {
                    return $t->toArray(['name']);
                },
                $this->em->getRepository(FuelIgnoreList::class)->findAll()
            );
    }

    /**
     * @param array $clients
     * @param array $dataImport
     * @return bool
     */
    public function checkClientsExist(array $clients, array $dataImport): bool
    {
        $customerName = $dataImport['CustomerName'] ?? null;
        $key = strtolower(array_search($customerName, array_column($clients, 'name')));

        return (($key !== "") && $clients[$key]['name']) ?? false;
    }

    public function checkChevronClientsExist(array $clients, array $dataImport, $filePrefix): bool
    {
        $customerCardAccountNumber = $dataImport[BaseFileMapper::FIELD_CARD_ACCOUNT_NO] ?? null;
        $key = strtolower(array_search($filePrefix . $customerCardAccountNumber,
            array_column($clients, 'chevronAccountId')));

        return (($key !== "") && $clients[$key]['chevronAccountId']) ?? false;
    }
}
