<?php

namespace App\Helpers;

use App\Entity\UpdateLog;
use App\Util\BookImportDriver;
use App\Util\OdsImportDriver;
use Doctrine\Bundle\DoctrineBundle\Registry;
use Doctrine\Persistence\ManagerRegistry;

/**
 * Helper for the import driver system. Captures input from the command to be
 * used in the fixtures.
 * 
 * @author Austin Shinpaugh
 */
class ImportDriverHelper
{
    /**
     * @var Registry
     */
    protected $doctrine;
    
    /**
     * @var UpdateLog[]
     */
    protected $logs;
    
    /**
     * @var String
     */
    protected $service_id;
    
    /**
     * @var String
     */
    protected $academic_period;
    
    /**
     * @var Integer
     */
    protected $num_years;

    /**
     * The path to the datastore if the source is a Book export.
     *
     * @var string
     */
    protected $path;

    /**
     * Whether or not to include online courses in the import.
     *
     * @var bool
     */
    protected $include_online;
    
    /**
     * ImportDriverHelper constructor.
     *
     * @param ManagerRegistry $doctrine
     */
    public function __construct(
        ManagerRegistry $doctrine
    ) {
        $this->doctrine  = $doctrine;
        $this->logs      = [];

        $this->fetchUpdateLogs();
    }

    /**
     * For ODS imports set the default number of historical years to import.
     *
     * @param int $years
     */
    public function setNumberOfYears(int $years)
    {
        $this->num_years = $years;
    }

    /**
     * Returns the filepath used for TheBook imports.
     *
     * @return string
     */
    public function getPath()
    {
        return $this->path;
    }
    
    /**
     * Get the service id of the driver being used.
     * 
     * @return String
     */
    public function getServiceId()
    {
        return $this->service_id;
    }
    
    /**
     * Sets the service id.
     * 
     * @param string $source
     *
     * @return $this
     * @throws \ErrorException
     */
    public function setServiceId($source)
    {
        if (!static::isValidImportSource($source)) {
            throw new \ErrorException("Invalid input provided for source option. Must be either 'book', 'ods', or a full filepath to a Book export.");
        }

        if ('ods' === $source) {
            $this->service_id = OdsImportDriver::class;
            return $this;
        }

        $this->service_id = BookImportDriver::class;
        $this->path       = null;
        
        if ('book' === $source) {
            return $this;
        }

        $this->path = $source;
        
        return $this;
    }
    
    /**
     * @return String
     */
    public function getAcademicPeriod()
    {
        return $this->academic_period;
    }
    
    /**
     * @param string $period The year to start from.
     *
     * @return $this
     */
    public function setAcademicPeriod($period)
    {
        if (!$period) {
            $this->academic_period = null;
            
            return $this;
        }

        if ('all' === $period) {
            return $this->setAcademicPeriod(
                date('Y') - $this->num_years
            );
        }

        $this->academic_period = $period;

        return $this;
    }
    
    /**
     * Accepts two points to byref assign values based on the
     * input taken from the command line.
     * 
     * @param Integer $start
     * @param Integer $stop
     *
     * @return $this
     */
    public function assignAcademicPoints(&$start, &$stop)
    {
        if ($this->academic_period) {
            $start = $this->academic_period;
        } else {
            $start = date('Y') - $this->num_years;
        }
        
        $stop = date('Y') + 1;
        
        return $this;
    }
    
    /**
     * Validate the service ID.
     * 
     * @param string $source
     *
     * @return bool
     */
    public static function isValidImportSource($source)
    {
        if (in_array($source, ['book', 'ods'])) {
            return true;
        }

        return file_exists($source);
    }
    
    /**
     * FK Checks need to be disabled when using TRUNCATE instead of DELETE
     * during the :fixtures:load command.
     * 
     * @param boolean $enabled
     *
     * @return int
     */
    public function toggleFKChecks($enabled)
    {
        $connection = $this->doctrine->getConnection();
        
        return $connection->executeUpdate(sprintf(
            "SET foreign_key_checks = %b;",
            (int) $enabled
        ));
    }
    
    /**
     * Get the previous logs. The import command wipes the databases, so
     * fetch them before they are destroyed.
     * 
     * Try to keep a month's worth of logs.
     * 
     * @return $this
     */
    protected function fetchUpdateLogs()
    {
        $manager = $this->doctrine->getManager();
        $repo    = $manager->getRepository(UpdateLog::class);
        $logs    = $repo->findBy([], ['start' => 'DESC'], 31);

        // For re-storing purposes, store from oldest to newest.
        $this->logs = array_reverse($logs);
        $manager->clear(UpdateLog::class);
        
        return $this;
    }
    
    /**
     * @return UpdateLog[]
     */
    public function getUpdateLogs()
    {
        return $this->logs;
    }
    
    /**
     * Remove the logs after they've been stored.
     * 
     * @return $this
     */
    public function clearLogs()
    {
        unset($this->logs);
        
        $this->doctrine->getManager()->clear();
        
        return $this;
    }
    
    /**
     * Fetch the current UpdateLog.
     * 
     * @return UpdateLog
     */
    public function getLogEntry()
    {
        $repo = $this->doctrine->getRepository(UpdateLog::class);
        $logs = $repo->findBy([], ['start' => 'DESC'], 1);

        return current($logs);
    }

    public function getIncludeOnline(): bool
    {
        return $this->include_online;
    }

    public function setIncludeOnline(bool $include)
    {
        $this->include_online = $include;

        return $this;
    }
}
