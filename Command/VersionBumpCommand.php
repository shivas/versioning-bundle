<?php

namespace Shivas\VersioningBundle\Command;

use RuntimeException;
use Shivas\VersioningBundle\Provider\ProviderInterface;
use Shivas\VersioningBundle\Service\VersionsManager;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Yaml\Parser;
use Symfony\Component\Yaml\Yaml;
use Version\Version;

class VersionBumpCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        $this
            ->setName('app:version:bump')
            ->setDescription(
                'Bumping of application version using one of the available providers'
            )
            ->addArgument('version', InputArgument::OPTIONAL, 'Version to set, should be compatible with Semantic versioning 2.0.0', null)
            ->addOption('dry-run', 'd', InputOption::VALUE_NONE, 'Dry run, not update parameters file, just print it')
            ->addOption('list-providers', 'l', InputOption::VALUE_NONE, 'List registered version providers')
            ->addOption('major', null, InputOption::VALUE_OPTIONAL, 'Bump MAJOR version by given number', 0)
            ->addOption('minor', null, InputOption::VALUE_OPTIONAL, 'Bump MINOR version by given number', 0)
            ->addOption('patch', null, InputOption::VALUE_OPTIONAL, 'Bump PATCH version by given number', 0)
            ->addOption('prerelease', null, InputOption::VALUE_IS_ARRAY | InputOption::VALUE_OPTIONAL, 'Add PRERELEASE to version', array())
            ->addOption('build', null, InputOption::VALUE_IS_ARRAY | InputOption::VALUE_OPTIONAL, 'Add BUILD to version', array());
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $projectDir = $this->getContainer()->getParameter('kernel.project_dir');
        $file       = $this->getContainer()->getParameter('shivas_versioning.version_file');
        $param      = $this->getContainer()->getParameter('shivas_versioning.version_parameter');
        $paramFile  = "{$projectDir}/app/config/{$file}";

        /** @var VersionsManager $manager */
        $manager = $this->getContainer()->get('shivas_versioning.manager');

        if ($input->getOption('list-providers')) {
            $this->listProviders($manager, $output);
            return;
        }

        if ($input->getArgument('version') === null) {
            $version = $manager->getVersion();

            if ($output->getVerbosity() >= OutputInterface::VERBOSITY_VERY_VERBOSE) {
                $output->writeln(sprintf('Provider: <comment>%s</comment>', $manager->getActiveProvider()->getName()));
            }

            $incrementMajor = (int) $input->getOption('major');
            if ($incrementMajor > 0) {
                for ($i = 0; $i < $incrementMajor; $i++) {
                    $version = $version->withMajorIncremented();
                }
            }

            $incrementMinor = (int) $input->getOption('minor');
            if ($incrementMinor > 0) {
                for ($i = 0; $i < $incrementMinor; $i++) {
                    $version = $version->withMinorIncremented();
                }
            }

            $incrementPatch = (int) $input->getOption('patch');
            if ($incrementPatch > 0) {
                for ($i = 0; $i < $incrementPatch; $i++) {
                    $version = $version->withPatchIncremented();
                }
            }

            $preRelease = $input->getOption('prerelease');
            if (!empty($preRelease)) {
                if (in_array(null, $preRelease)) {
                    $preRelease = array();
                }

                $version = $version->withPreRelease($preRelease);
            }

            $build = $input->getOption('build');
            if (!empty($build)) {
                if (in_array(null, $build)) {
                    $build = array();
                }

                $version = $version->withBuild($build);
            }
        } else {
            $version = Version::fromString($input->getArgument('version'));
        }

        if (!$input->getOption('dry-run')) {
            if ($output->getVerbosity() >= OutputInterface::VERBOSITY_VERBOSE) {
                $output->writeln(
                    sprintf(
                        'Updating parameters file with version number: <info>%s</info>',
                        $version->getVersionString()
                    )
                );
            } else {
                $output->writeln($version->getVersionString());
            }

            if (!file_exists($paramFile)) {
                $this->createParametersFile($version, $paramFile, $param);
            } else {
                $this->updateParametersFile($version, $paramFile, $param);
            }
        } else {
            if ($output->getVerbosity() >= OutputInterface::VERBOSITY_VERBOSE) {
                $output->writeln(sprintf('Version: <comment>%s</comment>', $version->getVersionString()));
            } else {
                $output->writeln($version->getVersionString());
            }
        }
    }

    /**
     * @param VersionsManager $manager
     * @param OutputInterface $output
     */
    protected function listProviders(VersionsManager $manager, OutputInterface $output)
    {
        $output->writeln('Registered Version providers');
        $providers = $manager->getProviders();
        $table = new Table($output);
        $table->setHeaders(array('Alias', 'Priority', 'Name', 'Supported'))
            ->setStyle('borderless');

        foreach ($providers as $key => $providerEntry) {
            /** @var $provider ProviderInterface */
            $provider = $providerEntry['provider'];
            $supported = $provider->isSupported() ? 'Yes' : 'No';
            $table->addRow(array($key, $providerEntry['priority'], $provider->getName(), $supported));
        }

        $table->render();
    }

    /**
     * @param Version   $version
     * @param string    $file
     * @param string    $param
     */
    protected function createParametersFile(Version $version, $file, $param)
    {
        $params = array('parameters' => array($param => $version->getVersionString()));
        file_put_contents($file, Yaml::dump($params));
    }

    /**
     * @param   Version $version
     * @param   string  $file
     * @param   string  $param
     * @throws  RuntimeException
     */
    protected function updateParametersFile(Version $version, $file, $param)
    {
        $yamlParser = new Parser();

        $params = $yamlParser->parse(file_get_contents($file));
        if (!empty($params['parameters'])) {
            $params['parameters'][$param] = $version->getVersionString();
            file_put_contents($file, Yaml::dump($params));
        } else {
            throw new RuntimeException('Not valid parameters file?');
        }
    }
}
