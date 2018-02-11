<?php

namespace Shivas\VersioningBundle\Command;

use Shivas\VersioningBundle\Service\VersionManager;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Version\Version;

class VersionBumpCommand extends Command
{
    /**
     * @var VersionManager
     */
    private $manager;

    /**
     * @var string
     */
    private $envDir;

    /**
     * Constructor
     *
     * @param VersionManager $manager
     * @param string         $envDir
     */
    public function __construct(VersionManager $manager, $envDir)
    {
        $this->manager = $manager;
        $this->envDir = $envDir;

        parent::__construct();
    }

    protected function configure()
    {
        $this
            ->setName('app:version:bump')
            ->setDescription(
                'Bump application version using one of the available providers'
            )
            ->addArgument('version', InputArgument::OPTIONAL, 'Version to set, should be compatible with Semantic versioning 2.0.0', null)
            ->addOption('dry-run', 'd', InputOption::VALUE_NONE, 'Dry run, does not update .env file')
            ->addOption('major', null, InputOption::VALUE_OPTIONAL, 'Bump MAJOR version by given number', 0)
            ->addOption('minor', null, InputOption::VALUE_OPTIONAL, 'Bump MINOR version by given number', 0)
            ->addOption('patch', null, InputOption::VALUE_OPTIONAL, 'Bump PATCH version by given number', 0)
            ->addOption('prerelease', null, InputOption::VALUE_IS_ARRAY | InputOption::VALUE_OPTIONAL, 'Add PRERELEASE to version', array())
            ->addOption('build', null, InputOption::VALUE_IS_ARRAY | InputOption::VALUE_OPTIONAL, 'Add BUILD to version', array());
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        if ($input->getArgument('version') === null) {
            $version = $this->manager->getVersion();

            $output->writeln(sprintf('Provider: <comment>%s</comment>', $this->manager->getActiveProvider()->getName()));

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

        $output->writeln(sprintf('Current version: <info>%s</info>', getenv('SHIVAS_APP_VERSION')));
        $output->writeln(sprintf('New version: <info>%s</info>', $version->getVersionString()));
        if ($input->getOption('dry-run')) {
            $output->writeln(sprintf('Dry run: <comment>%s</comment>', 'Skipping version bump'));
        } else {
            $this->setEnvironmentValue('SHIVAS_APP_VERSION', $version->getVersionString());
        }
    }

    /**
     * @param string $envKey
     * @param string $envValue
     */
    protected function setEnvironmentValue($envKey, $envValue)
    {
        $str = file_get_contents($this->envDir . '/.env');

        $oldValue = getenv('SHIVAS_APP_VERSION');
        $str = str_replace("{$envKey}={$oldValue}", "{$envKey}={$envValue}", $str);

        file_put_contents($this->envDir . '/.env', $str);
    }
}
