<?php

declare(strict_types=1);

namespace Tests\Unit\Backend\Jobs;

use App\Http\Controllers\Common\PhpMailController;
use App\Jobs\NotifyMail;
use Illuminate\Contracts\Queue\ShouldQueue;
use Mockery;
use Mockery\MockInterface;
use Tests\TestCase;

class NotifyMailTest extends TestCase
{
    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    public function test_implements_should_queue(): void
    {
        $this->assertInstanceOf(ShouldQueue::class, new NotifyMail());
    }

    public function test_handle_calls_notify_mailing(): void
    {
        /** @var PhpMailController&MockInterface $mailer */
        $mailer = Mockery::mock(PhpMailController::class);
        $mailer->shouldReceive('NotifyMailing')->once()->andReturn(null);

        (new NotifyMail())->handle($mailer);
        $this->addToAssertionCount(1);
    }

    public function test_handle_accepts_injected_mailer(): void
    {
        /** @var PhpMailController&MockInterface $mailer */
        $mailer = Mockery::mock(PhpMailController::class);
        $mailer->shouldReceive('NotifyMailing')->once()->andReturn(null);

        // Should not throw — constructor-less job, dependency injected via handle()
        (new NotifyMail())->handle($mailer);
        $this->addToAssertionCount(1);
    }
}
