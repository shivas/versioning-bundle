<?php

namespace Shivas\VersioningBundle\Service;

use RuntimeException;
use Shivas\VersioningBundle\Provider\ProviderInterface;
use Version\Exception\InvalidVersionStringException;
use Version\Version;

/**
 * Class VersionsManager
 */
class VersionsManager
{
    /**
     * @var array
     */
    private $providers;

    /**
     * Active provider
     *
     * @var array
     */
    private $activeProvider;

    /**
     * Constructor
     */
    public function __construct()
    {
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
        $version = $this->getVersionFromProvider($provider);

        return $version;
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

    /**
     * @param   ProviderInterface $provider
     * @return  Version
     * @throws  RuntimeException
     */
    protected function getVersionFromProvider($provider)
    {
        try {
            $versionString = $provider->getVersion();
            // remove the version prefix
            if (substr(strtolower($versionString), 0, 1) == 'v') {
                $versionString = substr($versionString, 1);
            }

            $version = Version::fromString($versionString);
            if (preg_match('/^(\d+)-g([a-fA-F0-9]{7,40})(-dirty)?$/', $version->getPreRelease(), $matches)) {
                if ((int) $matches[1] != 0) {
                    // we are not on TAG commit, add "dev" and git commit hash as pre release part
                    $version = $version->withPreRelease(array('dev', $matches[2]));
                } else {
                    $version = $version->withPreRelease(array());
                }
            }

            if (preg_match('/^(.*)-(\d+)-g([a-fA-F0-9]{7,40})(-dirty)?$/', $version->getPreRelease(), $matches)) {
                if ((int) $matches[2] != 0) {
                    // we are not on TAG commit, add "dev" and git commit hash as pre release part
                    if (empty($matches[1])) {
                        $version = $version->withPreRelease(array('dev', $matches[3]));
                    } else {
                        $version = $version->withPreRelease(array_merge(explode('.', trim($matches[1], '-')), array('dev', $matches[3])));
                    }
                } else {
                    if (empty($matches[1])) {
                        $version = $version->withPreRelease(array());
                    } else {
                        $version = $version->withPreRelease(trim($matches[1], '-'));
                    }
                }
            }

            return $version;
        } catch (InvalidVersionStringException $e) {
            throw new RuntimeException($provider->getName() . ' returned no valid version');
        }
    }
}
