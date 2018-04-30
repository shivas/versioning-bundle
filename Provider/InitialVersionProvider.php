<?php

namespace Shivas\VersioningBundle\Provider;

/**
 * Class InitialVersionProvider
 *
 * Fallback provider to get initial version
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
