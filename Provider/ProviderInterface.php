<?php
declare(strict_types=1);

namespace Shivas\VersioningBundle\Provider;

/**
 * Interface ProviderInterface
 */
interface ProviderInterface
{
    public function isSupported(): bool;

    public function getVersion(): string;
}
