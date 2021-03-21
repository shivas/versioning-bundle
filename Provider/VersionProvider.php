<?php
declare(strict_types=1);

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

    public function __construct(string $path)
    {
        $this->path = $path;
    }

    public function isSupported(): bool
    {
        return $this->hasVersionFile() && $this->canGetVersion();
    }

    public function getVersion(): string
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
