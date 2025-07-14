<?php

namespace Tests\Unit;

/**
 * tracks outgoing mails
 * NOTE: if mails has been sent in a queue and that queue has been mocked then, email assertions will
 * not work.
 */
trait MailTracker
{
    protected $emails = [];

    /** @before */
    public function setUpForMailTracker()
    {
        parent::setUp();
        \Mail::getSymfonyTransport()
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
            "Expected $count $emailOrEmails to have been send, but only $actualCount $grammerWordForActualCount sent"
        );
    }

    public function addEmail(\Symfony\Component\Mime\Email $email)
    {
        $this->emails[] = $email;
    }
}
class TestingMailEventListener implements \Swift_Events_EventListener
{
    protected $test;

    public function __construct($testClass)
    {
        $this->test = $testClass;
    }

    public function beforeSendPerformed($event)
    {
        $message = $event->getMessage();

        $this->test->addEmail($event->getMessage($message));
    }
}
