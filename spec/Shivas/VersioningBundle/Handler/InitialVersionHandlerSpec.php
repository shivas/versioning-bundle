<?php

namespace spec\Shivas\VersioningBundle\Handler;

use Shivas\VersioningBundle\Handler\InitialVersionHandler;
use PhpSpec\ObjectBehavior;

class InitialVersionHandlerSpec extends ObjectBehavior
{
    function it_is_initializable()
    {
        $this->shouldHaveType(InitialVersionHandler::class);
    }

    function it_should_always_be_supported()
    {
        $this->isSupported()->shouldReturn(true);
    }

    function it_should_return_an_initial_version()
    {
        $this->getVersion()->shouldBeString();
        $this->getVersion()->shouldBe('0.1.0');
    }
}
