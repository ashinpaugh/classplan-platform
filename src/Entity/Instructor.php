<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use ForceUTF8\Encoding;
use JMS\Serializer\Annotation as Serializer;

/**
 * @ORM\Entity(repositoryClass="App\Repository\InstructorRepository")
 */
class Instructor extends AbstractEntity
{
    /**
     * @ORM\OneToMany(targetEntity="Section", mappedBy="instructor")
     * @Serializer\Exclude()
     *
     * @var Section[]
     */
    protected $sections;
    
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue(strategy="NONE")
     * @ORM\Column(type="bigint")
     * @Serializer\Groups(groups={"instructor", "instructor_full"})
     * 
     * @var Integer
     */
    protected $id;
    
    /**
     * @ORM\Column(name="name", type="string")
     * @Serializer\Groups(groups={"instructor", "instructor_full"})
     *
     * @var String
     */
    protected $name;
    
    /**
     * @ORM\Column(name="email", type="string", nullable=true)
     *
     * @var String
     */
    protected $email;
    
    /**
     * Instructor constructor.
     *
     * @param int    $id
     * @param string $name
     */
    public function __construct($id, $name)
    {
        $this
            ->setId($id)
            ->setName($name)
        ;
        
        $this->sections = new ArrayCollection();
    }

    /**
     * @Serializer\VirtualProperty(name="sections")
     * @Serializer\Groups(groups={"instructor_full"})
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
     * Set the instructor's ID.
     * 
     * @param integer $id
     *
     * @return $this
     */
    private function setId($id)
    {
        $this->id = $id;
        
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
     * @return String
     */
    public function getName()
    {
        return $this->name;
    }
    
    /**
     * @param String $name
     *
     * @return Instructor
     */
    public function setName($name)
    {
        $this->name = Encoding::toUTF8($name);
        
        return $this;
    }
    
    /**
     * @return Section[]
     */
    public function getSections()
    {
        return $this->sections;
    }
    
    /**
     * @param Section $event
     *
     * @return Instructor
     */
    public function addSection(Section $event)
    {
        $this->sections->add($event);
        
        return $this;
    }
    
    /**
     * @param Section $class
     *
     * @return Instructor
     */
    public function removeSection(Section $class)
    {
        $this->sections->removeElement($class);
        
        return $this;
    }
    
    /**
     * @return String
     */
    public function getEmail()
    {
        return $this->email;
    }
    
    /**
     * @param String $email
     *
     * @return Instructor
     */
    public function setEmail($email)
    {
        $this->email = $email ?: '';
        
        return $this;
    }
}
