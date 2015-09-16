<?php

namespace LaFourchette\FixturesBundle\Command;

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
        ;
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $fixturesLoaderRegistry = $this->getContainer()->get('fixtures.loader.registry');
        $em = $input->getArgument('em');
        $path = $input->getArgument('path');
        $purge = $input->getOption('purge');

        $fixturesLoader = $fixturesLoaderRegistry->getLoader($em);

        $output->writeln(sprintf('<info>Load : %s</info>', $path));
        $fixturesLoader->load(array($path), $purge);

        return 0;
    }
}
