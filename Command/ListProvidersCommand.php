<?php

namespace Shivas\VersioningBundle\Command;

use Shivas\VersioningBundle\Provider\ProviderInterface;
use Shivas\VersioningBundle\Service\VersionManager;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Class ListProvidersCommand
 */
class ListProvidersCommand extends Command
{
    protected static $defaultName = 'app:version:list-providers';

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
        $this->setDescription('List all registered version providers');
    }

    /**
     * List all registered version providers
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $output->writeln('Registered version providers');
        $providers = $this->manager->getProviders();

        $table = new Table($output);
        $table->setHeaders(array('Alias', 'Class', 'Priority', 'Supported'))
            ->setStyle('borderless');

        foreach ($providers as $alias => $providerEntry) {
            /** @var ProviderInterface $provider */
            $provider = $providerEntry['provider'];
            $supported = $provider->isSupported() ? 'Yes' : 'No';
            $table->addRow(array($alias, get_class($provider), $providerEntry['priority'], $supported));
        }

        $table->render();

        return 0;
    }
}
