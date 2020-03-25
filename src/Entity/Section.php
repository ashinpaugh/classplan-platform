<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation as Serializer;

/**
 * The Section table represents instances of course sections.
 * 
 * @ORM\Entity()
 * @ORM\Table(name="section")
 */
class Section extends AbstractEntity
{
    const MT_EXAM    = 0;
    const MT_CLASS   = 1;
    const MT_WEB     = 2;
    const MT_LAB     = 3;
    const MT_CONF    = 4;
    const MT_UNKNOWN = 5;
    
    const CANCELLED = -1;
    const INACTIVE  = 0;
    const ACTIVE    = 1;
    
    /**
     * The owning subject.
     *
     * @ORM\ManyToOne(targetEntity="Subject", inversedBy="sections", fetch="EAGER")
     * @Serializer\Groups(groups={"subject"})
     *
     * @var Subject
     */
    protected $subject;
    
    /**
     * The owning course.
     *
     * @ORM\ManyToOne(targetEntity="Course", inversedBy="sections", fetch="EAGER")
     * @Serializer\Groups(groups={"course"})
     *
     * @var Course
     */
    protected $course;
    
    /**
     * The campus that's teaching the course.
     *
     * @ORM\ManyToOne(targetEntity="Campus", inversedBy="sections", fetch="EAGER")
     * @Serializer\Groups(groups={"campus"})
     *
     * @var Campus
     */
    protected $campus;

    /**
     * The room the section is held in.
     *
     * @ORM\ManyToOne(targetEntity="Building", fetch="LAZY")
     * @Serializer\Groups(groups={"building"})
     *
     * @var Building
     */
    protected $building;
    
    /**
     * The room the section is held in.
     *
     * @ORM\ManyToOne(targetEntity="Room", inversedBy="sections", fetch="EAGER")
     * @Serializer\Groups(groups={"room"})
     *
     * @var Room
     */
    protected $room;
    
    /**
     * The primary instructor.
     *
     * @ORM\ManyToOne(targetEntity="Instructor", inversedBy="sections", fetch="EAGER")
     * @Serializer\Groups(groups={"instructor"})
     * 
     * @var Instructor
     */
    protected $instructor;
    
    /**
     * The block the section belongs to.
     *
     * @ORM\ManyToOne(targetEntity="TermBlock")
     * @Serializer\Groups({"section_full"})
     *
     * @var TermBlock
     */
    protected $block;
    
    /**
     * The unique section id.
     *
     * @ORM\Id()
     * @ORM\Column(name="id", type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     * @Serializer\Groups({"section", "section_full"})
     * 
     * @var Integer
     */
    protected $id;
    
    /**
     * The class CRN.
     * 
     * @ORM\Column(type="integer")
     * @Serializer\Groups({"section", "section_full"})
     * 
     * @var Integer
     */
    protected $crn;
    
    /**
     * The section's state / status.
     *
     * @ORM\Column(type="smallint")
     * @Serializer\Groups({"section", "section_full"})
     *
     * @var Integer
     */
    protected $status;
    
    /**
     * The section number.
     *
     * @ORM\Column(type="string", length=3)
     * @Serializer\Groups({"section", "section_full"})
     *
     * @var Integer
     */
    protected $number;
    
    /**
     * The string representation of the days the class meets.
     *
     * @ORM\Column(type="string", nullable=true)
     * @Serializer\Groups({"section", "section_full"})
     *
     * @var String
     */
    protected $days;
    
    /**
     * @ORM\Column(type="date")
     * @Serializer\Exclude()
     *
     * @var \DateTime
     */
    protected $start_date;
    
    /**
     * 24hr end time.
     *
     * @ORM\Column(type="date")
     * @Serializer\Exclude()
     *
     * @var \DateTime
     */
    protected $end_date;
    
    /**
     * 24hr start time.
     *
     * @ORM\Column(type="string")
     * @Serializer\Groups({"section", "section_full"})
     *
     * @var string
     */
    protected $start_time;
    
    /**
     * When the section ends.
     *
     * @ORM\Column(type="string")
     * @Serializer\Groups({"section", "section_full"})
     *
     * @var string
     */
    protected $end_time;
    
    /**
     * The number of students enrolled.
     *
     * @ORM\Column(type="integer")
     * @Serializer\Groups({"section", "section_full"})
     *
     * @var integer
     */
    protected $num_enrolled;
    
    /**
     * The soft-cap of students allowed to enroll.
     *
     * @ORM\Column(type="integer")
     * @Serializer\Groups({"section", "section_full"})
     *
     * @var Integer
     */
    protected $maximum_enrollment;
    
