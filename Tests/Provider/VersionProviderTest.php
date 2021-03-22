<?php
declare(strict_types=1);

namespace Shivas\VersioningBundle\Tests\Provider;

use bovigo\vfs\vfsStream;
use bovigo\vfs\vfsStreamDirectory;
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

    protected function setUp(): void
    {
        $this->root = vfsStream::setup();
        $this->versionProvider = new VersionProvider($this->root->url());
    }

    /**
     * @covers \Shivas\VersioningBundle\Provider\VersionProvider::isSupported
     */
    public function testIsSupportedWithoutFile(): void
    {
        $this->assertFalse($this->versionProvider->isSupported());
    }

    /**
     * @covers \Shivas\VersioningBundle\Provider\VersionProvider::isSupported
     */
    public function testIsSupportedWithUnreadableFile(): void
    {
        vfsStream::newFile('VERSION', 0000)
            ->withContent('1.2.3')
            ->at($this->root);
        $this->assertFalse($this->versionProvider->isSupported());
    }

    /**
     * @covers \Shivas\VersioningBundle\Provider\VersionProvider::isSupported
     */
    public function testIsSupportedWithWhiteSpaceOnly(): void
    {
        vfsStream::newFile('VERSION')
            ->withContent(" \n")
            ->at($this->root);
        $this->assertFalse($this->versionProvider->isSupported());
    }

    /**
     * @covers \Shivas\VersioningBundle\Provider\VersionProvider::isSupported
     */
    public function testIsSupported(): void
    {
        vfsStream::newFile('VERSION')
            ->withContent('1.2.3')
            ->at($this->root);
        $this->assertTrue($this->versionProvider->isSupported());
    }

    /**
     * @covers \Shivas\VersioningBundle\Provider\VersionProvider::getVersion
     */
    public function testGetVersion(): void
    {
        vfsStream::newFile('VERSION')
            ->withContent('1.2.3')
            ->at($this->root);
        $this->assertSame('1.2.3', $this->versionProvider->getVersion());
    }

    /**
     * @covers \Shivas\VersioningBundle\Provider\VersionProvider::getVersion
     */
    public function testGetVersionTrimsWhitespace(): void
    {
        vfsStream::newFile('VERSION')
            ->withContent("1.2.3 \n")
            ->at($this->root);
        $this->assertSame('1.2.3', $this->versionProvider->getVersion());
    }
}
