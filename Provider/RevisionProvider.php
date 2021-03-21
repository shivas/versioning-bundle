<?php
declare(strict_types=1);

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

    public function __construct(string $path)
    {
        $this->path = $path;
    }

    public function isSupported(): bool
    {
        return $this->isCapistranoEnv() && $this->canGetRevision();
    }

    public function getVersion(): string
    {
        return $this->getRevision();
    }

    private function isCapistranoEnv(): bool
    {
        return file_exists($this->path . DIRECTORY_SEPARATOR . 'REVISION');
    }

    private function canGetRevision(): bool
    {
        try {
            if ('' === $this->getRevision()) {
                return false;
            }
        } catch (RuntimeException $e) {
            return false;
        }

        return true;
    }

    private function getRevision(): string
    {
        $filename = $this->path . DIRECTORY_SEPARATOR . 'REVISION';
        $result = file_get_contents($filename);
        if (false === $result) {
            throw new RuntimeException(sprintf('Reading "%s" failed', $filename));
        }

        return rtrim($result);
    }
}
