<?php
declare(strict_types=1);

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

        return $version->withPreRelease($this->formatPreRelease($preRelease->toString()));
    }

    private function formatPreRelease(string $preRelease): ?string
    {
        if (preg_match('/^(\d+)-g([a-fA-F0-9]{7,40})(-dirty)?$/', $preRelease, $matches)) {
            if ('0' !== $matches[1]) {
                return sprintf('dev.%s', $matches[2]);
            }

            return null;
        }

        if (preg_match('/^(.*)-(\d+)-g([a-fA-F0-9]{7,40})(-dirty)?$/', $preRelease, $matches)) {
            if ('0' !== $matches[2]) {
                // if we are not on TAG commit, add "dev" and git commit hash as pre release part
                if ('' === $matches[1]) {
                    return sprintf('dev.%s', $matches[3]);
                }

                return sprintf('%s.dev.%s', trim($matches[1], '-'), $matches[3]);
            }

            if ('' === $matches[1]) {
                return null;
            }

            return trim($matches[1], '-');
        }

        return $preRelease;
    }
}
