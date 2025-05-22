<?php

declare(strict_types=1);

namespace App\Command;

use App\Mailer\MailSender;
use App\Mailer\Render\RenderedEmail;
use App\Mailer\Render\TwigEmailRender;
use App\Service\File\Provider\SftpFileService;
use App\Service\FuelCard\FuelCardService;
use Doctrine\ORM\EntityManager;
use Exception;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Finder\Finder;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\Console\Attribute\AsCommand;

#[AsCommand(name: 'app:download:fuel-file')]
class DownloadFuelFileCommand extends Command
{
    protected EntityManager $em;
    protected SftpFileService $sftpService;
    protected FuelCardService $fuelService;
    private MailSender $mailSender;
    private TwigEmailRender $emailRender;

    public const IGNORE_REMOTE_FILES = [
        '.',
        '..',
        'Connect to MQ web Console.pages',
    ];

    private const STATUS = 'status';
    private const STATUS_FAILED = 'FAILED';
    private const STATUS_SUCCESSFUL = 'SUCCESSFUL';

    private const EMAILS_TO = ['dklyuchnikov@scnsoft.com', 'cardsystemsupp@chevron.com'];
    private const EMAILS_TO_DEVELOPER = ['dklyuchnikov@scnsoft.com', 'cardsystemsupp@chevron.com'];
    private const EMAIL_FROM = ['notifications@linxio.com'];

    /**
     * Configures the current command.
     */
    protected function configure(): void
    {
        $this->setDescription('Download and import fuel file');
        $this->addOption('ownerTeamId', null, InputOption::VALUE_OPTIONAL, '', 1234);
    }

    public function __construct(
        EntityManager $em,
        SftpFileService $sftpService,
        FuelCardService $fuelService,
        MailSender $mailSender,
        TwigEmailRender $emailRender
    ) {
        $this->em = $em;
        $this->sftpService = $sftpService;
        $this->fuelService = $fuelService;
        $this->mailSender = $mailSender;
        $this->emailRender = $emailRender;

        parent::__construct();
    }

    public function getIgnoreSet(): array
    {
        static $ignoreSet = null;

        if($ignoreSet === null){
            $ignoreSet = array_flip(self::IGNORE_REMOTE_FILES);
        }

        return $ignoreSet;
    }

    private function shouldIgnoreFile(string $filename): bool
    {
        return isset($this->getIgnoreSet()[$filename]);
    }

    private function fetchRemoteFiles(array $dirRemoteFiles, string $remotePath, string $loadPath, OutputInterface $output): array
    {
        $receivedFiles = [];

        foreach ($dirRemoteFiles as $filename) {
            $output->writeln("Check file to ignore: {$remotePath}{$filename}");

            if ($this->shouldIgnoreFile($filename)) {
                continue;
            }

            $fileRemotePath = $remotePath . $filename;

            if (!$this->sftpService->fileExists($fileRemotePath)) {
                $output->writeln("File not exists: $fileRemotePath");
                continue;
            }

            $loadFile = $loadPath . $filename;
            $file = $this->sftpService->getRemoteFile($fileRemotePath, $loadFile);

            if (!$file) {
                $output->writeln("File not downloaded: $fileRemotePath");
                continue;
            }

            $output->writeln("The file: $filename was successfully uploaded to: $loadFile");

            $receivedFiles[] = [
                'filename' => $filename,
                'fullRemotePath' => $fileRemotePath,
                'localPath' => $loadFile,
            ];
        }

        return $receivedFiles;
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return int|void|null
     * @throws Exception
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        try {
            $remotePath = $this->sftpService->getRemotePath();
            $loadPath = dirname(__DIR__) . $this->sftpService->getLoadPath();

            $dirRemoteFiles = $this->sftpService->getDirectoryList($this->sftpService->getRemotePath());
            if (!$dirRemoteFiles) {
                $output->writeln('Files on sftp not found: ' . $remotePath);
                $this->mailSender->sendEmail(
                    self::EMAILS_TO_DEVELOPER, $this->getFileNotFoundRenderedEmail($remotePath), self::EMAIL_FROM
                );
                return 0;
            }

            $receivedFiles = $this->fetchRemoteFiles($dirRemoteFiles, $remotePath, $loadPath, $output);

            if (empty($receivedFiles)) {
                $output->writeln('No files were received after filtering.');
                return 0;
            }

            $this->sftpService->disconnect();
            $output->writeln('sftp disconnect ' . $remotePath);

            $output->writeln('After successfully receiving all the files, upload them');
            //chevron reseller team ids
            $ownerTeamId = [1234, 2722, 2720, 2721, 2719];
            $filesInfo = $this->uploadRemoteFile($loadPath, $receivedFiles, $output, $ownerTeamId);

            $this->deleteSuccessUploadedFiles($filesInfo, $output);

            if (in_array(self::STATUS_FAILED, array_column($filesInfo, self::STATUS))) {
                $this->mailSender->sendEmail(
                    self::EMAILS_TO, $this->getFileProcessRenderedEmail($filesInfo), self::EMAIL_FROM
                );
            }

            $output->writeln(PHP_EOL . 'Files import completed');
        } catch (Exception $exception) {
            $this->em->flush();
            $output->writeln($exception->getMessage());
            $output->writeln($exception->getTraceAsString());
        } catch (\Throwable $exception) {
            $output->writeln($exception->getMessage());
            $output->writeln($exception->getTraceAsString());
        }
        return 0;
    }

