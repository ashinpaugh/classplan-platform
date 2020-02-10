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

    public function __construct(ImportDriverHelper $importDriverHelper, KernelInterface $kernel)
    {
        $this->importer = $importDriverHelper;
        $this->projectDir = $kernel->getProjectDir();

        parent::__construct('classplan:import');
    }

    /**
     * {@inheritDoc}
     */
    protected function configure()
    {
        parent::configure();

        $this
            // ->setAliases(['classplan:import'])
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

        try {
            $this->importer
                ->setServiceId($source)
                ->setAcademicPeriod($period)
                ->toggleFKChecks(false)
            ;
        } catch (\ErrorException $e) {
            $output->writeln('An error occurred: ' . $e->getMessage());
        }

        // parent::execute($input, $output);

        $command = $this->getApplication()->find('doctrine:fixtures:load');
        $args    = new ArrayInput([
            'command' => 'doctrine:fixtures:load',
            '-n' => true,
            '--purge-with-truncate' => true,
            '--no-debug' => true,
            // '-q' => true,
        ]);

        $command->run($args, $output);

        /*$process = new Process(
            [
                $this->getConsolePath(),
                'classplan:import',
                '--no-debug',
                '--purge-with-truncate',
                '--no-interaction',
            ]
        );

        $process->run();*/

        // $this->doImport($output);
    }

    /**
     * Runs the schedule:import command.
     * The command will timeout after three hours.
     *
     * @param OutputInterface $output
     *
     * @return $this
     */
    /*private function doImport(OutputInterface $output)
    {
        $output->writeln("\nRunning import...");

        $reset = false;
        $process->run(function ($type, $buffer) use ($output, &$reset) {
            if (1 === strlen($buffer)) {
                return;
            }

            if (false === strpos($buffer, '%')) {
                $output->write($buffer);
                $reset = true;
            } else {
                $this->printStreamResponse($buffer, $reset ? 0 : null);
                $reset = false;
            }
        });

        return $this;
    }*/

    /**
     * Replace the cli's last message with a new one.
     *
     * @param string $message
     * @param null   $force_clear_lines
     *
     * @url https://stackoverflow.com/questions/4320081/clear-php-cli-output
     */
    private function printStreamResponse($message, $force_clear_lines = null)
    {
        static $last_lines = 0;

        if (!is_null($force_clear_lines)) {
            $last_lines = $force_clear_lines;
        }

        $term_width = exec('tput cols', $toss, $status);
        if ($status) {
            $term_width = 64; // Arbitrary fall-back term width.
        }

        $line_count = 0;
        foreach (explode("\n", $message) as $line) {
            $line_count += count(str_split($line, $term_width));
        }

        // Erasure MAGIC: Clear as many lines as the last output had.
        for ($i = 0; $i < $last_lines; $i++) {
            // Can be consolodated into
            echo "\r\033[K\033[1A\r\033[K\r";
        }

        $last_lines = $line_count;

        echo $message."\n";
    }
}
