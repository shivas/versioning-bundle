<?php
declare(strict_types=1);

namespace Shivas\VersioningBundle\Tests\Provider;

use bovigo\vfs\vfsStream;
use bovigo\vfs\vfsStreamDirectory;
use PHPUnit\Framework\TestCase;
use RuntimeException;
use Shivas\VersioningBundle\Provider\GitRepositoryProvider;

/**
 * @author Dominic Tubach <dominic.tubach@to.com>
 *
 * @covers \Shivas\VersioningBundle\Provider\GitRepositoryProvider::__construct
 * @covers \Shivas\VersioningBundle\Provider\GitRepositoryProvider::<!public>
 */
final class GitRepositoryProviderTest extends TestCase
{
    /**
     * @var int
     */
    public static $gitDescribeExitCode;

    /**
     * @var string
     */
    public static $gitDescribeOutput;

    /**
     * @var string
     */
    public static $path;

    /**
     * @var GitRepositoryProvider
     */
    private $gitRepositoryProvider;

    /**
     * @var vfsStreamDirectory
     */
    private $root;

    public static function setUpBeforeClass(): void
    {
        self::mockChdir();
        self::mockGitDescribe();
    }

    protected function setUp(): void
    {
        self::$gitDescribeExitCode = 0;
        self::$gitDescribeOutput = __CLASS__;

        $structure = [
            'folder' => [
                'subfolder' => [
                ],
            ],
        ];
        $this->root = vfsStream::setup('root', null, $structure);
        self::$path = $this->root->getChild('folder/subfolder')->url();
        $this->gitRepositoryProvider = new GitRepositoryProvider(self::$path);
    }

    /**
     * @covers \Shivas\VersioningBundle\Provider\GitRepositoryProvider::isSupported
     */
    public function testIsSupportedNonGit(): void
    {
        $this->assertFalse($this->gitRepositoryProvider->isSupported());
    }

    /**
     * @covers \Shivas\VersioningBundle\Provider\GitRepositoryProvider::isSupported
     */
    public function testIsSupportedDirNotReadable(): void
    {
        vfsStream::newDirectory('.git')->at($this->root);
        $this->root->getChild('folder')->chmod(0000);
        $this->assertFalse($this->gitRepositoryProvider->isSupported());
    }

    /**
     * @covers \Shivas\VersioningBundle\Provider\GitRepositoryProvider::isSupported
     */
    public function testIsSupportedGitDescribeError(): void
    {
        vfsStream::newDirectory('.git')->at($this->root);
        self::$gitDescribeExitCode = 1;
        $this->assertFalse($this->gitRepositoryProvider->isSupported());
    }

    /**
     * @covers \Shivas\VersioningBundle\Provider\GitRepositoryProvider::isSupported
     */
    public function testIsSupportedDir(): void
    {
        vfsStream::newDirectory('.git')->at($this->root);
        self::$gitDescribeOutput = '1.2.3';
        $this->assertTrue($this->gitRepositoryProvider->isSupported());
    }

    /**
     * @covers \Shivas\VersioningBundle\Provider\GitRepositoryProvider::isSupported
     */
    public function testIsSupportedFile(): void
    {
        vfsStream::newFile('.git')->at($this->root);
        self::$gitDescribeOutput = '1.2.3';
        $this->assertTrue($this->gitRepositoryProvider->isSupported());
    }

    /**
     * @covers \Shivas\VersioningBundle\Provider\GitRepositoryProvider::getVersion
     */
    public function testGetVersion(): void
    {
        vfsStream::newDirectory('.git')->at($this->root);
        self::$gitDescribeOutput = '1.2.3';
        $this->assertSame('1.2.3', $this->gitRepositoryProvider->getVersion());
    }

    /**
     * @covers \Shivas\VersioningBundle\Provider\GitRepositoryProvider::getVersion
     */
    public function testGetVersionGitDescribeError(): void
    {
        vfsStream::newDirectory('.git')->at($this->root);
        self::$gitDescribeExitCode = 1;
        self::$gitDescribeOutput = 'Test';
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Git error: Test');
        $this->gitRepositoryProvider->getVersion();
    }

    private static function mockChdir(): void
    {
        $self = '\\' . __CLASS__;
        eval(<<<EOPHP
namespace Shivas\VersioningBundle\Provider;

function chdir(string \$directory): bool
{
    if ($self::\$path === \$directory) {
        return true;
    }

    return \\chdir(\$directory);
}
EOPHP
        );
    }

    private static function mockGitDescribe(): void
    {
        $self = '\\' . __CLASS__;
        eval(<<<EOPHP
namespace Shivas\VersioningBundle\Provider;

function exec(string \$command, array &\$execOutput = null, int &\$execExitCode = null): string
{
    if ('git describe --tags --long 2>&1' === \$command) {
        \$execOutput = $self::\$gitDescribeOutput;
        \$execExitCode = $self::\$gitDescribeExitCode;

        return $self::\$gitDescribeOutput;
    }

    throw new \RuntimeException(sprintf('Unexpected command "%s"', \$command));
}
EOPHP
        );
    }
}
