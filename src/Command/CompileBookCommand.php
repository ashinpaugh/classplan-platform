<?php

namespace App\Command;

use App\Helpers\ImportDriverHelper;
use SplFileInfo;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Finder\Finder;

/**
 * Extends doctrine's fixtures command for integration into the
 * import driver system.
 *
 * @author Austin Shinpaugh
 */
class CompileBookCommand extends AbstractCommand
{
    /**
     * @var ImportDriverHelper
     */
    protected $importer;

    public function __construct(ImportDriverHelper $import_helper)
    {
        $this->importer = $import_helper;

        parent::__construct('classplan:book:compile');
    }

    /**
     * {@inheritDoc}
     */
    protected function configure()
    {
        parent::configure();

        $this
            ->setDescription('Concat several book exports into one file.')
            ->addArgument(
                'folder',
                InputArgument::REQUIRED,
                'The full folder path of the files to import - files should end in <info>.csv</info>.'
            )
            ->addOption(
                'output_file',
                'o',
                InputOption::VALUE_OPTIONAL,
                'The full file path to append to.',
                realpath(__DIR__ . '/../../datastores/Classes.csv')
            )
            ->addOption(
                'memory',
                'm',
                InputOption::VALUE_OPTIONAL,
                'The memory limit set while running this command.',
                '2048M'
            )
        ;
    }

    /**
     * {@inheritDoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        ini_set('memory_limit', $input->getOption('memory'));

        $path        = $input->getArgument('folder');
        $output_path = $input->getOption('output_file');

        $finder = Finder::create()
            ->in($path)
            ->files()
            ->name('*.csv')
        ;

        if (!$finder->hasResults()) {
            $output->writeln('No csv files were found in folder: ' . $path);
            return -1;
        }

        $output_file = fopen($output_path, 'a+');

        if (false === $output_file) {
            $output->writeln('Unable to open datastore: ' . $output_file);
            return -2;
        }

        $total_sections = 0;

        /* @var SplFileInfo $file */
        foreach ($finder as $file) {
            $num_sections = $this->appendFile($output_file, $file->getRealPath());
            $output->writeln($file->getRealPath() . ': ' . $num_sections);

            $total_sections += $num_sections;
        }

        fclose($output_file);

        $output->writeln('Total number of sections appended: ' . $total_sections);

        return 0;
    }

    /**
     * Write the contents of an input file to the output handle.
     *
     * @param resource $output_handle
     * @param string   $file
     *
     * @return int
     */
    protected function appendFile($output_handle, $file)
    {
        $handle = fopen($file, 'r');

        if (!$handle) {
            return 0;
        }

        $count = 1;
        $buffer = '';

        // Skip TheBook headers.
        fgets($handle);

        while (($line = fgets($handle)) !== false) {

            $buffer .= $line;
            $count++;

            if ($count % 10 === 0) {
                fwrite($output_handle, $buffer);
                $buffer = '';
            }
        }


        if ($buffer) {
            fwrite($output_handle, $buffer);
        }

        fclose($handle);

        return $count;
    }
}
