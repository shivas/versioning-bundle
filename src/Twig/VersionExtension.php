<?php
declare(strict_types=1);

namespace Shivas\VersioningBundle\Twig;

use Shivas\VersioningBundle\Service\VersionManagerInterface;
use Twig\Extension\AbstractExtension;
use Twig\Extension\GlobalsInterface;

/**
 * Class VersionExtension
 */
final class VersionExtension extends AbstractExtension implements GlobalsInterface
{
    /**
     * @var VersionManagerInterface
     */
    protected $manager;

    public function __construct(VersionManagerInterface $manager)
    {
        $this->manager = $manager;
    }

    /**
     * @return array{'shivas_app_version': string}
     */
    public function getGlobals(): array
    {
        return [
            'shivas_app_version' => (string) $this->manager->getVersion(),
        ];
    }
}
