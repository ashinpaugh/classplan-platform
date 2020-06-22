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
    protected $project_dir;

    /**
     * {@inheritDoc}
     */
    public function __construct(string $name = null)
    {
        parent::__construct($name);
    }

    /**
     * Set the project's root directory.
     *
     * @param KernelInterface $kernel
     */
    public function setProjectDir(KernelInterface $kernel)
    {
        $this->project_dir = $kernel->getProjectDir();
    }

    /**
     * Return the full path to the bin/console component.
     *
     * @return string
     */
    public function getConsolePath()
    {
        return '$(which php) ' . $this->project_dir . '/bin/console';
    }
}
