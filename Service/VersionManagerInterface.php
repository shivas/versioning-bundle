<?php
declare(strict_types=1);

namespace Shivas\VersioningBundle\Service;

use RuntimeException;
use Shivas\VersioningBundle\Formatter\FormatterInterface;
use Shivas\VersioningBundle\Provider\ProviderInterface;
use Version\Version;

interface VersionManagerInterface
{
    /**
     * Register a provider
     *
     * @param ProviderInterface $provider
     * @param string            $alias
     * @param integer           $priority
     */
    public function addProvider(ProviderInterface $provider, string $alias, int $priority): void;

    /**
     * Returns array of registered providers
     *
     * @return array<string, array{'provider': ProviderInterface, 'priority': int, 'alias': string}>
     */
    public function getProviders(): array;

    /**
     * Returns the active provider
     *
     * @return ProviderInterface
     * @throws RuntimeException
     */
    public function getActiveProvider(): ProviderInterface;

    /**
     * Write a new version number to the cache and storage
     *
     * @param  Version $version
     */
    public function writeVersion(Version $version): void;

    /**
     * Get the current application version from the cache or the active provider
     *
     * @return Version
     * @throws RuntimeException
     */
    public function getVersion(): Version;

    /**
     * Get the version from the active provider
     *
     * @return Version
     * @throws RuntimeException
     */
    public function getVersionFromProvider(): Version;

    /**
     * Get the formatter
     *
     * @return FormatterInterface
     */
    public function getFormatter(): FormatterInterface;
}
