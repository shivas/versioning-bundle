<?php
namespace Shivas\VersioningBundle\Tests\Provider;

use org\bovigo\vfs\vfsStream;
use org\bovigo\vfs\vfsStreamDirectory;
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

    protected function setUp()
    {
        $this->root = vfsStream::setup();
        $this->revisionProvider = new RevisionProvider($this->root->url());
    }

    /**
     * @covers \Shivas\VersioningBundle\Provider\RevisionProvider::isSupported
     */
    public function testIsSupportedWithoutFile()
    {
        $this->assertFalse($this->revisionProvider->isSupported());
    }

    /**
     * @covers \Shivas\VersioningBundle\Provider\RevisionProvider::isSupported
     */
    public function testIsSupportedWithUnreadableFile()
    {
        vfsStream::newFile('REVISION', 0000)
            ->withContent('1.2.3')
            ->at($this->root);
        $this->assertFalse($this->revisionProvider->isSupported());
    }

    /**
     * @covers \Shivas\VersioningBundle\Provider\RevisionProvider::isSupported
     */
    public function testIsSupportedWithWhiteSpaceOnly()
    {
        vfsStream::newFile('REVISION')
            ->withContent(" \n")
            ->at($this->root);
        $this->assertFalse($this->revisionProvider->isSupported());
    }

    /**
     * @covers \Shivas\VersioningBundle\Provider\RevisionProvider::isSupported
     */
    public function testIsSupported()
    {
        vfsStream::newFile('REVISION')
            ->withContent('1.2.3')
            ->at($this->root);
        $this->assertTrue($this->revisionProvider->isSupported());
    }

    /**
     * @covers \Shivas\VersioningBundle\Provider\RevisionProvider::getVersion
     */
    public function testGetVersion()
    {
        vfsStream::newFile('REVISION')
            ->withContent('1.2.3')
            ->at($this->root);
        $this->assertSame('1.2.3', $this->revisionProvider->getVersion());
    }

    /**
     * @covers \Shivas\VersioningBundle\Provider\RevisionProvider::getVersion
     */
    public function testGetVersionTrimsWhitespace()
    {
        vfsStream::newFile('REVISION')
            ->withContent("1.2.3 \n")
            ->at($this->root);
        $this->assertSame('1.2.3', $this->revisionProvider->getVersion());
    }
}
