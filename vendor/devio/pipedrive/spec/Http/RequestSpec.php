<?php

namespace spec\Devio\Pipedrive\Http;

use Devio\Pipedrive\Http\Client;
use Devio\Pipedrive\Http\Response;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

class RequestSpec extends ObjectBehavior
{
    public function it_is_initializable()
    {
        $this->shouldHaveType('Devio\Pipedrive\Http\Request');
    }

    public function let(Client $client)
    {
        $this->beConstructedWith($client);
        $this->setToken('foobarbaz');
    }

    public function it_makes_a_request(Client $client, Response $response)
    {
        $content = ['success' => true, 'data' => []];
        $response->isSuccess()
            ->shouldBeCalled()
            ->willReturn(true);
        $response->getStatusCode()
            ->shouldBeCalled()
            ->willReturn(200);
        $response->getContent()
            ->shouldBeCalled()
            ->willReturn((object)$content);
        $client->get('foo/1', [])
            ->shouldBeCalled()
            ->willReturn($response);
        $client->put('foo/1', ['name' => 'bar'])
            ->shouldBeCalled()
            ->willReturn($response);
        $client->post('foo', [])
            ->shouldBeCalled()
            ->willReturn($response);

        $this->get('foo/:id', ['id' => 1]);
        $this->put('foo/:id', ['id' => 1, 'name' => 'bar']);
        $this->post('foo', []);
    }
}
