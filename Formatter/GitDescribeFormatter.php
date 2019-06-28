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
    public function format(Version $version)
    {
        if (preg_match('/^(\d+)-g([a-fA-F0-9]{7,40})(-dirty)?$/', $version->getPreRelease(), $matches)) {
            if ((int) $matches[1] != 0) {
                // we are not on TAG commit, add "dev" and git commit hash as pre release part
                $version = $version->withPreRelease('dev.' . $matches[2]);
            } else {
                $version = $this->clearPreRelease($version);
            }
        }

        if (preg_match('/^(.*)-(\d+)-g([a-fA-F0-9]{7,40})(-dirty)?$/', $version->getPreRelease(), $matches)) {
            if ((int) $matches[2] != 0) {
                // we are not on TAG commit, add "dev" and git commit hash as pre release part
                if (empty($matches[1])) {
                    $version = $version->withPreRelease('dev.' . $matches[3]);
                } else {
                    $version = $version->withPreRelease(trim($matches[1], '-') . '.dev.' . $matches[3]);
                }
            } else {
                if (empty($matches[1])) {
                    $version = $this->clearPreRelease($version);
                } else {
                    $version = $version->withPreRelease(trim($matches[1], '-'));
                }
            }
        }

        return $version;
    }

    private function clearPreRelease(Version $version)
    {
        if (class_exists(\Version\Metadata\PreRelease::class)) {
            // we cannot use null with nikolaposa/version 2.2
            return $version->withPreRelease(\Version\Metadata\PreRelease::createEmpty());
        } else {
            return $version->withPreRelease(null);
        }
    }
}
