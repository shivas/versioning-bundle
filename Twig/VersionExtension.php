<?php

namespace Shivas\VersioningBundle\Twig;

use Shivas\VersioningBundle\Service\VersionManager;
use Twig_Extension;
use Twig\Extension\GlobalsInterface;

/**
 * Class VersionExtension
 */
class VersionExtension extends Twig_Extension implements GlobalsInterface
{
    /**
     * @var VersionManager
     */
    protected $manager;

    public function __construct(VersionManager $manager)
    {
        $this->manager = $manager;
    }

    /**
     * @return array
     */
    public function getGlobals()
    {
        return [
            'shivas_app_version' => (string) $this->manager->getVersion(),
        ];
    }
}
