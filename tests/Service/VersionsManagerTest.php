<?php

namespace tests\Shivas\VersioningBundle\Service;

use PHPUnit\Framework\TestCase;
use Shivas\VersioningBundle\Service\VersionsManager;

class VersionsManagerTest extends TestCase
{
    public function testInitializable()
    {
        $manager = new VersionsManager();

        $this->assertInstanceOf(VersionsManager::class, $manager);
    }

    public function testVersions()
    {
        $this->assertEquals('0.1.0', $this->getVersionFromHandler('0.1.0'), 'Basic version number');
        $this->assertEquals('0.1.5', $this->getVersionFromHandler('v0.1.5'), 'Tag prefix should be ignored');
        $this->assertEquals('1.4.1', $this->getVersionFromHandler('1.4.1-0-gd891f45'), 'Tag commit should ignore hash');
        $this->assertEquals('1.4.1-dev.7f07e6d', $this->getVersionFromHandler('1.4.1-1-g7f07e6d'), 'Not on tag commit adds dev.hash');
        $this->assertEquals('2.3.3-dev.1c224d9fa', $this->getVersionFromHandler('v2.3.3-201-g1c224d9fa'), 'Multiple commits since last tag');
        $this->assertEquals('1.2.3-foo-bar.1', $this->getVersionFromHandler('1.2.3-foo-bar.1-0-gd891f45'), 'Long version on tag commit');
        $this->assertEquals('1.2.3-foo-bar.1.dev.13ebcdd', $this->getVersionFromHandler('1.2.3-foo-bar.1-203-g13ebcdd'), 'Long version not on tag commit');
    }

    protected function getVersionFromHandler($version)
    {
        $handler = $this->createMock('Shivas\VersioningBundle\Handler\HandlerInterface');
        $handler->method('isSupported')->willReturn(true);
        $handler->method('getVersion')->willReturn($version);
        $handler->method('getName')->willReturn('Mock handler');

        $manager = new VersionsManager();
        $manager->addHandler($handler, 'mock', 0);

        return $manager->getVersion();
    }
}
