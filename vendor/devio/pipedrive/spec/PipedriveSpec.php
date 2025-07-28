<?php

namespace spec\Devio\Pipedrive;

use Devio\Pipedrive\Resources\EmailMessages;
use Devio\Pipedrive\Resources\Organizations;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

class PipedriveSpec extends ObjectBehavior
{
    public function it_is_initializable()
    {
        $this->shouldHaveType('Devio\Pipedrive\Pipedrive');
    }

    public function it_can_resolve_a_resource_object()
    {
        $this->make('organizations')->shouldBeAnInstanceOf(Organizations::class);
        $this->make('Organizations')->shouldBeAnInstanceOf(Organizations::class);
        $this->make('emailMessages')->shouldBeAnInstanceOf(EmailMessages::class);
        $this->make('email_messages')->shouldBeAnInstanceOf(EmailMessages::class);
    }

    public function it_can_resolve_a_resource_object_from_magic_method()
    {
        $this->organizations->shouldBeAnInstanceOf(Organizations::class);
        $this->organizations()->shouldBeAnInstanceOf(Organizations::class);
        $this->emailMessages->shouldBeAnInstanceOf(EmailMessages::class);
        $this->emailMessages()->shouldBeAnInstanceOf(EmailMessages::class);
    }
}
