<?php

namespace Shivas\VersioningBundle\Handler;

use Version\Version;

interface HandlerInterface
{
    /**
     * @return boolean
     */
    public function isSupported();

    /**
     * @return Version
     */
    public function getVersion();

    /**
     * @return string
     */
    public function getName();
}
