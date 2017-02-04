<?php

namespace Shivas\VersioningBundle\Service;

use RuntimeException;
use Shivas\VersioningBundle\Handler\HandlerInterface;
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
    private $handlers;

    /**
     * Active handler entry
     *
     * @var array
     */
    private $activeHandler;

    /**
     * Constructor
     */
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
     * @return Version
     */
    public function getVersion()
    {
        $handler = $this->getSupportedHandler();
        $version = $this->getVersionFromHandler($handler);

        return $version;
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
     * @throws RuntimeException
     */
    public function getSupportedHandler()
    {
        if (empty($this->handlers)) {
            throw new RuntimeException('No supported versioning handlers found');
        }

        foreach ($this->handlers as $entry) {
            $handler = $entry['handler'];
            /** @var $handler HandlerInterface */
            if ($handler->isSupported()) {
                $this->activeHandler = $entry;
                return $handler;
            }
        }

        throw new RuntimeException(
            "No valid versioning handlers found, all handlers can't provide version information"
        );
    }

    /**
     * @param   HandlerInterface $handler
     * @return  Version
     * @throws  RuntimeException
     */
    protected function getVersionFromHandler($handler)
    {
        try {
            $version = $handler->getVersion();
            // remove the version prefix
            if (substr(strtolower($version), 0, 1) == 'v') {
                $version = substr($version, 1);
            }

            $version = Version::fromString($version);
            if (preg_match('/^(?:(.*)-?(\d+)-g)?([a-fA-F0-9]{7,40})(-dirty)?$/', $version->getPreRelease(), $matches)) {
                if ((int) $matches[2] != 0) {
                    // we are not on TAG commit, add "dev" and git commit hash as pre release part
                    if (empty($matches[1])) {
                        $version = $version->withPreRelease(array('dev', $matches[3]));
                    } else {
                        $version = $version->withPreRelease(array_merge(explode('.', trim($matches[1], '-')), array('dev', $matches[3])));
                    }
                }
            }

            return $version;
        } catch (InvalidVersionStringException $e) {
            throw new RuntimeException($handler->getName() . " describe returned no valid version");
        }
    }
}
