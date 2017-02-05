<?php

namespace Shivas\VersioningBundle\Formatter;

use Version\Version;

/**
 * Interface FormatterInterface
 */
interface FormatterInterface
{
    /**
     * @param  Version $version
     * @return Version
     */
    public function format(Version $version);
}
