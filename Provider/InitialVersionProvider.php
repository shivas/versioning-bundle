<?php

namespace Shivas\VersioningBundle\Provider;

/**
 * Class InitialVersionProvider
 *
 * Fallback provider to get initial version, later Parameters or Git providers should be able to take over
 */
class InitialVersionProvider implements ProviderInterface
{
    /**
     * @return boolean
     */
    public function isSupported()
    {
        return true;
    }

    /**
     * @return string
     */
    public function getVersion()
    {
        return '0.1.0';
    }
}
