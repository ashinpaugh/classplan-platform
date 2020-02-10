<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use ForceUTF8\Encoding;
use JMS\Serializer\Annotation as Serializer;

/**
 * @ORM\Entity()
 * @ORM\Table(name="subject", indexes={
 *    @ORM\Index(name="idx_name", columns={"name"})
 * })
 */
class Subject extends AbstractEntity
{
    /**
     * @ORM\OneToMany(targetEntity="Course", mappedBy="subject", cascade={"persist"}, fetch="EXTRA_LAZY")
     * @Serializer\Groups(groups={"subject_courses"})
     *
     * @var Course[]
     */
    protected $courses;
    
    /**
     * @ORM\OneToMany(targetEntity="Section", mappedBy="subject", cascade={"persist"}, fetch="EXTRA_LAZY")
     * @Serializer\Groups(groups={"subject_sections"})
     *
     * @var Section[]
     */
    protected $sections;
    
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue(strategy="AUTO")
     * @ORM\Column(type="integer")
     * @Serializer\Groups(groups={"course", "default", "full"})
     *
     * @var Integer
     */
    protected $id;
    
    /**
     * @ORM\Column(type="string", unique=true)
     * @Serializer\Groups(groups={"default", "full"})
     *
     * @var String
     */
    protected $name;
    
    /**
     * Subject constructor.
     *
     * @param string $name
     */
    public function __construct($name)
    {
        $this->setName($name);
        
        $this->courses  = new ArrayCollection();
        $this->sections = new ArrayCollection();
    }
    
    /**
     * {@inheritdoc}
     */
    public function getKeyArr()
    {
        return [
            'name' => $this->name,
        ];
    }
    
    /**
     * @return Course[]|ArrayCollection
     */
    public function getCourses()
    {
        return $this->courses;
    }
    
    /**
     * Add a course.
     *
     * @param Course $course
     *
     * @return Subject
     */
    public function addCourse(Course $course)
    {
        if (!$course->getSubject()) {
            $course->setSubject($this);
        }
        
        if (!$this->courses->contains($course)) {
            $this->courses->add($course);
        }
        
        return $this;
    }
    
    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }
    
    /**
     * @param string $name
     *
     * @return $this
     */
    private function setName($name)
    {
        $this->name = Encoding::toUTF8($name);
        
        return $this;
    }
    
    /**
     * @return String
     */
    public function getName()
    {
        return $this->name;
    }
    
    /**
     * @return Section[]|ArrayCollection
     */
    public function getSections()
    {
        return $this->sections;
    }
    
    /**
     * @param Section $section
     *
     * @return Subject
     */
    public function addSection(Section $section)
    {
        $this->sections->add($section);
        
        if (!$section->getSubject()) {
            $section->setSubject($this);
        }
        
        return $this;
    }
}
