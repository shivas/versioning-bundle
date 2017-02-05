<?php

namespace tests\Shivas\VersioningBundle\Service;

use PHPUnit\Framework\TestCase;
use Shivas\VersioningBundle\Service\VersionsManager;

/**
 * Class VersionsManagerTest
 */
class VersionsManagerTest extends TestCase
{
    public function testInitializable()
    {
        $manager = new VersionsManager();

        $this->assertInstanceOf(VersionsManager::class, $manager);
    }

    public function testVersions()
    {
        $this->assertEquals('0.1.0', $this->getVersionFromManager('0.1.0'), 'Basic version number');
        $this->assertEquals('0.1.5', $this->getVersionFromManager('v0.1.5'), 'Tag prefix should be ignored');
        $this->assertEquals('1.4.1', $this->getVersionFromManager('1.4.1-0-gd891f45'), 'Tag commit should ignore hash');
        $this->assertEquals('1.4.1-dev.7f07e6d', $this->getVersionFromManager('1.4.1-1-g7f07e6d'), 'Not on tag commit adds dev.hash');
        $this->assertEquals('2.3.3-dev.1c224d9fa', $this->getVersionFromManager('v2.3.3-201-g1c224d9fa'), 'Multiple commits since last tag');
        $this->assertEquals('1.2.3-foo-bar.1', $this->getVersionFromManager('1.2.3-foo-bar.1-0-gd891f45'), 'Long version on tag commit');
        $this->assertEquals('1.2.3-foo-bar.1.dev.13ebcdd', $this->getVersionFromManager('1.2.3-foo-bar.1-203-g13ebcdd'), 'Long version not on tag commit');
    }

    protected function getVersionFromManager($version)
    {
        $provider = $this->createMock('Shivas\VersioningBundle\Provider\ProviderInterface');
        $provider->method('isSupported')->willReturn(true);
        $provider->method('getVersion')->willReturn($version);
        $provider->method('getName')->willReturn('Mock provider');

        $manager = new VersionsManager();
        $manager->addProvider($provider, 'mock', 0);

        return $manager->getVersion();
    }
}