    /**
     * The classroom type. Default to meeting_type_code 'CLAS'
     * because exams aren't included in TheBook imports.
     * 
     * @ORM\Column(type="smallint", options={"default": 1})
     * @Serializer\Groups({"section", "section_full"})
     *
     * @var String
     */
    protected $meeting_type;
    
    /**
     * The building the section meets in.
     *
     * @Serializer\VirtualProperty(name="building")
     * @Serializer\Groups({"section_full"})
     * @Serializer\Type(Building::class)
     *
     * @return Building
     */
    public function getBuilding()
    {
        return $this->building;
    }
    
    /**
     * The date the section ends (YYYY-MM-DD).
     *
     * @Serializer\VirtualProperty()
     * @Serializer\Groups({"section", "section_full"})
     * @Serializer\Type("string")
     *
     * @return string
     */
    public function getEnd()
    {
        // return $this->formatTime($this->end_date, $this->end_time);
        return $this->end_date->format('Y-m-d');
    }
    
    /**
     * The date the section starts (YYYY-MM-DD).
     *
     * @Serializer\VirtualProperty()
     * @Serializer\Groups({"section", "section_full"})
     * @Serializer\Type("string")
     *
     * @return string
     */
    public function getStart()
    {
        // return $this->formatTime($this->start_date, $this->start_time);
        return $this->start_date->format('Y-m-d');
    }

    /**
     * The human readable meeting code.
     *
     * @Serializer\VirtualProperty()
     * @Serializer\Groups(groups={"section_full"})
     * @Serializer\Type("string")
     *
     * @return String
     */
    public function getMeetingTypeStr()
    {
        switch ($this->meeting_type) {
            case static::MT_CLASS:
                return 'class';
            case static::MT_WEB:
                return 'web';
            case static::MT_EXAM:
                return 'exam';
            case static::MT_LAB:
                return 'lab';
            case static::MT_CONF:
                return 'conference';
            default:
                return 'unknown';
        }
    }

    /**
     * Return an ISO8601 formatted date.
     * 
     * @param \DateTime $date_obj
     * @param  string   $time
     *
     * @return string
     */
    protected function formatTime(\DateTime $date_obj, $time)
    {
        $date   = clone $date_obj;
        $time   = 3 === strlen($time) ? '0' . $time : $time;
        $hour   = (int) substr($time, 0, 2);
        $minute = (int) substr($time, 2);

        return $date->setTime($hour, $minute)->format('c');
    }
    
    /**
     * @return Course
     */
    public function getCourse()
    {
        return $this->course;
    }
    
    /**
     * @param Course $course
     *
     * @return Section
     */
    public function setCourse(Course $course)
    {
        $this->course = $course;
        
        return $this;
    }
    
    /**
     * @return Campus
     */
    public function getCampus()
    {
        return $this->campus;
    }
    
    /**
     * @param Campus $campus
     *
     * @return Section
     */
    public function setCampus(Campus $campus)
    {
        $this->campus = $campus;
        
        return $this;
    }
    
    /**
     * @return Instructor
     */
    public function getInstructor()
    {
        return $this->instructor;
    }
    
    /**
     * @param Instructor $instructor
     *
     * @return Section
     */
    public function setInstructor(Instructor $instructor)
    {
        $this->instructor = $instructor;
        
        return $this;
    }
    
    /**
     * @return TermBlock
     */
    public function getBlock()
    {
        return $this->block;
    }
    
    /**
     * @param TermBlock $block
     *
     * @return Section
     */
    public function setBlock(TermBlock $block)
    {
        $this->block = $block;
        
        return $this;
    }
    
    /**
     * @return mixed
     */
    public function getId()
    {
        return $this->id;
    }
    
    /**
     * @return int
     */
    public function getCrn()
    {
        return $this->crn;
    }
    
    /**
     * @param int $crn
     *
     * @return Section
     */
    public function setCrn($crn)
    {
        $this->crn = $crn;
        
        return $this;
    }
    
    /**
     * @return int
     */
    public function getStatus()
    {
        return $this->status;
    }
    
    /**
     * Find the verbal version of the status.
     * 
     * @return string
     */
    public function getVerbalStatus()
    {
        switch ($this->status) {
            case static::CANCELLED:
                return 'cancelled';
            case static::ACTIVE:
                return 'active';
            default:
                return 'inactive';
        }
    }
    
    /**
     * @param int $status
     *
     * @return Section
     */
    public function setStatus($status)
    {
        if (!is_string($status) && in_array($status, [-1, 0, 1])) {
            $this->status = $status;
            return $this;
        }
        
        switch (strtolower($status)) {
            case 'a':
            case 'active':
                $this->status = static::ACTIVE;
                break;
            case 'i':
            case 'inactive':
                $this->status = static::INACTIVE;
                break;
            default:
                $this->status = static::CANCELLED;
        }
        
        return $this;
    }
    
