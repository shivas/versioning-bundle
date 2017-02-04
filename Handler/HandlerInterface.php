<?php

namespace Shivas\VersioningBundle\Handler;

/**
 * Interface HandlerInterface
 */
interface HandlerInterface
{
    /**
     * @return boolean
     */
    public function isSupported();

    /**
     * @return string
     */
    public function getVersion();

    /**
     * @return string
     */
    public function getName();
}
