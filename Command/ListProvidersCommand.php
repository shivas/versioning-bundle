<?php

namespace Shivas\VersioningBundle\Command;

use Shivas\VersioningBundle\Provider\ProviderInterface;
use Shivas\VersioningBundle\Service\VersionManagerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Class ListProvidersCommand
 */
final class ListProvidersCommand extends Command
{
    protected static $defaultName = 'app:version:list-providers';

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
        $this->setDescription('List all registered version providers');
    }

    /**
     * List all registered version providers
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $output->writeln('Registered version providers');
        $providers = $this->manager->getProviders();

        $table = new Table($output);
        $table->setHeaders(['Alias', 'Class', 'Priority', 'Supported'])
            ->setStyle('borderless');

        foreach ($providers as $alias => $providerEntry) {
            /** @var ProviderInterface $provider */
            $provider = $providerEntry['provider'];
            $supported = $provider->isSupported() ? 'Yes' : 'No';
            $table->addRow([$alias, get_class($provider), $providerEntry['priority'], $supported]);
        }

        $table->render();

        return 0;
    }
}
