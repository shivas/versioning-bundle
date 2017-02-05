<?php

namespace Shivas\VersioningBundle\Provider;

use Symfony\Component\Yaml\Parser;

/**
 * Class ParameterProvider
 */
class ParameterProvider implements ProviderInterface
{
    /**
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
    private $versionFile;

    /**
     * @param string $path
     * @param string $versionParameter
     * @param string $versionFile
     */
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
     * @return string
     */
    public function getVersion()
    {
        $parameters = $this->readParametersFile();
        $version = $parameters['parameters'][$this->versionParameter];

        return $version;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return "parameters.yml file version provider";
    }

    /**
     * @return array
     */
    private function readParametersFile()
    {
        $parametersFile = sprintf('%s/config/%s', $this->path, $this->versionFile);
        if (!is_file($parametersFile)) {
            return array('parameters');
        }

        $yamlParser = new Parser();
        $parameters = $yamlParser->parse(file_get_contents($parametersFile));

        return $parameters;
    }
}
