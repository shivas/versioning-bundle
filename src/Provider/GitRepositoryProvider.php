<?php
declare(strict_types=1);

namespace Shivas\VersioningBundle\Provider;

use RuntimeException;

/**
 * Class GitRepositoryProvider
 */
class GitRepositoryProvider implements ProviderInterface
{
    /**
     * @var string
     */
    private $path;

    public function __construct(string $path)
    {
        $this->path = $path;
    }

    public function isSupported(): bool
    {
        return $this->isGitRepository($this->path) && $this->canGitDescribe();
    }

    /**
     * @return string
     * @throws RuntimeException
     */
    public function getVersion(): string
    {
        return $this->getGitDescribe();
    }

    private function isGitRepository(string $path): bool
    {
        // silenced to avoid E_WARNING on open_basedir restriction
        if (!@is_readable($path)) {
            return false;
        }

        if (file_exists($path . DIRECTORY_SEPARATOR . '.git')) {
            return true;
        }

        $path = dirname($path);
        $parentPath = dirname($path);

        if (strlen($path) === strlen($parentPath) || $parentPath === '.') {
            return false;
        }

        return $this->isGitRepository($path);
    }

    /**
     * If describing throws error return false, otherwise true
     *
     * @return bool
     */
    private function canGitDescribe(): bool
    {
        try {
            $this->getGitDescribe();
        } catch (RuntimeException $e) {
            return false;
        }

        return true;
    }

    /**
     * @return string
     * @throws RuntimeException
     */
    private function getGitDescribe(): string
    {
        $dir = getcwd();
        if (false === $dir) {
            throw new RuntimeException('getcwd() returned false');
        }

        chdir($this->path);
        $result = exec('git describe --tags --long 2>&1', $output, $returnCode);
        chdir($dir);

        if ($returnCode !== 0) {
            throw new RuntimeException('Git error: ' . $result);
        }

        /** @var string $result */
        return $result;
    }
}
