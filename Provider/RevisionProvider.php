<?php

namespace Shivas\VersioningBundle\Provider;

use RuntimeException;

/**
 * Class RevisionProvider
 */
class RevisionProvider implements ProviderInterface
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
        return $this->isCapistranoEnv() && $this->canGetRevision();
    }

    /**
     * @return string
     */
    public function getVersion()
    {
        return $this->getRevision();
    }

    /**
     * @return bool
     */
    private function isCapistranoEnv()
    {
        return file_exists($this->path . DIRECTORY_SEPARATOR . 'REVISION');
    }

    /**
     * If describing throws error return false, otherwise true
     *
     * @return boolean
     * @throws RuntimeException
     */
    private function canGetRevision()
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
     * @return string
     */
    private function getRevision()
    {
        $result = file_get_contents($this->path . DIRECTORY_SEPARATOR . 'REVISION');

        return $result;
    }
}
