<?php

namespace LaFourchette\FixturesBundle\Command;

use Doctrine\DBAL\Sharding\PoolingShardConnection;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class LoadCommand extends ContainerAwareCommand
{
    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this
            ->setName('lafourchette:fixtures:load')
            ->setDescription('Load fixtures')
            ->addArgument('em', InputArgument::REQUIRED, 'Which entity manager ?')
            ->addArgument('path', InputArgument::REQUIRED, 'Which path (can be a group, ... see format) ?')
            ->addOption('purge', 'p', InputOption::VALUE_OPTIONAL, 'Purge data', true)
            ->addOption('shard', null, InputOption::VALUE_REQUIRED, 'The shard connection to use for this command.')
        ;
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $fixturesLoaderRegistry = $this->getContainer()->get('fixtures.loader.registry');
        $emName = $input->getArgument('em');
        $path = $input->getArgument('path');
        $purge = $input->getOption('purge');
        $shard = $input->getOption('shard');

        $em = $this->getContainer()->get('doctrine')->getManager($emName);

        if (!$em) {
            throw new \LogicException(sprintf("EntityManager '%s' not exist.", $emName));
        }

        if ($shard) {
            if (!$em->getConnection() instanceof PoolingShardConnection) {
                throw new \LogicException(sprintf("Connection of EntityManager '%s' must implement shards configuration.", $emName));
            }

            $em->getConnection()->connect($shard);
        }

        $fixturesLoader = $fixturesLoaderRegistry->getLoader($emName);

        $output->writeln(sprintf('<info>Load : %s</info>', $path));
        $fixturesLoader->load(array($path), $purge);

        return 0;
    }
}
