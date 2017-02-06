<?php

namespace Shivas\VersioningBundle\Twig;

use Herrera\Version\Dumper;
use Herrera\Version\Parser;
use Herrera\Version\Builder;
use Shivas\VersioningBundle\Service\VersionsManager;


class VersionExtension extends \Twig_Extension implements \Twig_Extension_GlobalsInterface
{

    /**
     * @var Builder
     */
    protected $builder;

    /**
     * @var VersionsManager
     */
    protected $versioningManager;

    /**
     * VersionExtension constructor.
     *
     * @param $versioningManager VersionsManager
     */
    public function __construct(VersionsManager $versioningManager)
    {
        $this->versioningManager = $versioningManager;
        $version = $versioningManager->getVersion();
        $this->builder = Parser::toBuilder(Dumper::toString($version));
    }

    public function getFunctions()
    {
        return array(
            new \Twig_SimpleFunction('semver_version', array($this, 'getVersion')),
            new \Twig_SimpleFunction('semver_version_gitlab', array($this, 'getVersionGitlab'), array('is_safe' => array('html'))),
            new \Twig_SimpleFunction('semver_major', array($this, 'getMajorVersion')),
            new \Twig_SimpleFunction('semver_minor', array($this, 'getMinorVersion')),
            new \Twig_SimpleFunction('semver_patch', array($this, 'getPatchVersion')),
            new \Twig_SimpleFunction('semver_pre_release', array($this, 'getPreReleaseVersion')),
            new \Twig_SimpleFunction('semver_build', array($this, 'getBuildVersion')),
            new \Twig_SimpleFunction('semver_build_linked', array($this, 'getLinkedBuildVersion'), array('is_safe' => array('html'))),
        );
    }

    public function getLinkedBuildVersion($urlBuild, $urlCommit, $prefixBuild='id-', $prefixCommit='sha-',  $length=8)
    {
        $buildParts = $this->builder->getBuild();
        $build = array();

        foreach ($buildParts as $buildPart) {
            // detect commit hash
            if (strlen($buildPart) >= 40) {
                if ($this->isSha1($buildPart)) {
                    $build[] = '<a href="'.$urlCommit.'/'.$buildPart.'">'.substr($buildPart, 0, $length).'</a>';
                    continue;
                }

                $build[] = '<a href="'.$urlCommit.'/'.str_replace($prefixCommit, "", $buildPart).'">'.$this->shortenSha1ContainingString($buildPart, $length).'</a>';
                continue;
            }

            // detect build id
            if (strpos($buildPart, $prefixBuild) !== false) {
                $buildId = substr($buildPart,3);
                $build[] = '<a href="'.$urlBuild.'/'.$buildId.'">'.$buildPart.'</a>';
                continue;
            }

            $build[] = $buildPart;
        }

        return implode('.',$build);
    }

    public function getVersion($short = true)
    {
        if ($short){
            return $this->getMajorVersion().'.'.$this->getMinorVersion().'.'.$this->getPatchVersion().'.'.$this->getPreReleaseVersion().'.'.$this->getBuildVersion($short);
        }

        return $this->builder->__toString();
    }

    public function getVersionGitlab($urlBuild, $urlCommit, $prefixBuild='id-', $prefixCommit='sha-')
    {
        return $this->getMajorVersion()
            .'.'.$this->getMinorVersion()
            .'.'.$this->getPatchVersion()
            .'.'.$this->getPreReleaseVersion()
            .'.'.$this->getLinkedBuildVersion($urlBuild, $urlCommit, $prefixBuild, $prefixCommit);
    }

    public function getMajorVersion()
    {
        return $this->builder->getMajor();
    }

    public function getMinorVersion()
    {
        return $this->builder->getMinor();
    }

    public function getPatchVersion()
    {
        return $this->builder->getPatch();
    }

    public function getPreReleaseVersion()
    {
        return implode('.', $this->builder->getPreRelease());
    }

    public function getBuildVersion($detectAndShortenGitHash=true, $length=8)
    {
        if ($detectAndShortenGitHash) {
            $build = $this->detectAndShortenGitHashFromBuild($length);
        } else {
            $build = $this->builder->getBuild();
        }

        return implode('.', $build);
    }

    protected function detectAndShortenGitHashFromBuild($length=8)
    {
        $buildParts = $this->builder->getBuild();
        $build = array();

        foreach ($buildParts as $buildPart) {
            if (strlen($buildPart)>=40) {
                if ($this->isSha1($buildPart)) {
                    $build[] = substr($buildPart, 0, $length);
                    continue;
                }

                $build[] = $this->shortenSha1ContainingString($buildPart, $length);
                continue;
            }

            $build[] = $buildPart;
        }

        return $build;
    }

    protected function isSha1($str)
    {
        return (bool) preg_match('/^[0-9a-f]{40}$/i', $str);
    }

    protected function shortenSha1ContainingString($string, $length=8)
    {
        $parts = explode('-', $string);
        $result = array();

        foreach ($parts as $part) {

            if (strlen($part) != 40) {
                $result[] = $part;
                continue;
            } else {
                if ($this->isSha1($part)) {
                    $result[] = substr($part, 0, $length);
                    continue;
                }
            }

            $result[] = $part;
        }

        return implode('-', $result);
    }

    public function getName()
    {
        return 'version_extension';
    }
}
