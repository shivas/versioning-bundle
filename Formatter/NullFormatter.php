<?php
declare(strict_types=1);

namespace Shivas\VersioningBundle\Formatter;

use Version\Version;

class NullFormatter implements FormatterInterface
{
    public function format(Version $version): Version
    {
        return $version;
    }
}
