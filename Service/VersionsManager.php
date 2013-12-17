<?php

namespace Shivas\VersioningBundle\Service;

use Shivas\VersioningBundle\Handler\HandlerInterface;

class VersionsManager
{
    /**
     * @var array
     */
    private $handlers;

    /**
     * Active handler entry
     *
     * @var array
     */
    private $activeHandler;

    public function __construct()
    {
        $this->handlers = array();
        $this->activeHandler = null;
    }


    /**
     * @param HandlerInterface $handler
     * @param $alias
     * @param $priority
     */
    public function addHandler(HandlerInterface $handler, $alias, $priority)
    {
        $this->handlers[$alias] = array(
            'handler' => $handler,
            'priority' => $priority,
            'alias' => $alias
        );

        // sort handlers by priority
        uasort(
            $this->handlers,
            function ($a, $b) {
                if ($a['priority'] == $b['priority']) {
                    return 0;
                }

                return $a['priority'] < $b['priority'] ? 1 : -1;
            }
        );
    }

    /**
     * @return \Herrera\Version\Version
     */
    public function getVersion()
    {
        $handler = $this->getSupportedHandler();
        return $handler->getVersion();
    }

    /**
     * @return HandlerInterface
     */
    public function getActiveHandler()
    {
        return $this->activeHandler['handler'];
    }

    /**
     * Returns array of registered handlers
     *
     * @return array
     */
    public function getHandlers()
    {
        return $this->handlers;
    }

    /**
     * @return HandlerInterface
     * @throws \RuntimeException
     */
    public function getSupportedHandler()
    {
        if (empty($this->handlers)) {
            throw new \RuntimeException('No supported versioning handlers found');
        }

        foreach ($this->handlers as $entry) {
            $handler = $entry['handler'];
            /** @var $handler HandlerInterface */
            if ($handler->isSupported()) {
                $this->activeHandler = $entry;
                return $handler;
            }
        }

        throw new \RuntimeException(
            "No valid versioning handlers found, all handlers can't provide version information"
        );
    }
}

