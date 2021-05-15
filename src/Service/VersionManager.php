<?php
declare(strict_types=1);

namespace Shivas\VersioningBundle\Service;

use RuntimeException;
use Shivas\VersioningBundle\Formatter\FormatterInterface;
use Shivas\VersioningBundle\Provider\ProviderInterface;
use Shivas\VersioningBundle\Writer\WriterInterface;
use Symfony\Component\Cache\Adapter\AdapterInterface;
use Version\Exception\InvalidVersionString;
use Version\Version;

/**
 * Class VersionManager
 */
class VersionManager implements VersionManagerInterface
{
    /**
     * @var AdapterInterface
     */
    private $cache;

    /**
     * @var WriterInterface
     */
    private $writer;

    /**
     * @var FormatterInterface
     */
    private $formatter;

    /**
     * @var ProviderInterface
     */
    private $activeProvider;

    /**
     * @var array<string, array{'provider': ProviderInterface, 'priority': int, 'alias': string}>
     */
    private $providers = [];

    public function __construct(AdapterInterface $cache, WriterInterface $writer, FormatterInterface $formatter)
    {
        $this->cache = $cache;
        $this->writer = $writer;
        $this->formatter = $formatter;
    }

    public function addProvider(ProviderInterface $provider, string $alias, int $priority): void
    {
        $this->providers[$alias] = [
            'provider' => $provider,
            'priority' => $priority,
            'alias' => $alias,
        ];

        uasort(
            $this->providers,
            function (array $a, array $b): int {
                if ($a['priority'] === $b['priority']) {
                    return 0;
                }

                return $a['priority'] < $b['priority'] ? 1 : -1;
            }
        );
    }

    public function getProviders(): array
    {
        return $this->providers;
    }

    public function getActiveProvider(): ProviderInterface
    {
        if (null !== $this->activeProvider) {
            return $this->activeProvider;
        }

        if ([] === $this->providers) {
            throw new RuntimeException('No versioning provider found');
        }

        foreach ($this->providers as $entry) {
            $provider = $entry['provider'];
            /** @var $provider ProviderInterface */
            if ($provider->isSupported()) {
                $this->activeProvider = $provider;

                return $provider;
            }
        }

        throw new RuntimeException('No supported versioning providers found');
    }

    public function writeVersion(Version $version): void
    {
        $cacheItem = $this->cache->getItem('version');
        $cacheItem->set($version);

        $this->cache->save($cacheItem);
        $this->writer->write($version);
    }

    public function getVersion(): Version
    {
        $cacheItem = $this->cache->getItem('version');
        if ($cacheItem->isHit()) {
            return $cacheItem->get();
        } else {
            $version = $this->getVersionFromProvider();
            $cacheItem->set($version);
            $this->cache->save($cacheItem);

            return $version;
        }
    }

    public function getVersionFromProvider(): Version
    {
        $provider = $this->getActiveProvider();

        try {
            $versionString = $provider->getVersion();
            if (substr(strtolower($versionString), 0, 1) == 'v') {
                $versionString = substr($versionString, 1);
            }

            $version = Version::fromString($versionString);

            return $this->formatter->format($version);
        } catch (InvalidVersionString $e) {
            throw new RuntimeException(get_class($provider) . ' returned an invalid version');
        }
    }

    public function getFormatter(): FormatterInterface
    {
        return $this->formatter;
    }
}
