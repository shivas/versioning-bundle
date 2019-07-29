<?php

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
     * @param Version $version
     */
    public function write(Version $version)
    {
        file_put_contents($this->path . DIRECTORY_SEPARATOR . 'VERSION', $version);
    }
}
