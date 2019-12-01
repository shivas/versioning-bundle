<?php

namespace Shivas\VersioningBundle\Command;

use Shivas\VersioningBundle\Formatter\FormatterInterface;
use Shivas\VersioningBundle\Service\VersionManager;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Class StatusCommand
 */
class StatusCommand extends Command
{
    protected static $defaultName = 'app:version:status';

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
        $this->setDescription('Show current application version status');
    }

    /**
     * Show current application version status
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $output->writeln(sprintf('Provider: <comment>%s</comment>', get_class($this->manager->getActiveProvider())));
        $formatter = $this->manager->getFormatter();
        if ($formatter instanceof FormatterInterface) {
            $output->writeln(sprintf('Formatter: <comment>%s</comment>', get_class($formatter)));
        } else {
            $output->writeln(sprintf('Formatter: <comment>%s</comment>', 'None'));
        }

        $version = $this->manager->getVersion();
        $newVersion = $this->manager->getVersionFromProvider();

        if ((string) $version == (string) $newVersion) {
            $output->writeln(sprintf('Current version: <info>%s</info>', $version));
        } else {
            $output->writeln(sprintf('Current version: <error>%s</error>', $version));
            $output->writeln(sprintf('New version: <info>%s</info>', $newVersion));
            $output->writeln(sprintf('<comment>%s</comment>', 'Version outdated, please run the cache:clear command'));
        }

        return 0;
    }
}
