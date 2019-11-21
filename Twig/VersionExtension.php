<?php

namespace Shivas\VersioningBundle\Twig;

use Shivas\VersioningBundle\Service\VersionManager;
use Twig\Extension\AbstractExtension;
use Twig\Extension\GlobalsInterface;

/**
 * Class VersionExtension
 */
class VersionExtension extends AbstractExtension implements GlobalsInterface
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
    public function getGlobals(): array
    {
        return [
            'shivas_app_version' => (string) $this->manager->getVersion(),
        ];
    }
}
