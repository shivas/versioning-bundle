<?php

namespace Shivas\VersioningBundle\Command;

use Shivas\VersioningBundle\Formatter\FormatterInterface;
use Shivas\VersioningBundle\Service\VersionManager;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Version\Version;

/**
 * Class VersionBumpCommand
 */
class VersionBumpCommand extends Command
{
    /**
     * @var VersionManager
     */
    private $manager;

    /**
     * Constructor
     *
     * @param VersionManager $manager
     */
    public function __construct(VersionManager $manager)
    {
        $this->manager = $manager;

        parent::__construct();
    }

    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this
            ->setName('app:version:bump')
            ->setDescription('Manually bump the application version')
            ->addArgument('version', InputArgument::OPTIONAL, 'Version to set, should be compatible with Semantic versioning 2.0.0', null)
            ->addOption('dry-run', 'd', InputOption::VALUE_NONE, 'Dry run, does not update VERSION file')
            ->addOption('major', null, InputOption::VALUE_OPTIONAL, 'Bump MAJOR version by given number', 0)
            ->addOption('minor', null, InputOption::VALUE_OPTIONAL, 'Bump MINOR version by given number', 0)
            ->addOption('patch', null, InputOption::VALUE_OPTIONAL, 'Bump PATCH version by given number', 0)
            ->addOption('prerelease', null, InputOption::VALUE_IS_ARRAY | InputOption::VALUE_OPTIONAL, 'Add PRERELEASE to version', array())
            ->addOption('build', null, InputOption::VALUE_IS_ARRAY | InputOption::VALUE_OPTIONAL, 'Add BUILD to version', array());
    }

    /**
     * Manually bump the application version
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        if ($input->getArgument('version') === null) {
            $version = $this->manager->getVersionFromProvider();
            $output->writeln(sprintf('Provider: <comment>%s</comment>', get_class($this->manager->getActiveProvider())));

            $formatter = $this->manager->getFormatter();
            if ($formatter instanceof FormatterInterface) {
                $output->writeln(sprintf('Formatter: <comment>%s</comment>', get_class($formatter)));
            } else {
                $output->writeln(sprintf('Formatter: <comment>%s</comment>', 'None'));
            }
        } else {
            $version = Version::fromString($input->getArgument('version'));
            $output->writeln(sprintf('Provider: <comment>%s</comment>', 'Symfony command'));
            $output->writeln(sprintf('Formatter: <comment>%s</comment>', 'Not available'));
        }

        // Used for BC compatibility with nikolaposa/version 2.2
        $isNikolaposaVersion2 = method_exists($version, 'withMajorIncremented');

        $incrementMajor = (int) $input->getOption('major');
        if ($incrementMajor > 0) {
            for ($i = 0; $i < $incrementMajor; $i++) {
                $version = $isNikolaposaVersion2 ? $version->withMajorIncremented() : $version->incrementMajor();
            }
        }

        $incrementMinor = (int) $input->getOption('minor');
        if ($incrementMinor > 0) {
            for ($i = 0; $i < $incrementMinor; $i++) {
                $version = $isNikolaposaVersion2 ? $version->withMinorIncremented() : $version->incrementMinor();
            }
        }

        $incrementPatch = (int) $input->getOption('patch');
        if ($incrementPatch > 0) {
            for ($i = 0; $i < $incrementPatch; $i++) {
                $version = $isNikolaposaVersion2 ? $version->withPatchIncremented() : $version->incrementPatch();
            }
        }

        $preRelease = $input->getOption('prerelease');
        if (!empty($preRelease)) {
            if (in_array(null, $preRelease, true)) {
                $preRelease = $isNikolaposaVersion2 ? array() : null;
            } else {
                $preRelease = implode('.', $preRelease);
            }

            $version = $version->withPreRelease($preRelease);
        }

        $build = $input->getOption('build');
        if (!empty($build)) {
            if (in_array(null, $build, true)) {
                $build = $isNikolaposaVersion2 ? array() : null;
            } else {
                $build = implode('.', $build);
            }

            $version = $version->withBuild($build);
        }

        $currentVersion = $this->manager->getVersion();
        if ((string) $currentVersion === (string) $version) {
            $version = $isNikolaposaVersion2 ? $version->withPatchIncremented() : $version->incrementPatch();
        }

        $output->writeln(sprintf('Current version: <info>%s</info>', $currentVersion));
        $output->writeln(sprintf('New version: <info>%s</info>', $version));
        if ($input->getOption('dry-run')) {
            $output->writeln(sprintf('<question>%s</question>', 'Dry run, skipping version bump'));
        } else {
            $this->manager->writeVersion($version);
        }
    }
}
