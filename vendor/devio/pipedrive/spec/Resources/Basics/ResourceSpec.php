<?php

namespace spec\Devio\Pipedrive\Resources\Basics;

use Devio\Pipedrive\Exceptions\PipedriveException;
use Devio\Pipedrive\Http\Request;
use Devio\Pipedrive\Resources\Basics\Resource;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

class ResourceSpec extends ObjectBehavior
{
    public function it_is_initializable()
    {
        $this->shouldHaveType('Devio\Pipedrive\Resources\Basics\Resource');
    }

    public function let(Request $request)
    {
        $this->beAnInstanceOf(ResourceClass::class);
        $this->beConstructedWith($request);
    }
    
    public function it_identifies_an_enabled_method()
    {
        $this->setEnabled('all', 'find');

        $this->isEnabled('all')->shouldBe(true);
        $this->isEnabled('find')->shouldBe(true);
        $this->isEnabled('update')->shouldBe(false);
        $this->isEnabled('delete')->shouldBe(false);

        $this->shouldThrow(PipedriveException::class)->during('__call', ['update']);
        $this->shouldThrow(PipedriveException::class)->during('__call', ['delete']);
    }

    public function it_identifies_a_disabled_method()
    {
        $this->setDisabled('all', 'find');

        $this->isDisabled('all')->shouldBe(true);
        $this->isDisabled('find')->shouldBe(true);
        $this->isEnabled('all')->shouldBe(false);
        $this->isEnabled('find')->shouldBe(false);

        $this->shouldThrow(PipedriveException::class)->during('__call', ['all']);
        $this->shouldThrow(PipedriveException::class)->during('__call', ['find']);
    }
}

class ResourceClass extends Resource
{
}