    public function uploadRemoteFile(
        string $loadPath,
        array $remoteFiles,
        OutputInterface $output,
        ?array $ownerTeamId
    ): array {
        $progressBar = new ProgressBar($output, count($remoteFiles));
        $progressBar->start();

        foreach ($remoteFiles as $key => $remoteFile) {
            $filename = $remoteFile['filename'];
            $fullPath = $remoteFile['localPath'];
    
            if (!file_exists($fullPath)) {
                $output->writeln(PHP_EOL . "File not found: $fullPath");
                $remoteFiles[$key][self::STATUS] = self::STATUS_FAILED;
                $progressBar->advance();
                continue;
            }
    
            $output->writeln(PHP_EOL . "File upload started: $filename");
            
            $uploadFile = new UploadedFile(
                $fullPath,
                $filename,
                null,
                UPLOAD_ERR_OK,
                true
            );
            //1234 - teamId of reseller - Chevron
            $result = $this->fuelService->sftpUploadFile($uploadFile, $ownerTeamId);

            if (!empty($result['error'])) {
                $output->writeln(PHP_EOL . "Error uploading file: $filename");
                $remoteFiles[$key][self::STATUS] = self::STATUS_FAILED;
            } else {
                $output->writeln(PHP_EOL . "File upload finished: $filename");
                $output->writeln("Details: " . json_encode($result));
                $remoteFiles[$key][self::STATUS] = self::STATUS_SUCCESSFUL;
            }

            $progressBar->advance();
            $output->writeln(PHP_EOL . 'File upload finished ' . $filename);
            $output->writeln(PHP_EOL . 'details:' . json_encode($result));            
        }
        $progressBar->finish();

        return $remoteFiles;
    }

    public function getFileProcessRenderedEmail($data): RenderedEmail
    {
        $dateTime = (new \DateTime())->format('Y-m-d');

        return $this->emailRender->render(
            'reports/chevron/chevronFuelFileProcess.html.twig',
            [
                'subject' => 'Linxio - Fuel data upload report: ' . $dateTime,
                'data' => $data,
            ]
        );
    }

    public function getFileNotFoundRenderedEmail($remotePath): RenderedEmail
    {
        $dateTime = (new \DateTime())->format('Y-m-d');

        return $this->emailRender->render(
            'reports/chevron/chevronFuelFileNotFound.html.twig',
            [
                'subject' => 'Linxio - Fuel data upload report: ' . $dateTime,
                'remotePath' => $remotePath,
            ]
        );
    }

    /**
     * @param $remoteFiles
     * @param OutputInterface $output
     * @return void
     * @throws Exception
     */
    public function deleteSuccessUploadedFiles($remoteFiles, OutputInterface $output): void
    {
        foreach ($remoteFiles as $remoteFile) {
            if ($remoteFile[self::STATUS] === self::STATUS_SUCCESSFUL) {
                $fileRemotePath = $remoteFile['fullRemotePath'];
                if (!$this->sftpService->deleteRemoteFile($fileRemotePath, false)) {
                    $output->writeln(PHP_EOL . 'Remote file not delete: ' . $fileRemotePath);
                } else {
                    $output->writeln(PHP_EOL . 'File was deleted: ' . $fileRemotePath);
                }

            }
        }

        $this->sftpService->disconnect();
    }

    /**
     * @param $filename
     * @return bool
     */
    public function isValidDateInFileName($filename): bool
    {
        $dateTime = (new \DateTime())->format('Ymd');

        return preg_match('/(\d{4})(\d{1,2})(\d{1,2})\.csv|xlsx$/', $filename, $matches)
            && checkdate(intval($matches[2]), intval($matches[3]), intval($matches[1]))
            && ($matches[1] . $matches[2] . $matches[3] === $dateTime);
    }
}
