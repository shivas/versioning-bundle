<?php

namespace Shivas\VersioningBundle\Provider;

use RuntimeException;

/**
 * Class VersionProvider
 */
class VersionProvider implements ProviderInterface
{
    /**
     * @var string
     */
    private $path;

    /**
     * Constructor
     *
     * @param string $path
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
        return $this->hasVersionFile() && $this->canGetVersion();
    }

    /**
     * @return string
     */
    public function getVersion()
    {
        $filename = $this->path . DIRECTORY_SEPARATOR . 'VERSION';
        $result = file_get_contents($filename);
        if (false === $result) {
            throw new RuntimeException(sprintf('Reading "%s" failed', $filename));
        }

        return rtrim($result);
    }

    /**
     * @return bool
     */
    private function hasVersionFile()
    {
        return file_exists($this->path . DIRECTORY_SEPARATOR . 'VERSION');
    }

    /**
     * @return boolean
     * @throws RuntimeException
     */
    private function canGetVersion()
    {
        try {
            if ('' === $this->getVersion()) {
                return false;
            }
        } catch (RuntimeException $e) {
            return false;
        }

        return true;
    }
}
