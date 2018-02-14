<?php

namespace Shivas\VersioningBundle\Service;

use RuntimeException;
use Shivas\VersioningBundle\Formatter\FormatterInterface;
use Shivas\VersioningBundle\Provider\ProviderInterface;
use Version\Exception\InvalidVersionStringException;
use Version\Version;

/**
 * Class VersionManager
 */
class VersionManager
{
    /**
     * @var FormatterInterface
     */
    private $formatter;

    /**
     * @var array
     */
    private $providers;

    /**
     * @var array
     */
    private $activeProvider;

    /**
     * Constructor
     *
     * @param FormatterInterface $formatter
     */
    public function __construct(FormatterInterface $formatter = null)
    {
        $this->formatter = $formatter;
        $this->providers = array();
        $this->activeProvider = null;
    }

    /**
     * @param ProviderInterface $provider
     * @param string            $alias
     * @param integer           $priority
     */
    public function addProvider(ProviderInterface $provider, $alias, $priority)
    {
        $this->providers[$alias] = array(
            'provider' => $provider,
            'priority' => $priority,
            'alias' => $alias
        );

        // sort providers by priority
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
     * @return Version
     */
    public function getVersion()
    {
        $provider = $this->getSupportedProvider();

        try {
            $versionString = $provider->getVersion();
            if (substr(strtolower($versionString), 0, 1) == 'v') {
                $versionString = substr($versionString, 1);
            }

            $version = Version::fromString($versionString);
            if (null !== $this->formatter) {
                $version = $this->formatter->format($version);
            }

            return $version;
        } catch (InvalidVersionStringException $e) {
            throw new RuntimeException($provider->getName() . ' returned no valid version');
        }
    }

    /**
     * @return ProviderInterface
     */
    public function getActiveProvider()
    {
        return $this->activeProvider['provider'];
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
     * @return ProviderInterface
     * @throws RuntimeException
     */
    public function getSupportedProvider()
    {
        if (empty($this->providers)) {
            throw new RuntimeException('No versioning provider found');
        }

        foreach ($this->providers as $entry) {
            $provider = $entry['provider'];
            /** @var $provider ProviderInterface */
            if ($provider->isSupported()) {
                $this->activeProvider = $entry;

                return $provider;
            }
        }

        throw new RuntimeException('No supported versioning providers found');
    }
}
