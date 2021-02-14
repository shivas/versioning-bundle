<?php

namespace Shivas\VersioningBundle\Tests\Provider;

use PHPUnit\Framework\TestCase;
use Shivas\VersioningBundle\Provider\InitialVersionProvider;

/**
 * Class InitialVersionProviderTest
 */
final class InitialVersionProviderTest extends TestCase
{
    public function testInitializable(): void
    {
        $provider = new InitialVersionProvider();

        $this->assertInstanceOf(InitialVersionProvider::class, $provider);
    }

    public function testAlwaysSupported(): void
    {
        $provider = new InitialVersionProvider();

        $this->assertTrue($provider->isSupported(), 'The provider should always be supported');
    }

    public function testInitialVersion(): void
    {
        $provider = new InitialVersionProvider();

        $this->assertTrue(is_string($provider->getVersion()), 'The provider version must be a string');
        $this->assertEquals('0.1.0', $provider->getVersion(), 'The initial version is wrong');
    }
}
