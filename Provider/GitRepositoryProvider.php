<?php

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

    /**
     * Constructor
     *
     * @param $path
     */
    public function __construct($path)
    {
        $this->path = $path;
    }

    /**
     * @return bool
     */
    public function isSupported()
    {
        return $this->isGitRepository($this->path) && $this->canGitDescribe();
    }

    /**
     * @return string
     * @throws RuntimeException
     */
    public function getVersion()
    {
        $version = $this->getGitDescribe();
        // if "write to file" (and thus VersionProvider Priority is < -25)
        fwrite(fopen($this->path . DIRECTORY_SEPARATOR . 'VERSION', 'w+b'),$version);
        return $version;
    }

    /**
     * @param   string $path
     * @return  boolean
     */
    private function isGitRepository($path)
    {
        if (is_dir($path . DIRECTORY_SEPARATOR . '.git')) {
            return true;
        }

        return false;
    }

    /**
     * If describing throws error return false, otherwise true
     *
     * @return boolean
     */
    private function canGitDescribe()
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
    private function getGitDescribe()
    {
        $dir = getcwd();
        chdir($this->path);
        $result = exec('git describe --tags --long 2>&1', $output, $returnCode);
        chdir($dir);

        if ($returnCode !== 0) {
            throw new RuntimeException('Git error: ' . $result);
        }

        return $result;
    }
}
