<?php

namespace App\Command;

use App\Helpers\ImportDriverHelper;
use ErrorException;
use Exception;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

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

    public function __construct(ImportDriverHelper $import_helper)
    {
        $this->importer = $import_helper;

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
            ->setHelp('Import data using one of the specified drivers to populate the database.')
            ->addOption(
                'source',
                's',
                InputOption::VALUE_REQUIRED,
                "The data source used to update the data. Either 'ods', 'book', or a full file path to an export of TheBook.",
                'ods'
            )
            ->addOption(
                'year',
                'y',
                InputOption::VALUE_OPTIONAL,
                'The starting year to import. IE: 2019',
                'all'
            )
            ->addOption(
                'online',
                'o',
                InputOption::VALUE_OPTIONAL,
                'Whether to include online courses in the import.',
                true
            )
            ->addOption(
                'update-buildings',
                'b',
                InputOption::VALUE_NONE,
                'Run the update building after the import completes.'
            )
            ->addOption(
                'memory',
                'm',
                InputOption::VALUE_OPTIONAL,
                'The memory limit set while running this command.',
                '4096M'
            )
            ->addOption(
                'logging',
                'l',
                InputOption::VALUE_OPTIONAL,
                'Whether to log sql events (mysql general_log).',
                false
            )
        ;
    }

    /**
     * {@inheritDoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        ini_set('memory_limit', $input->getOption('memory'));

        $code = $this->loadFixtures($input, $output);

        if (!$input->getOption('update-buildings')) {
            return $code;
        }

        return $this->updateBuildings($output);
    }

    /**
     * Import content into the main DB.
     *
     * @param InputInterface $input
     * @param OutputInterface $output
     *
     * @return int|mixed
     */
    protected function loadFixtures(InputInterface $input, OutputInterface $output)
    {
        $source  = $input->getOption('source');
        $period  = $input->getOption('year');
        $online  = $input->getOption('online');
        $logging = $input->getOption('logging');

        $command = $this->getApplication()->find('doctrine:fixtures:load');
        $args    = new ArrayInput([
            'command'               => 'doctrine:fixtures:load',
            '--purge-with-truncate' => true,
            '--no-debug'            => true,
        ]);

        $args->setInteractive(false);

        try {
            $this->importer
                ->setServiceId($source)
                ->setAcademicPeriod($period)
                ->setIncludeOnline($online)
                ->toggleFKChecks(false)
            ;

            if (!$logging) {
                $this->importer->toggleSqlLogging(false);
            }

            $result = $command->run($args, $output);
        } catch (ErrorException $e) {
            $output->writeln('An error occurred: ' . $e->getMessage());
            $result = $e->getCode();
        } catch (Exception $e) {
            $output->writeln('An error occurred executing [doctrine:fixtures:load]: ' . $e->getMessage());
            $result = $e->getCode();
        }

        $this->importer
            ->toggleSqlLogging(true)
            ->toggleFKChecks(true)
        ;

        return $result;
    }

    /**
     * Runs the update buildings command after an import.
     *
     * @param OutputInterface $output
     *
     * @return int|mixed
     */
    protected function updateBuildings(OutputInterface $output)
    {
        $command = $this->getApplication()->find('classplan:buildings:update');
        $args    = new ArrayInput([
            'command'    => 'classplan:buildings:update',
            '--no-debug' => true,
        ]);

        $args->setInteractive(false);

        try {
            return $command->run($args, $output);
        } catch (Exception $e) {
            $output->writeln('An error occurred executing [classplan:buildings:update]: ' . $e->getMessage());
            return $e->getCode();
        }
    }
}
