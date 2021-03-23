<?php
declare(strict_types=1);

namespace Shivas\VersioningBundle\Writer;

use Version\Version;

/**
 * Class VersionWriter
 */
class VersionWriter implements WriterInterface
{
    /**
     * @var string
     */
    private $path;

    public function __construct(string $path)
    {
        $this->path = $path;
    }

    public function write(Version $version): void
    {
        file_put_contents($this->path . DIRECTORY_SEPARATOR . 'VERSION', $version);
    }
}
