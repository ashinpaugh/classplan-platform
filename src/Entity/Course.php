<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use ForceUTF8\Encoding;
use JMS\Serializer\Annotation as Serializer;

/**
 * @ORM\Entity()
 * @ORM\Table(name="course", indexes={
 *    @ORM\Index(name="idx_number", columns={"number"})
 * })
 */
class Course extends AbstractEntity
{
    /**
     * @ORM\ManyToOne(targetEntity="Subject", inversedBy="courses", cascade={"persist"})
     * @Serializer\Exclude()
     *
     * @var Subject
     */
    protected $subject;
    
    /**
     * @ORM\OneToMany(targetEntity="Section", mappedBy="course")
     * @Serializer\Exclude()
     *
     * @var Section[]
     */
    protected $sections;
    
    /**
     * @ORM\Id()
     * @ORM\Column(name="id", type="bigint")
     * @ORM\GeneratedValue(strategy="AUTO")
     * @Serializer\Groups(groups={"course", "course_full"})
     * 
     * @var Integer
     */
    protected $id;
    
    /**
     * @ORM\Column(type="integer")
     * @Serializer\Groups(groups={"course", "course_full"})
     * 
     * @var Integer
     */
    protected $number;
    
    /**
     * @ORM\Column(type="string")
     * @Serializer\Groups(groups={"course", "course_full"})
     *
     * @var string
     */
    protected $name;
    
    /**
     * @ORM\Column(type="string", nullable=true, unique=false)
     * @Serializer\Groups(groups={"course", "course_full"})
     *
     * @var string
     */
    protected $level;
    
    /**
     * Course constructor.
     * 
     * @param Subject $subject
     * @param integer $number
     */
    public function __construct(Subject $subject, $number)
    {
        $this->setNumber($number);
        
        $this->subject  = $subject;
        $this->sections = new ArrayCollection();
    }

    /**
     * @Serializer\VirtualProperty(name="subject")
     * @Serializer\Groups(groups={"course", "course_full"})
     *
     * @return int
     */
    public function getSubjectId(): int
    {
        return $this->subject->getId();
    }

    /**
     * @Serializer\VirtualProperty(name="sections")
     * @Serializer\Groups(groups={"course_full"})
     *
     * @return array
     */
    public function getSectionIds(): array
    {
        $collection = $this->sections->map(function (Section $section) {
            return $section->getId();
        });

        return $collection->toArray();
    }
    
    /**
     * {@inheritdoc}
     */
    public function getKeyArr()
    {
        return [
            'subject' => $this->getSubject()->getName(),
            'number'  => $this->number,
        ];
    }
    
    /**
     * @return Section[]
     */
    public function getSections()
    {
        return $this->sections;
    }
    
    /**
     * @return mixed
     */
    public function getNumber()
    {
        return $this->number;
    }
    
    /**
     * @return Subject
     */
    public function getSubject()
    {
        return $this->subject;
    }
    
    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }
    
    /**
     * @return string
     */
    public function getLevel()
    {
        return $this->level;
    }
    
    /**
     * @param int $number
     *
     * @return Course
     */
    public function setNumber($number)
    {
        $this->number = Encoding::toUTF8($number);
        
        return $this;
    }
    
    /**
     * @param Subject $subject
     *
     * @return Course
     */
    public function setSubject(Subject $subject)
    {
        $this->subject = $subject;
        $subject->addCourse($this);
        
        return $this;
    }
    
    /**
     * @param string $name
     *
     * @return Course
     */
    public function setName($name)
    {
        $this->name = Encoding::toUTF8($name);
        
        return $this;
    }
    
    /**
     * @param string $level
     *
     * @return Course
     */
    public function setLevel($level)
    {
        $this->level = $level;
        
        return $this;
    }
    
    /**
     * @param Section $event
     *
     * @return $this
     */
    public function addSection(Section $event)
    {
        $this->sections->add($event);
        
        return $this;
    }
    
    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }
}
