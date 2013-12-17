<?php
namespace Shivas\VersioningBundle\Handler;

use Herrera\Version\Validator;
use Herrera\Version\Version;
use Herrera\Version\Parser;
use Herrera\Version\Builder;

class GitRepositoryHandler implements HandlerInterface
{
    const DESCRIBE_REGEX = '/^[vV]?(%s)-([0-9]*)-g([0-9a-f]{7,32})$/';

    /** @var string */
    private $path;

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
     * If describing throws error return false, otherwise true
     *
     * @return bool
     */
    public function canGitDescribe()
    {
        try {
            $this->getGitDescribe();
        } catch (\RuntimeException $e) {
            return false;
        }

        return true;
    }

    /**
     * @throws \RuntimeException
     * @return Version
     */
    public function getVersion()
    {
        $semVersionRegex = substr(Validator::VERSION_REGEX, 2, -2);
        $describeRegex = sprintf(self::DESCRIBE_REGEX, $semVersionRegex);

        $version = $this->getGitDescribe();

        if (!preg_match($describeRegex, $version, $matches)) {
            throw new \RuntimeException($this->getName(). " describe returned no valid version");
        }

        $builder = Parser::toBuilder($matches[1]);
        $this->handleMetaData($builder, $matches);
        return $builder->getVersion();
    }

    /**
     * @param Builder $builder
     * @param $matches
     */
    protected function handleMetaData(Builder $builder, $matches)
    {
        if (intval($matches[7]) != 0) {
            // we are not on TAG commit, add "dev" and git commit hash as pre release part
            $preRelease = array_merge($builder->getPreRelease(), array('dev', $matches[8]));
            $builder->setPreRelease($preRelease);
        }
    }

    /**
     * @return string
     * @throws \RuntimeException
     */
    private function getGitDescribe()
    {
        $dir = getcwd();
        chdir($this->path);
        $result = exec('git describe --tags --long 2>&1', $output, $returnCode);
        chdir($dir);

        if ($returnCode !== 0) {
            throw new \RuntimeException('Git error: '. $result);
        }

        return $result;
    }


    /**
     * @param string $path
     * @return boolean
     */
    private function isGitRepository($path)
    {
        if (is_dir($path . DIRECTORY_SEPARATOR . '.git')) {
            return true;
        }

        $path = dirname($path);

        if (strlen($path) == strlen(dirname($path))) {
            return false;
        }

        return $this->isGitRepository($path);
    }

    /**
     * @return string
     */
    public function getName()
    {
        return 'Git tag describe handler';
    }
}

