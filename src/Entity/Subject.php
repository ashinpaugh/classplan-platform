<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use ForceUTF8\Encoding;
use JMS\Serializer\Annotation as Serializer;

/**
 * @ORM\Entity(repositoryClass="App\Repository\SubjectRepository")
 * @ORM\Table(name="subject", indexes={
 *    @ORM\Index(name="idx_name", columns={"name"})
 * })
 */
class Subject extends AbstractEntity
{
    /**
     * The sections that belong to this subject.
     *
     * @ORM\OneToMany(targetEntity="Course", mappedBy="subject", cascade={"persist"}, fetch="EXTRA_LAZY")
     * @Serializer\Exclude()
     *
     * @var Course[]
     */
    protected $courses;
    
    /**
     * The sections that belong to the courses' owned by this subject.
     *
     * @ORM\OneToMany(targetEntity="Section", mappedBy="subject", cascade={"persist"}, fetch="EXTRA_LAZY")
     * @Serializer\Exclude()
     *
     * @var Section[]
     */
    protected $sections;
    
    /**
     * The unique subject id.
     *
     * @ORM\Id()
     * @ORM\GeneratedValue(strategy="AUTO")
     * @ORM\Column(type="integer")
     * @Serializer\Groups(groups={"subject", "subject_full"})
     *
     * @var Integer
     */
    protected $id;
    
    /**
     * The subject name.
     *
     * @ORM\Column(type="string", unique=true)
     * @Serializer\Groups(groups={"subject", "subject_full"})
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
     * The course ids that belong to this subject.
     *
     * @Serializer\VirtualProperty(name="courses")
     * @Serializer\Groups(groups={"subject_full"})
     * @Serializer\Type("array<integer>")
     *
     * @return int[]
     */
    public function getCourseIds(): array
    {
        $collection = $this->courses->map(function (Course $course) {
            return (int) $course->getId();
        });

        return $collection->toArray();
	}

    /**
     * The section ids belonging to this subject.
     *
     * @Serializer\VirtualProperty(name="sections")
     * @Serializer\Groups(groups={"subject_full"})
     * @Serializer\Type("array<integer>")
     *
     * @return int[]
     */
    public function getSectionIds(): array
    {
        $collection = $this->sections->map(function (Section $section) {
            return $section->getId();
        });

        return $collection->toArray();
    }
    
    /**
     * @return ArrayCollection<Course>
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
