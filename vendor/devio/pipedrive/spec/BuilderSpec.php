<?php

namespace spec\Devio\Pipedrive;

use InvalidArgumentException;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

class BuilderSpec extends ObjectBehavior
{
    public function it_is_initializable()
    {
        $this->shouldHaveType('Devio\Pipedrive\Builder');
    }

    public function let()
    {
        $this->setToken('foobarbaz');
    }

    public function it_will_remove_the_options_used_in_the_uri()
    {
        $this->setTarget('foo/:id');
        $this->getQueryVars(['id' => 1])
             ->shouldReturn([]);
        $this->getQueryVars(['id' => 1, 'name' => 'bar', 'country' => 'es'])
             ->shouldReturn(['name' => 'bar', 'country' => 'es']);

        $this->setTarget('foo');
        $this->getQueryVars(['id' => 1])
             ->shouldReturn(['id' => 1]);
    }

    public function it_get_an_array_of_the_uri_parameters()
    {
        $this->getParameters('foo/:id')
             ->shouldReturn(['id']);
        $this->getParameters('foo/:id/:name')
             ->shouldReturn(['id', 'name']);
        $this->getParameters('foo')
             ->shouldReturn([]);
        $this->getParameters(':name/foo/:id')
             ->shouldReturn(['name', 'id']);
        $this->getParameters('foo/:id/bar/:name')
             ->shouldReturn(['id', 'name']);
    }

    public function it_replaces_uri_parameters_with_option_values()
    {
        $this->setTarget('foo/:id');
        $this->buildEndpoint(['id' => 1])
             ->shouldReturn('foo/1');

        $this->setTarget(':id/:name');
        $this->buildEndpoint(['id' => 1, 'name' => 'foo'])
             ->shouldReturn('1/foo');

        $this->setTarget(':id/foo');
        $this->shouldThrow(InvalidArgumentException::class)
             ->duringBuildEndpoint(['bar' => 'baz']);
    }
}
