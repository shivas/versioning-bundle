<?php

namespace Shivas\VersioningBundle\Handler;

use RuntimeException;
use Version\Exception\InvalidVersionStringException;
use Version\Version;

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
        } catch (RuntimeException $e) {
            return false;
        }

        return true;
    }

    /**
     * @throws RuntimeException
     * @return Version
     */
    public function getVersion()
    {
        $semVersionRegex = '(?P<core>(?:[0-9]|[1-9][0-9]+)(?:\.(?:[0-9]|[1-9][0-9]+)){2})'
            . '(?:\-(?P<preRelease>[0-9A-Za-z\-\.]+))?'
            . '(?:\+(?P<build>[0-9A-Za-z\-\.]+))?';
        $describeRegex = sprintf(self::DESCRIBE_REGEX, $semVersionRegex);

        $version = $this->getRevision();
        if (!preg_match($describeRegex, $version, $matches)) {
            throw new RuntimeException($this->getName(). " describe returned no valid version");
        }

        try {
            $version = Version::fromString($matches[1]);
            $version = $this->handleMetaData($version, $matches);
        } catch (InvalidVersionStringException $e) {
            throw new RuntimeException($this->getName() . " describe returned no valid version");
        }

        return $version;
    }

    /**
     * @param   Version $version
     * @param   array   $matches
     * @return  Version
     */
    protected function handleMetaData(Version $version, $matches)
    {
        if ((int) $matches[5] != 0) {
            // we are not on TAG commit, add "dev" and git commit hash as pre release part
            $preRelease = $version->getPreRelease();
            $version = $version->withPreRelease(array_merge($preRelease->toArray(), array('dev', $matches[6])));
        }

        return $version;
    }

    /**
     * @return string
     * @throws RuntimeException
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
