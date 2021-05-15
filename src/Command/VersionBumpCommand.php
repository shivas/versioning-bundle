<?php

namespace Shivas\VersioningBundle\Command;

use Shivas\VersioningBundle\Service\VersionManagerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Version\Version;

/**
 * Class VersionBumpCommand
 */
final class VersionBumpCommand extends Command
{
    protected static $defaultName = 'app:version:bump';

    /**
     * @var VersionManagerInterface
     */
    private $manager;

    public function __construct(VersionManagerInterface $manager)
    {
        $this->manager = $manager;

        parent::__construct();
    }

    /**
     * {@inheritdoc}
     */
    protected function configure(): void
    {
        $this
            ->setDescription('Manually bump the application version')
            ->addArgument('version', InputArgument::OPTIONAL, 'Version to set, should be compatible with Semantic versioning 2.0.0', null)
            ->addOption('dry-run', 'd', InputOption::VALUE_NONE, 'Dry run, does not update VERSION file')
            ->addOption('major', null, InputOption::VALUE_REQUIRED, 'Bump MAJOR version by given number', 0)
            ->addOption('minor', null, InputOption::VALUE_REQUIRED, 'Bump MINOR version by given number', 0)
            ->addOption('patch', null, InputOption::VALUE_REQUIRED, 'Bump PATCH version by given number', 0)
            ->addOption('prerelease', null, InputOption::VALUE_IS_ARRAY | InputOption::VALUE_OPTIONAL, 'Set PRERELEASE to given value', [])
            ->addOption('build', null, InputOption::VALUE_IS_ARRAY | InputOption::VALUE_OPTIONAL, 'Set BUILD to given value', []);
    }

    /**
     * Manually bump the application version
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        /** @var string|null $versionArg */
        $versionArg = $input->getArgument('version');
        if (null === $versionArg) {
            $version = $this->manager->getVersionFromProvider();
            $output->writeln(sprintf('Provider: <comment>%s</comment>', get_class($this->manager->getActiveProvider())));
            $output->writeln(sprintf('Formatter: <comment>%s</comment>', get_class($this->manager->getFormatter())));
        } else {
            $version = Version::fromString($versionArg);
            $output->writeln(sprintf('Provider: <comment>%s</comment>', 'Symfony command'));
            $output->writeln(sprintf('Formatter: <comment>%s</comment>', 'Not available'));
        }

        $incrementMajor = (int) $input->getOption('major');
        if ($incrementMajor > 0) {
            for ($i = 0; $i < $incrementMajor; $i++) {
                $version = $version->incrementMajor();
            }
        }

        $incrementMinor = (int) $input->getOption('minor');
        if ($incrementMinor > 0) {
            for ($i = 0; $i < $incrementMinor; $i++) {
                $version = $version->incrementMinor();
            }
        }

        $incrementPatch = (int) $input->getOption('patch');
        if ($incrementPatch > 0) {
            for ($i = 0; $i < $incrementPatch; $i++) {
                $version = $version->incrementPatch();
            }
        }

        /** @var array<string|null> $preRelease */
        $preRelease = $input->getOption('prerelease');
        if ([] !== $preRelease) {
            if (in_array(null, $preRelease, true)) {
                $preRelease = null;
            } else {
                $preRelease = implode('.', $preRelease);
            }

            $version = $version->withPreRelease($preRelease);
        }

        /** @var array<string|null> $build */
        $build = $input->getOption('build');
        if ([] !== $build) {
            if (in_array(null, $build, true)) {
                $build = null;
            } else {
                $build = implode('.', $build);
            }

            $version = $version->withBuild($build);
        }

        $currentVersion = $this->manager->getVersion();
        if ((string) $currentVersion === (string) $version) {
            $version = $version->incrementPatch();
        }

        $output->writeln(sprintf('Current version: <info>%s</info>', $currentVersion));
        $output->writeln(sprintf('New version: <info>%s</info>', $version));
        if ($input->getOption('dry-run')) {
            $output->writeln(sprintf('<question>%s</question>', 'Dry run, skipping version bump'));
        } else {
            $this->manager->writeVersion($version);
        }

        return 0;
    }
}
