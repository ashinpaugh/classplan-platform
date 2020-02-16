<?php

namespace App\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\HttpKernel\KernelInterface;

/**
 * Base command class.
 * 
 * @author Austin Shinpaugh <ashinpaugh@ou.edu>
 */
abstract class AbstractCommand extends Command
{
    protected $projectDir;

    public function __construct(string $name = null)
    {
        parent::__construct($name);
    }

    public function setProjectDir(KernelInterface $kernel)
    {
        $this->projectDir = $kernel->getProjectDir();
    }

    /**
     * Return the full path to the bin/console component.
     *
     * @return string
     */
    public function getConsolePath()
    {
        // return 'php ' . $this->getAppRoot() . '/bin/console';
        return '$(which php) ' . $this->projectDir . '/bin/console';
    }
}
