<?php

namespace Shivas\VersioningBundle\Formatter;

use Version\Extension\PreRelease;
use Version\Version;

/**
 * Class GitDescribeFormatter
 */
class GitDescribeFormatter implements FormatterInterface
{
    /**
     * Remove hash on tag commit else put dev.hash in prerelease
     *
     * @param  Version $version
     * @return Version
     */
    public function format(Version $version): Version
    {
        $preRelease = $version->getPreRelease();
        if (!$preRelease instanceof PreRelease) {
            return $version;
        }

        if (preg_match('/^(\d+)-g([a-fA-F0-9]{7,40})(-dirty)?$/', $preRelease->toString(), $matches)) {
            $withPreRelease = (int) $matches[1] != 0 ? sprintf('dev.%s',  $matches[2]) : null;
            $version = $version->withPreRelease($withPreRelease);
        }

        if (preg_match('/^(.*)-(\d+)-g([a-fA-F0-9]{7,40})(-dirty)?$/', $preRelease->toString(), $matches)) {
            if ((int) $matches[2] != 0) {
                // if we are not on TAG commit, add "dev" and git commit hash as pre release part
                $withPreRelease = empty($matches[1]) ? sprintf('dev.%s',  $matches[3]) : sprintf('%s.dev.%s', trim($matches[1], '-'),  $matches[3]);
                $version = $version->withPreRelease($withPreRelease);
            } else {
                $withPreRelease = empty($matches[1]) ? null : trim($matches[1], '-');
                $version = $version->withPreRelease($withPreRelease);
            }
        }

        return $version;
    }
}