    /**
     * @return int
     */
    public function getNumber()
    {
        return $this->number;
    }
    
    /**
     * @param int $number
     *
     * @return Section
     */
    public function setNumber($number)
    {
        $this->number = $number;
        
        return $this;
    }
    
    /**
     * @return String
     */
    public function getDays()
    {
        return $this->days;
    }
    
    /**
     * @param String $days
     *
     * @return Section
     */
    public function setDays($days)
    {
        $this->days = $days;
        
        return $this;
    }
    
    /**
     * @return string
     */
    public function getStartTime()
    {
        return $this->start_time;
    }
    
    /**
     * @param string $start_time
     *
     * @return Section
     */
    public function setStartTime($start_time)
    {
        $this->start_time = $start_time;
        
        return $this;
    }
    
    /**
     * @return string
     */
    public function getEndTime()
    {
        return $this->end_time;
    }
    
    /**
     * @param string $end_time
     *
     * @return Section
     */
    public function setEndTime($end_time)
    {
        $this->end_time = $end_time;
        
        return $this;
    }
    
    /**
     * @return int
     */
    public function getNumEnrolled()
    {
        return $this->num_enrolled;
    }
    
    /**
     * @param int $num_enrolled
     *
     * @return Section
     */
    public function setNumEnrolled($num_enrolled)
    {
        $this->num_enrolled = $num_enrolled;
        
        return $this;
    }
    
    /**
     * @return \DateTime
     */
    public function getStartDate()
    {
        return $this->start_date;
    }
    
    /**
     * @param \DateTime $start_date
     *
     * @return Section
     */
    public function setStartDate($start_date)
    {
        $this->start_date = $start_date;
        
        return $this;
    }
    
    /**
     * @return \DateTime
     */
    public function getEndDate()
    {
        return $this->end_date;
    }
    
    /**
     * @param \DateTime $end_date
     *
     * @return Section
     */
    public function setEndDate($end_date)
    {
        $this->end_date = $end_date;
        
        return $this;
    }


    
    /**
     * @return Room
     */
    public function getRoom()
    {
        return $this->room;
    }
    
    /**
     * @param Room $room
     *
     * @return Section
     */
    public function setRoom(Room $room)
    {
        if (empty($room->getNumber())) {
            return $this;
        }

        $this->room = $room;
        
        return $this;
    }

    /**
     * @param Building $building
     *
     * @return $this
     */
    public function setBuilding(Building $building)
    {
        if (empty($building->getShortname())) {
            return $this;
        }

        $this->building = $building;

        return $this;
    }
    
    /**
     * @return Subject
     */
    public function getSubject()
    {
        return $this->subject;
    }
    
    /**
     * @param Subject $subject
     *
     * @return Section
     */
    public function setSubject(Subject $subject)
    {
        $this->subject = $subject;
        
        $subject->addSection($this);
        
        return $this;
    }
    
    /**
     * @return int
     */
    public function getMaximumEnrollment()
    {
        return $this->maximum_enrollment;
    }
    
    /**
     * @param int $maximum_enrollment
     *
     * @return Section
     */
    public function setMaximumEnrollment($maximum_enrollment)
    {
        $this->maximum_enrollment = $maximum_enrollment;
        
        return $this;
    }
    
    /**
     * @return String
     */
    public function getMeetingType()
    {
        return $this->meeting_type;
    }
    
    /**
     * @param mixed $meeting_type
     *
     * @return Section
     */
    public function setMeetingType($meeting_type)
    {
        $meeting_codes = [static::MT_EXAM, static::MT_WEB, static::MT_CLASS, static::MT_CONF, static::MT_LAB];

        if (is_numeric($meeting_type) && in_array((int) $meeting_type, $meeting_codes, true)) {
            $this->meeting_type = $meeting_type;

            return $this;
        }

        switch (trim($meeting_type)) {
            case 'CLAS':
            case 'class':
            case 'TRAD':
                $this->meeting_type = static::MT_CLASS;
                
                return $this;
            case 'WEB':
            case 'web':
                $this->meeting_type = static::MT_WEB;
                
                return $this;
            case 'EXAM':
            case 'exam':
                $this->meeting_type = static::MT_EXAM;
                
                return $this;
            case 'LAB':
            case 'lab':
                $this->meeting_type = static::MT_LAB;
                
                return $this;
            case 'CONF':
            case 'conference':
                $this->meeting_type = static::MT_CONF;
                
                return $this;
            default:
                $this->meeting_type = static::MT_UNKNOWN;
        }
        
        return $this;
    }
}
