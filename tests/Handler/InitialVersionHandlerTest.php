<?php

namespace tests\Shivas\VersioningBundle\Handler;

use PHPUnit\Framework\TestCase;
use Shivas\VersioningBundle\Handler\InitialVersionHandler;

class InitialVersionHandlerTest extends TestCase
{
    public function testInitializable()
    {
        $handler = new InitialVersionHandler();

        $this->assertInstanceOf(InitialVersionHandler::class, $handler);
    }

    public function testAlwaysSupported()
    {
        $handler = new InitialVersionHandler();

        $this->assertTrue($handler->isSupported(), 'The handler should always be supported');
    }

    public function testInitialVersion()
    {
        $handler = new InitialVersionHandler();

        $this->assertTrue(is_string($handler->getVersion()), 'The handler version must be a string');
        $this->assertEquals('0.1.0', $handler->getVersion(), 'The initial version is wrong');
    }
}
