<?php

namespace tests\Shivas\VersioningBundle\Provider;

use PHPUnit\Framework\TestCase;
use Shivas\VersioningBundle\Provider\InitialVersionProvider;

/**
 * Class InitialVersionProviderTest
 */
class InitialVersionProviderTest extends TestCase
{
    public function testInitializable()
    {
        $provider = new InitialVersionProvider();

        $this->assertInstanceOf(InitialVersionProvider::class, $provider);
    }

    public function testAlwaysSupported()
    {
        $provider = new InitialVersionProvider();

        $this->assertTrue($provider->isSupported(), 'The provider should always be supported');
    }

    public function testInitialVersion()
    {
        $provider = new InitialVersionProvider();

        $this->assertTrue(is_string($provider->getVersion()), 'The provider version must be a string');
        $this->assertEquals('0.1.0', $provider->getVersion(), 'The initial version is wrong');
    }
}
