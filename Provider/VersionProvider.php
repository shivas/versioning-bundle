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

    private function hasVersionFile(): bool
    {
        return file_exists($this->path . DIRECTORY_SEPARATOR . 'VERSION');
    }

    private function canGetVersion(): bool
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
