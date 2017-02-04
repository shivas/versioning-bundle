<?php

namespace spec\Shivas\VersioningBundle\Service;

use Shivas\VersioningBundle\Service\VersionsManager;
use PhpSpec\ObjectBehavior;

class VersionsManagerSpec extends ObjectBehavior
{
    protected function addMockHandler($handler, $version)
    {
        $handler->beADoubleOf('Shivas\VersioningBundle\Handler\HandlerInterface');
        $handler->isSupported()->willReturn(true);
        $handler->getVersion()->willReturn($version);
        $handler->getName()->willReturn('Mock handler');

        $this->addHandler($handler, 'mock', 0);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(VersionsManager::class);
    }

    function it_parses_initial_version($handler)
    {
        $this->addMockHandler($handler, '0.1.0');

        $this->getVersion()->getVersionString()->shouldBe('0.1.0');
    }

    function it_parses_version_tags($handler)
    {
        $this->addMockHandler($handler, 'v0.1.5');

        $this->getVersion()->getVersionString()->shouldBe('0.1.5');
    }

    function it_parses_git_describe_version($handler)
    {
        $this->addMockHandler($handler, '1.4.1-2-g7f07e6d');

        $this->getVersion()->getVersionString()->shouldBe('1.4.1-dev.7f07e6d');
    }

    function it_parses_complex_git_describe_version($handler)
    {
        $this->addMockHandler($handler, '1.2.3-foo-bar.1-3-g13ebcdd');

        $this->getVersion()->getVersionString()->shouldBe('1.2.3-foo-bar.1.dev.13ebcdd');
    }
}
