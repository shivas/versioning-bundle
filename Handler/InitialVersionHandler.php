<?php

namespace Shivas\VersioningBundle\Handler;

/**
 * Class InitialVersionHandler
 *
 * Fallback handler to get initial version, later Parameters or Git handlers should be able to take over
 */
class InitialVersionHandler implements HandlerInterface
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

    /**
     * @return string
     */
    public function getName()
    {
        return 'Initial version (0.1.0) handler';
    }
}
