<?php

namespace Shivas\VersioningBundle\Service;

use RuntimeException;
use Shivas\VersioningBundle\Formatter\FormatterInterface;
use Shivas\VersioningBundle\Provider\ProviderInterface;
use Shivas\VersioningBundle\Writer\WriterInterface;
use Symfony\Component\Cache\Adapter\AdapterInterface;
use Version\Exception\InvalidVersionStringException;
use Version\Version;

/**
 * Class VersionManager
 */
class VersionManager
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
     * @var array
     */
    private $providers = [];

    /**
     * Constructor
     *
     * @param AdapterInterface   $cache
     * @param WriterInterface    $writer
     * @param FormatterInterface $formatter
     */
    public function __construct(AdapterInterface $cache, WriterInterface $writer, FormatterInterface $formatter = null)
    {
        $this->cache = $cache;
        $this->writer = $writer;
        $this->formatter = $formatter;
    }

    /**
     * Register a provider
     *
     * @param ProviderInterface $provider
     * @param string            $alias
     * @param integer           $priority
     */
    public function addProvider(ProviderInterface $provider, $alias, $priority)
    {
        $this->providers[$alias] = array(
            'provider' => $provider,
            'priority' => $priority,
            'alias' => $alias,
        );

        uasort(
            $this->providers,
            function ($a, $b) {
                if ($a['priority'] == $b['priority']) {
                    return 0;
                }

                return $a['priority'] < $b['priority'] ? 1 : -1;
            }
        );
    }

    /**
     * Returns array of registered providers
     *
     * @return array
     */
    public function getProviders()
    {
        return $this->providers;
    }

    /**
     * Returns the active provider
     *
     * @return ProviderInterface
     * @throws RuntimeException
     */
    public function getActiveProvider()
    {
        if ($this->activeProvider instanceof ProviderInterface) {
            return $this->activeProvider;
        }

        if (empty($this->providers)) {
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

    /**
     * Write a new version number to the cache and storage
     *
     * @param Version $version
     */
    public function writeVersion(Version $version)
    {
        $cacheItem = $this->cache->getItem('version');
        $cacheItem->set($version);

        $this->cache->save($cacheItem);
        $this->writer->write($version);
    }

    /**
     * Get the current application version
     *
     * @return Version
     */
    public function getVersion()
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

    /**
     * Get the version from the active provider
     *
     * @return Version
     * @throws RuntimeException
     */
    public function getVersionFromProvider()
    {
        $provider = $this->getActiveProvider();

        try {
            $versionString = $provider->getVersion();
            if (substr(strtolower($versionString), 0, 1) == 'v') {
                $versionString = substr($versionString, 1);
            }

            $version = Version::fromString($versionString);
            if ($this->formatter instanceof FormatterInterface) {
                $version = $this->formatter->format($version);
            }

            return $version;
        } catch (InvalidVersionStringException $e) {
            throw new RuntimeException(get_class($provider) . ' returned an invalid version');
        }
    }

    /**
     * Get the formatter
     *
     * @return FormatterInterface|null
     */
    public function getFormatter()
    {
        return $this->formatter;
    }
}
