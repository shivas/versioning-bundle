<?php
declare(strict_types=1);

namespace Shivas\VersioningBundle\Tests\Provider;

use bovigo\vfs\vfsStream;
use bovigo\vfs\vfsStreamDirectory;
use PHPUnit\Framework\TestCase;
use Shivas\VersioningBundle\Provider\RevisionProvider;

/**
 * @author Dominic Tubach <dominic.tubach@to.com>
 *
 * @covers \Shivas\VersioningBundle\Provider\RevisionProvider::__construct
 * @covers \Shivas\VersioningBundle\Provider\RevisionProvider::<!public>
 */
final class RevisionProviderTest extends TestCase
{
    /**
     * @var vfsStreamDirectory
     */
    private $root;

    /**
     * @var RevisionProvider
     */
    private $revisionProvider;

    protected function setUp(): void
    {
        $this->root = vfsStream::setup();
        $this->revisionProvider = new RevisionProvider($this->root->url());
    }

    /**
     * @covers \Shivas\VersioningBundle\Provider\RevisionProvider::isSupported
     */
    public function testIsSupportedWithoutFile(): void
    {
        $this->assertFalse($this->revisionProvider->isSupported());
    }

    /**
     * @covers \Shivas\VersioningBundle\Provider\RevisionProvider::isSupported
     */
    public function testIsSupportedWithUnreadableFile(): void
    {
        vfsStream::newFile('REVISION', 0000)
            ->withContent('1.2.3')
            ->at($this->root);
        $this->assertFalse($this->revisionProvider->isSupported());
    }

    /**
     * @covers \Shivas\VersioningBundle\Provider\RevisionProvider::isSupported
     */
    public function testIsSupportedWithWhiteSpaceOnly(): void
    {
        vfsStream::newFile('REVISION')
            ->withContent(" \n")
            ->at($this->root);
        $this->assertFalse($this->revisionProvider->isSupported());
    }

    /**
     * @covers \Shivas\VersioningBundle\Provider\RevisionProvider::isSupported
     */
    public function testIsSupported(): void
    {
        vfsStream::newFile('REVISION')
            ->withContent('1.2.3')
            ->at($this->root);
        $this->assertTrue($this->revisionProvider->isSupported());
    }

    /**
     * @covers \Shivas\VersioningBundle\Provider\RevisionProvider::getVersion
     */
    public function testGetVersion(): void
    {
        vfsStream::newFile('REVISION')
            ->withContent('1.2.3')
            ->at($this->root);
        $this->assertSame('1.2.3', $this->revisionProvider->getVersion());
    }

    /**
     * @covers \Shivas\VersioningBundle\Provider\RevisionProvider::getVersion
     */
    public function testGetVersionTrimsWhitespace(): void
    {
        vfsStream::newFile('REVISION')
            ->withContent("1.2.3 \n")
            ->at($this->root);
        $this->assertSame('1.2.3', $this->revisionProvider->getVersion());
    }
}
