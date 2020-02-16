<?php

namespace App\Command;

use App\Util\ImportDriverHelper;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\HttpKernel\KernelInterface;

/**
 * Extends doctrine's fixtures command for integration into the
 * import driver system.
 * 
 * @author Austin Shinpaugh
 */
class ImportCommand extends AbstractCommand
{
    /**
     * @var ImportDriverHelper
     */
    protected $importer;

    public function __construct(ImportDriverHelper $importDriverHelper)
    {
        $this->importer = $importDriverHelper;

        parent::__construct('classplan:import');
    }

    /**
     * {@inheritDoc}
     */
    protected function configure()
    {
        parent::configure();

        $this
            ->setDescription('Populate the database.')
            ->addOption(
                'source',
                's',
                InputOption::VALUE_OPTIONAL,
                "The data source used to update the data. Either 'ods' or 'book'.",
                'ods'
            )->addOption(
                'year',
                'y',
                InputOption::VALUE_OPTIONAL,
                'The starting year to import. IE: 2015',
                'all'
            )->setHelp('Import data from varying sources into the database.')
        ;
    }

    /**
     * {@inheritDoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        ini_set('memory_limit', '4096M');

        $source = $input->getOption('source');
        $period = $input->getOption('year');

        $command = $this->getApplication()->find('doctrine:fixtures:load');
        $args    = new ArrayInput([
            'command' => 'doctrine:fixtures:load',
            '--purge-with-truncate' => true,
            '--no-debug' => true,
        ]);

        $args->setInteractive(false);

        try {
            $this->importer
                ->setServiceId($source)
                ->setAcademicPeriod($period)
                ->toggleFKChecks(false)
            ;

            $command->run($args, $output);
        } catch (\ErrorException $e) {
            $output->writeln('An error occurred: ' . $e->getMessage());
        } catch (\Exception $e) {
            $output->writeln('An error occurred executing [doctrine:fixtures:load]: ' . $e->getMessage());
        }
    }
}
