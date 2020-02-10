<?php

namespace App\Command;

use Symfony\Component\Console\Command\Command;

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
