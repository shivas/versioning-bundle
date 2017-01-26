<?php
namespace Shivas\VersioningBundle\Handler;

use Herrera\Version\Version;
use Herrera\Version\Parser as VersionParser;
use Symfony\Component\Yaml\Parser;

class ParameterHandler implements HandlerInterface
{
    /**
     * Kernel root path
     *
     * @var string
     */
    private $path;

    /**
     * @var string
     */
    private $versionParameter;

    /**
     * @var string
     */
    protected $versionFile;

    public function __construct($path, $versionParameter, $versionFile)
    {
        $this->path = $path;
        $this->versionParameter = $versionParameter;
        $this->versionFile = $versionFile;
    }

    /**
     * @return boolean
     */
    public function isSupported()
    {
        $parameters = $this->readParametersFile();
        return isset($parameters['parameters'][$this->versionParameter]);
    }

    /**
     * @return Version
     */
    public function getVersion()
    {
        $parameters = $this->readParametersFile();
        return VersionParser::toVersion($parameters['parameters'][$this->versionParameter]);
    }

    /**
     * @return string
     */
    public function getName()
    {
        return "parameters.yml file version handler";
    }

    /**
     * @return array
     */
    private function readParametersFile()
    {
        $parametersFile = $this->path . '/config//'.$this->versionFile;
        if (!is_file($parametersFile)) {
            return array('parameters');
        }
        $yamlParser = new Parser();
        $parameters = $yamlParser->parse(file_get_contents($parametersFile));
        return $parameters;
    }
}

