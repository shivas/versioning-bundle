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
        return $this->hasVersionFile() && $this->canGetVersion();
    }

    /**
     * @return string
     */
    public function getVersion()
    {
        $handle = fopen($this->path . DIRECTORY_SEPARATOR . 'VERSION', 'rb');
        $result = fgets($handle);
        fclose($handle);

        return trim($result);
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
            if (false === $this->getVersion()) {
                return false;
            }
        } catch (RuntimeException $e) {
            return false;
        }

        return true;
    }
}
