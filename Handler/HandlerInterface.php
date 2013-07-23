<?php
namespace Shivas\VersioningBundle\Handler;

use Herrera\Version\Version;

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

