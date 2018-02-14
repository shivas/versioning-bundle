<?php

namespace Shivas\VersioningBundle\Provider;

/**
 * Interface ProviderInterface
 */
interface ProviderInterface
{
    /**
     * @return boolean
     */
    public function isSupported();

    /**
     * @return string
     */
    public function getVersion();
}
