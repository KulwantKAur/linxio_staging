<?php

namespace App\Tests\Behat\Context;


use App\Mailer\MailSender;
use App\Mailer\Render\RenderedEmail;
use Symfony\Component\HttpFoundation\FileBag;

class MailContext extends UsersContext
{
    protected $emailData;

    /**
     * @When I want create sendEmail mock
     */
    public function createEmailMock()
    {
        $emailData = &$this->emailData;
        $mailSenderMock = \Mockery::mock(MailSender::class);
        $mailSenderMock->shouldReceive('sendEmail')
            ->andReturnUsing(
                function ($to, RenderedEmail $message, array $files) use (&$emailData) {
                    $emailData['to'] = $to[0];
                    $emailData['subject'] = $message->subject();
                    $emailData['body'] = $message->body();
                    $emailData['files'] = $files;

                }
            );

        $this->getKernel()->getContainer()->set(MailSender::class, $mailSenderMock);
    }

    /**
     * @When I want check email data
     */
    public function iWantCheckEmailData()
    {
        $this->setResponse(json_encode(['emailData' => $this->emailData]));
    }

    /**
     * @When I want check email file with index :index
     */
    public function iWantCheckFileExist($index)
    {
        try {
            if (!$this->emailData['files'][$index]) {
                throw new \Exception();
            }
        } catch (\Exception $e) {
            throw new \Exception('File doesn\'t exist');
        }
    }
}