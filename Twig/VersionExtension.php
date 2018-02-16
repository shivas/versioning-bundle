<?php

namespace Shivas\VersioningBundle\Twig;

use Twig_Extension;
use Twig_Extension_GlobalsInterface;

/**
 * Class VersionExtension
 */
class VersionExtension extends Twig_Extension implements Twig_Extension_GlobalsInterface
{
    /**
     * @return array
     */
    public function getGlobals()
    {
        return [
            'shivas_app_version' => getenv('SHIVAS_APP_VERSION'),
        ];
    }
}
