<?php
declare(strict_types=1);

namespace Shivas\VersioningBundle\Writer;

use Version\Version;

interface WriterInterface
{
    public function write(Version $version): void;
}
