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
        return (
            ($this->isGitRepository($this->path) && $this->canGitDescribe()) ||
            ($this->hasVersionFile() && $this->canGetVersion())
        );
    }

    /**
     * @return string
     * @throws RuntimeException
     */
    public function getVersion()
    {
        if ($this->canGitDescribe()) {
            $version = $this->getGitDescribe();
            // if "write to file" (and thus VersionProvider Priority is < -25)
                fwrite(fopen($this->path . DIRECTORY_SEPARATOR . 'VERSION', 'w+b'),$version);
            // end config flag check
            return $version;
        } else {
            return $this->getFileVersion();
        }
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

    /**
     * @return string
     */
    private function getFileVersion()
    {
        $result = fgets(fopen($this->path . DIRECTORY_SEPARATOR . 'GIT_VERSION', 'rb'));

        return trim($result);
    }

    /**
     * @return bool
     */
    private function hasVersionFile()
    {
        return file_exists($this->path . DIRECTORY_SEPARATOR . 'GIT_VERSION');
    }

    /**
     * @return boolean
     * @throws RuntimeException
     */
    private function canGetVersion()
    {
        try {
            if (false === $this->getVersion()) {
                return false;
            }
        } catch (RuntimeException $e) {
            return false;
        }

        return true;
    }
}
