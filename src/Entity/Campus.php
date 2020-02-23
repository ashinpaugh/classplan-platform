<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use ForceUTF8\Encoding;
use JMS\Serializer\Annotation as Serializer;

/**
 * @ORM\Entity()
 * @ORM\Table(name="campus", indexes={
 *    @ORM\Index(name="idx_name", columns={"short_name"})
 * })
 */
class Campus extends AbstractEntity
{
    /**
     * @ORM\OneToMany(targetEntity="Building", mappedBy="campus", cascade={"all"})
     * @Serializer\Exclude()
     *
     * @var Building[]
     */
    protected $buildings;
    
    /**
     * @ORM\OneToMany(targetEntity="Section", mappedBy="campus")
     * @Serializer\Exclude()
     *
     * @var Section[]
     */
    protected $sections;
    
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue(strategy="AUTO")
     * @ORM\Column(name="id", type="bigint")
     * @Serializer\Groups(groups={"campus", "campus_full", "building_full"})
     * 
     * @var Integer
     */
    protected $id;
    
    /**
     * @ORM\Column(type="string")
     * @Serializer\Exclude()
     *
     * @var String
     */
    protected $short_name;
    
    /**
     * Campus constructor.
     *
     * @param string $name
     */
    public function __construct($name)
    {
        $this->setShortName($name);
        
        $this->buildings = new ArrayCollection();
        $this->sections  = new ArrayCollection();
    }

    /**
     * @Serializer\VirtualProperty(name="buildings")
     * @Serializer\Groups(groups={"campus_full"})
     * @Serializer\Type("array<integer>")
     *
     * @return int[]
     */
    public function getBuildingIds(): array
    {
        $collection = $this->buildings->map(function (Building $building) {
            return $building->getId();
        });

        return $collection->toArray();
    }
    
    /**
     * @Serializer\VirtualProperty()
     * @Serializer\Groups(groups={"campus", "building_full"})
     * @Serializer\Type("string")
     */
    public function getName()
    {
        switch ($this->getShortName()) {
            case 'A':
                return 'Advanced Programs';
            case 'H':
                return 'Health Science Center';
            case 'N':
                return 'Norman - Main Campus';
            case 'T':
                return 'Tulsa Campus';
            case 'I':
                return 'Independent Campus';
            case 'L':
                return 'Liberal Studies';
            case 'O':
                return 'Outreach Academic Programs';
            case 'R':
                return 'Redlands at Norman CCE';
            case 'S':
                return 'Intersession';
            case 'J':
                return 'Janux Campus';
            default:
                return $this->getShortName();
        }
    }
    
    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }
    
    /**
     * @param int $id
     *
     * @return Campus
     */
    public function setId($id)
    {
        $this->id = $id;
        
        return $this;
    }
    
    /**
     * @return mixed
     */
    public function getShortName()
    {
        return $this->short_name;
    }
    
    /**
     * @param mixed $short_name
     *
     * @return Campus
     */
    public function setShortName(string $short_name)
    {
        $this->short_name = Encoding::toUTF8($short_name);
        
        return $this;
    }
    
    /**
     * @return Building[]
     */
    public function getBuildings()
    {
        return $this->buildings;
    }
    
    /**
     * @param Building $building
     *
     * @return Campus
     */
    public function addBuilding(Building $building)
    {
        $this->buildings->add($building);
        $building->setCampus($this);
        
        return $this;
    }
    
    /**
     * @param Building $building
     *
     * @return Campus
     */
    public function removeBuilding(Building $building)
    {
        $this->buildings->remove($building);
        
        return $this;
    }
    
    /**
     * @return mixed
     */
    public function getSections()
    {
        return $this->sections;
    }
    
    /**
     * @param Section $section
     *
     * @return Campus
     */
    public function addSection(Section $section)
    {
        if (!$this->sections->contains($section)) {
            $this->sections->add($section);
        }
        
        return $this;
    }
}
