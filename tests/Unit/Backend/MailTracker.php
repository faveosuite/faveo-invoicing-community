<?php

namespace Tests\Unit\Backend;

use Mail;
use Swift_Events_EventListener;
use Symfony\Component\Mime\Email;

/**
 * tracks outgoing mails
 * NOTE: if mails has been sent in a queue and that queue has been mocked then, email assertions will
 * not work.
 */
trait MailTracker
{
    protected $emails = [];

    /** @before */
    public function setUpForMailTracker(): void
    {
        parent::setUp();
        Mail::getSymfonyTransport()
            ->registerPlugin(new TestingMailEventListener($this));
    }

    protected function assertEmailWasSent()
    {
        $this->assertNotEmpty($this->emails, 'No emails were sent');
    }

    protected function assertEmailCount($count)
    {
        $actualCount = count($this->emails);
        $grammerWordForActualCount = $actualCount > 1 ? 'were' : 'was';
        $emailOrEmails = $count > 1 ? 'emails' : 'email';
        $this->assertCount(
            $count,
            $this->emails,
            sprintf('Expected %s %s to have been send, but only %d %s sent', $count, $emailOrEmails, $actualCount, $grammerWordForActualCount)
        );
    }

    public function addEmail(Email $email): void
    {
        $this->emails[] = $email;
    }
}

class TestingMailEventListener implements Swift_Events_EventListener
{
    public function __construct(protected $test)
    {
    }

    public function beforeSendPerformed($event): void
    {
        $message = $event->getMessage();

        $this->test->addEmail($event->getMessage($message));
    }
}
