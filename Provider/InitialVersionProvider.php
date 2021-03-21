<?php
declare(strict_types=1);

namespace Shivas\VersioningBundle\Provider;

/**
 * Class InitialVersionProvider
 *
 * Fallback provider to get initial version
 */
class InitialVersionProvider implements ProviderInterface
{
    public function isSupported(): bool
    {
        return true;
    }

    public function getVersion(): string
    {
        return '0.1.0';
    }
}
