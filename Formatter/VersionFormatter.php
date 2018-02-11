<?php

namespace Shivas\VersioningBundle\Formatter;

use Version\Version;

/**
 * Class VersionFormatter
 */
class VersionFormatter implements FormatterInterface
{
    /**
     * Return the version without additional formatting
     *
     * @param  Version $version
     * @return Version
     */
    public function format(Version $version)
    {
        return $version;
    }
}
