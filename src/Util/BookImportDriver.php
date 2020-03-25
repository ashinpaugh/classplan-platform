<?php

namespace App\Util;

use App\Entity\Building;
use App\Entity\Campus;
use App\Entity\Course;
use App\Entity\Instructor;
use App\Entity\Room;
use App\Entity\Section;
use App\Entity\Subject;
use App\Entity\Term;
use App\Entity\TermBlock;
use Symfony\Component\Filesystem\Exception\FileNotFoundException;

class BookImportDriver extends AbstractImportDriver
{
    const CSV_PATH = 'datastores/Classes.csv';
    
    protected $path;
    
    /**
     * {@inheritdoc}
     */
    public function init($mixed = null)
    {
        $default = $this->project_dir . '/' . static::CSV_PATH;
        $path    = $this->helper->getPath() ?: $default;

        $this
            ->setEnvironmentVars()
            ->setPath($path)
            ->loadRawData()
        ;
    }
    
    /**
     * {@inheritdoc}
     */
    public function getCount()
    {
        return count($this->getEntries());
    }
    
    /**
     * {@inheritdoc}
     */
    public function createCampus()
    {
        return new Campus($this->getEntry(9));
    }
    
    /**
     * {@inheritdoc}
     */
    public function createBuilding(Campus $campus = null)
    {
        $campus = $campus ?: $this->createCampus();
        
        return new Building($campus, $this->getLocation('building'));
    }
    
    /**
     * {@inheritdoc}
     */
    public function createRoom(Building $building = null)
    {
        $building = $building ?: $this->createBuilding();
        // $number   = $this->getLocation('room') ?: '0000';
        $number   = $this->getLocation('room');
        
        return new Room($building, $number);
    }
    
    /**
     * {@inheritdoc}
     */
    public function createInstructor()
    {
        $data = $this->getEntry();
        $id   = (int) $data[7];
        $name = $data[7] ? $data[6] : 'N/A';
        
        return new Instructor($id, $name);
    }
    
    
    /**
     * {@inheritdoc}
     */
    public function createTerm()
    {
        $entry = $this->getEntry();
        $dict  = $this->parseTerm($entry);
        $term  = new Term($entry[0], $dict['year'], $dict['semester']);
        $block = new TermBlock($term, $dict['block']);
        
        $term->addBlock($block);
        
        return $block;
    }
    
    /**
     * {@inheritdoc}
     */
    public function createSubject()
    {
        return new Subject($this->getEntry(1));
    }
    
    /**
     * {@inheritdoc}
     */
    public function createCourse(Subject $subject = null)
    {
        $entry   = $this->getEntry();
        $subject = $subject ?: $this->createSubject();
        $course  = new Course($subject, $entry[2]);
        
        $course
            ->setName($entry[5])
            ->setLevel($entry[36])
        ;
        
        return $course;
    }
    
    /**
     * {@inheritdoc}
     */
    public function createSection(Subject $subject = null)
    {
        $entry   = $this->getEntry();
        $section = new Section();
        
        $section
            ->setCrn($entry[4])
            ->setDays($entry[20])
            ->setStartDate($this->getDate($entry[16]))
            ->setEndDate($this->getDate($entry[17]))
            ->setStartTime($entry[21])
            ->setEndTime($entry[22])
            ->setStatus($entry[8])
            ->setNumber($entry[3])
            ->setNumEnrolled($entry[12])
            ->setMaximumEnrollment($entry[11])
            ->setMeetingType($entry[33])
        ;
        
        return $section;
    }
    
    /**
     * Break the terms into parts.
     *
     * @param array $data
     *
     * @return array
     */
    protected function parseTerm(array $data)
    {
        $parts = explode(' ', $data[0]);
        return [
            'year'     => end($parts),
            'semester' => $parts[0],
            'block'    => $data[35],
        ];
    }
    
    /**
     * Set the path for the file to import.
     * 
     * @param string $path
     *
     * @return $this
     */
    public function setPath($path)
    {
        $this->path = $path;
        
        return $this;
    }
    
    /**
     * Read the CSV contents line by line and read the valid entries into memory.
     *
     * @param integer $mixed
     *
     * @return $this
     * @internal param int $limit
     *
     */
    protected function loadRawData($mixed = null)
    {
        $handle = $this->openFile();
        $data   = [];
        
        while($line = fgetcsv($handle)) {
            if (!$this->isValidEntry($line)) {
                continue;
            }
            
            $data[] = $line;
        }
        
        fclose($handle);
        
        return $this->setEntries($data);
    }
    
    /**
     * Open a file for reading.
     * 
     * @return bool|resource
     */
    protected function openFile()
    {
        if (!$handle = fopen($this->path, 'r')) {
            throw new FileNotFoundException();
        }
        
        // Ignore the column headers.
        fgetcsv($handle);
        
        return $handle;
    }
    
    /**
     * Determine if we should parse a row.
     * 
     * @param array $data
     *
     * @return bool
     */
    private function isValidEntry(array $data)
    {
        // 0 = semester - invalid entry. 8 = status.
        if ('...' === $data[0] || 'Active' !== $data[8]) {
            return false;
        }
        
        if (!$this->getIncludeOnline() && $this->isOnline($data)) {
            return false;
        }
        
        return true;
    }
    
    /**
     * Determine if the class offered is an online class.
     * 
     * @param array $data
     *
     * @return bool
     */
    private function isOnline(array $data)
    {
        // 18 = Building. 19 = Room. 20 = Days.
        // return !$data[18] || null === $data[19] || null === $data[20];
        return trim($data[33]) === 'WEB';
    }
    
    /**
     * Parse special cases of the building codes.
     * 
     * @return array
     */
    protected function parseBuilding()
    {
        $data = $this->getEntry();
        
        if ('XCH' !== substr($data[18], 0, 3)) {
            return [
                'building' => $data[18],
                'room'     => $data[19],
            ];
        }
        
        return [
            'building' => 'XCH',
            'room'     => substr($data[18], 3),
        ];
    }
}
