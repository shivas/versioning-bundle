<?php

namespace Shivas\VersioningBundle\Formatter;

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
        if (null === $preRelease) {
            return $version;
        }

        if (preg_match('/^(\d+)-g([a-fA-F0-9]{7,40})(-dirty)?$/', $preRelease->toString(), $matches)) {
            if ('0' !== $matches[1]) {
                $withPreRelease = sprintf('dev.%s', $matches[2]);
            } else {
                $withPreRelease = null;
            }

            $version = $version->withPreRelease($withPreRelease);
        } elseif (preg_match('/^(.*)-(\d+)-g([a-fA-F0-9]{7,40})(-dirty)?$/', $preRelease->toString(), $matches)) {
            if ('0' !== $matches[2]) {
                // if we are not on TAG commit, add "dev" and git commit hash as pre release part
                if (empty($matches[1])) {
                    $withPreRelease = sprintf('dev.%s', $matches[3]);
                } else {
                    $withPreRelease = sprintf('%s.dev.%s', trim($matches[1], '-'), $matches[3]);
                }

            } else {
                if ('' === $matches[1]) {
                    $withPreRelease = null;
                } else {
                    $withPreRelease = trim($matches[1], '-');
                }
            }

            $version = $version->withPreRelease($withPreRelease);
        }

        return $version;
    }
}
