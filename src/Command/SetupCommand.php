<?php

namespace App\Command;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\NullOutput;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Process\Process;

/**
 * Command that should be run after setting the environmental vars in ".env.local".
 * 
 * @author Austin Shinpaugh
 */
class SetupCommand extends AbstractCommand
{
    /**
     * @var EntityManagerInterface
     */
    protected $doctrine;

    public function __construct(EntityManagerInterface $doctrine)
    {
        parent::__construct('classplan:setup');

        $this->doctrine = $doctrine;
    }

    /**
     * {@inheritDoc}
     */
    protected function configure()
    {
        parent::configure();

        $this
            ->setDescription('Initialize the app settings.')
            ->addOption(
                'reset',
                '',
                InputOption::VALUE_NONE,
                'Drops the tables currently in the database.'
            )
            ->addOption(
                'composer',
                '',
                InputOption::VALUE_NONE,
                'Whether to run the composer optimizations.'
            )
        ;
    }

    /**
     * {@inheritDoc}
     *
     * @throws \Exception
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this
            ->setupDatabase($output)
            ->wipeSchema($input, $output)
            ->prepareAssets($output)
            ->generateOptimizedAutoloader($input, $output)
            ->warmCache()
        ;

        $output->writeln("\nSetup complete.");

        // Import needs to be run in dev env because doctrine fixtures aren't loaded in prod.
        $output->writeln("Next run <info>php bin/console classplan:import --env=dev --source=(book|ods) -n --no-debug</info> command to populate the database.");

        return 0;
    }

    /**
     * Passing the env option to the sub-command is ignored. The output says prod, but
     * builds the assets in the same environment that the :setup command was
     * run in. For this reason we use the process component.
     *
     * @param OutputInterface $output
     *
     * @return $this
     * @throws \Exception
     */
    private function prepareAssets(OutputInterface $output)
    {
        $output->writeln('Preparing assets...');

        $command = $this->getApplication()->find('assets:install');
        $args    = new ArrayInput([
            'command'    => 'assets:install',
            '--symlink'  => true,
            '--relative' => true,
            '--quiet'    => true,
        ]);

        $success = 0 == $command->run($args, new NullOutput());

        if ($success) {
            $output->writeln("Assets created successfully.\n");
            return $this;
        }
        
        $output->writeln("Production files could not be created.");
        $output->writeln("Try running 'php bin/console assets:install --env=prod' for further information.");
        die();
    }
    
    /**
     * Create the project's database.
     * 
     * @param OutputInterface $output
     *
     * @return $this
     * @throws \Exception
     */
    private function setupDatabase(OutputInterface $output)
    {
        $output->writeln('Creating the database...');
        
        $command = $this->getApplication()->find('doctrine:database:create');
        $args    = new ArrayInput([
            'command'         => 'doctrine:database:create',
            '--quiet'         => true,
            '--no-debug'      => true,
            '--if-not-exists' => true,
        ]);
        
        $command->run($args, new NullOutput());
        
        return $this;
    }
    
    /**
     * Wipe the tables in the DB.
     * 
     * @param InputInterface  $input
     * @param OutputInterface $output
     *
     * @return $this
     * @throws \Exception
     */
    private function wipeSchema(InputInterface $input, OutputInterface $output)
    {
        if (!$input->getOption('reset')) {
            return $this;
        }
        
        $output->writeln("\nWiping table schema...");
        $command = $this->getApplication()->find('doctrine:schema:drop');
        $args    = new ArrayInput([
            'command'    => 'doctrine:schema:drop',
            '--quiet'    => true,
            '--no-debug' => true,
            '--force'    => true,
        ]);
        
        $command->run($args, new NullOutput());
        
        $output->writeln("Wipe complete.\n");

        $this->createTableSchema($output);
        
        return $this;
    }
    
    /**
     * Create the table schema.
     * 
     * @param OutputInterface $output
     *
     * @return $this
     * @throws \Exception
     */
    private function createTableSchema(OutputInterface $output)
    {
        $command = $this->getApplication()->find('doctrine:schema:create');
        $args    = new ArrayInput([
            'command'    => 'doctrine:schema:create',
            '--quiet'    => true,
            '--no-debug' => true,
        ]);
        
        $command->run($args, new NullOutput());
        
        $output->writeln("Entity schema created.\n");
        
        return $this;
    }
    
    /**
     * Optimize the composer auto loader.
     *
     * @param InputInterface $input
     * @param OutputInterface $output
     *
     * @return $this
     */
    private function generateOptimizedAutoloader(InputInterface $input, OutputInterface $output)
    {
        if (!$input->getOption('composer')) {
            return $this;
        }

        $output->writeln('Generating optimized autoloader...');

        $command = 'composer dump-autoload -oa';
        $process = Process::fromShellCommandline($command);
        $process->run();
        
        if (!$process->isSuccessful()) {
            $output->writeln("Failed to run: <error>{$command}</error>");
        } else {
            $output->writeln('Autoloader optimized.');
        }
        
        return $this;
    }
    
    /**
     * Create cache files.
     * 
     * @return $this
     */
    private function warmCache()
    {
        $process = new Process([
            $this->getConsolePath(),
            'cache:warmup',
            '--env=prod',
        ]);
        
        $process->run();
        
        return $this;
    }
}
