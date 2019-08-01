<?php
namespace Shivas\VersioningBundle\Tests\Provider;

use org\bovigo\vfs\vfsStream;
use org\bovigo\vfs\vfsStreamDirectory;
use PHPUnit\Framework\TestCase;
use Shivas\VersioningBundle\Provider\VersionProvider;

/**
 * @author Dominic Tubach <dominic.tubach@to.com>
 *
 * @covers \Shivas\VersioningBundle\Provider\VersionProvider::__construct
 * @covers \Shivas\VersioningBundle\Provider\VersionProvider::<!public>
 */
final class VersionProviderTest extends TestCase
{
    /**
     * @var vfsStreamDirectory
     */
    private $root;

    /**
     * @var VersionProvider
     */
    private $versionProvider;

    protected function setUp()
    {
        $this->root = vfsStream::setup();
        $this->versionProvider = new VersionProvider($this->root->url());
    }

    /**
     * @covers \Shivas\VersioningBundle\Provider\VersionProvider::isSupported
     */
    public function testIsSupportedWithoutFile()
    {
        $this->assertFalse($this->versionProvider->isSupported());
    }

    /**
     * @covers \Shivas\VersioningBundle\Provider\VersionProvider::isSupported
     */
    public function testIsSupportedWithUnreadableFile()
    {
        vfsStream::newFile('VERSION', 0000)
            ->withContent('1.2.3')
            ->at($this->root);
        $this->assertFalse($this->versionProvider->isSupported());
    }

    /**
     * @covers \Shivas\VersioningBundle\Provider\VersionProvider::isSupported
     */
    public function testIsSupportedWithWhiteSpaceOnly()
    {
        vfsStream::newFile('VERSION')
            ->withContent(" \n")
            ->at($this->root);
        $this->assertFalse($this->versionProvider->isSupported());
    }

    /**
     * @covers \Shivas\VersioningBundle\Provider\VersionProvider::isSupported
     */
    public function testIsSupported()
    {
        vfsStream::newFile('VERSION')
            ->withContent('1.2.3')
            ->at($this->root);
        $this->assertTrue($this->versionProvider->isSupported());
    }

    /**
     * @covers \Shivas\VersioningBundle\Provider\VersionProvider::getVersion
     */
    public function testGetVersion()
    {
        vfsStream::newFile('VERSION')
            ->withContent('1.2.3')
            ->at($this->root);
        $this->assertSame('1.2.3', $this->versionProvider->getVersion());
    }

    /**
     * @covers \Shivas\VersioningBundle\Provider\VersionProvider::getVersion
     */
    public function testGetVersionTrimsWhitespace()
    {
        vfsStream::newFile('VERSION')
            ->withContent("1.2.3 \n")
            ->at($this->root);
        $this->assertSame('1.2.3', $this->versionProvider->getVersion());
    }
}
