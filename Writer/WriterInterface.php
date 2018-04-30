<?php

namespace Shivas\VersioningBundle\Writer;

use Version\Version;

/**
 * Interface WriterInterface
 */
interface WriterInterface
{
    /**
     * @param  Version $version
     * @return Version
     */
    public function write(Version $version);
}
