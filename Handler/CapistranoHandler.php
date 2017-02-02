<?php

namespace Shivas\VersioningBundle\Handler;

use Herrera\Version\Validator;
use Herrera\Version\Version;
use Herrera\Version\Parser;
use Herrera\Version\Builder;
use Shivas\VersioningBundle\Handler\HandlerInterface;

class CapistranoHandler implements HandlerInterface
{
    const DESCRIBE_REGEX = '/^[vV]?(%s)-([0-9]*)-g([0-9a-f]{7,32})$/';

    /** @var string */
    private $path;

    public function __construct($path)
    {
        $this->path = $path . '/../';
    }

    /**
     * @return bool
     */
    public function isSupported()
    {
        return $this->isCapistranoEnv() && $this->canGetRevision();
    }

    /**
     * If describing throws error return false, otherwise true
     *
     * @return bool
     */
    public function canGetRevision()
    {
        try {
            if (false === $this->getRevision()) {
                return false;
            }
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

        $version = $this->getRevision();

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
    private function getRevision()
    {
        $result = file_get_contents($this->path . 'REVISION');

        return $result;
    }

    /**
     * @return bool
     */
    private function isCapistranoEnv()
    {
        return file_exists($this->path . 'REVISION');
    }

    /**
     * @return string
     */
    public function getName()
    {
        return 'Capistrano tag handler';
    }
}
