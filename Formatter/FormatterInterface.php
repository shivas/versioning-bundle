<?php
declare(strict_types=1);

namespace Shivas\VersioningBundle\Formatter;

use Version\Version;

/**
 * Interface FormatterInterface
 */
interface FormatterInterface
{
    public function format(Version $version): Version;
}
