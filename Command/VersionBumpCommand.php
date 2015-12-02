<?php
namespace Shivas\VersioningBundle\Command;

use Herrera\Version\Dumper;
use Herrera\Version\Parser as VersionParser;
use Herrera\Version\Version;
use Shivas\VersioningBundle\Handler\HandlerInterface;
use Shivas\VersioningBundle\Service\VersionsManager;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Yaml\Parser;
use Symfony\Component\Yaml\Yaml;

class VersionBumpCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        $this
            ->setName('app:version:bump')
            ->setDescription(
                'Bumping of application version using one of available handlers'
            )
            ->addArgument('version', InputArgument::OPTIONAL, 'Version to set, should be compatible with Semantic versioning 2.0.0', null)
            ->addOption('dry-run', 'd', InputOption::VALUE_NONE, 'Dry run, not update parameters file, just print it')
            ->addOption('list-handlers', 'l', InputOption::VALUE_NONE, 'List registered version handlers')
            ->addOption('major', null, InputOption::VALUE_OPTIONAL, 'Bump MAJOR version by given number', 0)
            ->addOption('minor', null, InputOption::VALUE_OPTIONAL, 'Bump MINOR version by given number', 0)
            ->addOption('patch', null, InputOption::VALUE_OPTIONAL, 'Bump PATCH version by given number', 0)
            ->addOption('prerelease', null, InputOption::VALUE_IS_ARRAY | InputOption::VALUE_OPTIONAL, 'Add PRERELEASE to version', null)
            ->addOption('build', null, InputOption::VALUE_IS_ARRAY | InputOption::VALUE_OPTIONAL, 'Add BUILD to version', null);
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $kernelRoot = $this->getContainer()->getParameter('kernel.root_dir');
        $file       = $this->getContainer()->getParameter('shivas_versioning.version_file');
        $param      = $this->getContainer()->getParameter('shivas_versioning.version_parameter');
        $paramFile  = "{$kernelRoot}/config/{$file}";

        /** @var VersionsManager $manager */
        $manager = $this->getContainer()->get('shivas_versioning.manager');

        if ($input->getOption('list-handlers')) {
            $this->listHandlers($manager, $output);
            return;
        }

        if ($input->getArgument('version') === null) {

            $version = $manager->getVersion();

            if ($output->getVerbosity() >= OutputInterface::VERBOSITY_VERY_VERBOSE) {
                $output->writeln(sprintf('Handler: <comment>%s</comment>', $manager->getActiveHandler()->getName()));
            }

            $builder = VersionParser::toBuilder(Dumper::toString($version));


            if ($input->getOption('major') > 0) {
                $builder->incrementMajor(intval($input->getOption('major')));
            }

            if ($input->getOption('minor') > 0) {
                $builder->incrementMinor(intval($input->getOption('minor')));
            }

            if ($input->getOption('patch') > 0) {
                $builder->incrementPatch(intval($input->getOption('patch')));
            }

            if ($input->getOption('prerelease')) {
                $preRelease = $input->getOption('prerelease');
                if (in_array(null, $preRelease)) {
                    $preRelease = array();
                }

                $builder->setPreRelease($preRelease);
            }

            if ($input->getOption('build')) {
                $build = $input->getOption('build');
                if (in_array(null, $build)) {
                    $build = array();
                }

                $builder->setBuild($build);
            }

            $version = $builder->getVersion();

        } else {
            $version = VersionParser::toVersion($input->getArgument('version'));
        }

        if (!$input->getOption('dry-run')) {
            if ($output->getVerbosity() >= OutputInterface::VERBOSITY_VERBOSE) {
                $output->writeln(
                    sprintf(
                        'Updating parameters file with version number: <info>%s</info>',
                        Dumper::toString($version)
                    )
                );
            } else {
                $output->writeln(Dumper::toString($version));
            }

            if (!file_exists($paramFile)) {
                $this->createParametersFile($version, $paramFile, $param);
            } else {
                $this->updateParametersFile($version, $paramFile, $param);
            }
        } else {
            if ($output->getVerbosity() >= OutputInterface::VERBOSITY_VERBOSE) {
                $output->writeln(sprintf('Version: <comment>%s</comment>', Dumper::toString($version)));
            } else {
                $output->writeln(Dumper::toString($version));
            }
        }
    }

    /**
     * @param VersionsManager $manager
     * @param OutputInterface $output
     */
    protected function listHandlers(VersionsManager $manager, OutputInterface $output)
    {
        $output->writeln('Registered Version handlers');
        $handlers = $manager->getHandlers();
        $table = new Table($output);
        $table->setHeaders(array('Alias', 'Priority', 'Name', 'Supported'))
            ->setStyle('borderless');

        foreach ($handlers as $key => $handlerEntry) {
            /** @var $handler HandlerInterface */
            $handler = $handlerEntry['handler'];
            $supported = $handler->isSupported() ? 'Yes' : 'No';
            $table->addRow(array($key, $handlerEntry['priority'], $handler->getName(), $supported));
        }

        $table->render();
    }

    /**
     * @param Version $version
     * @param $file
     * @param $param
     */
    protected function createParametersFile(Version $version, $file, $param)
    {
        $params = array('parameters' => array($param => Dumper::toString($version)));
        file_put_contents($file, Yaml::dump($params));
    }

    /**
     * @param Version $version
     * @param $file
     * @param $param
     * @throws \RuntimeException
     */
    protected function updateParametersFile(Version $version, $file, $param)
    {
        $yamlParser = new Parser();

        $params = $yamlParser->parse(file_get_contents($file));
        if (!empty($params['parameters'])) {
            $params['parameters'][$param] = Dumper::toString($version);
            file_put_contents($file, Yaml::dump($params));
        } else {
            throw new \RuntimeException('Not valid parameters file?');
        }
    }
}

