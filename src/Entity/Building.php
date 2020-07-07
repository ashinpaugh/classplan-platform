<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use ForceUTF8\Encoding;
use JMS\Serializer\Annotation as Serializer;

/**
 * @see https://directory.ouhsc.edu/Contacts/BuildingLocations.aspx
 *
 * @ORM\Entity(repositoryClass="App\Repository\BuildingRepository")
 * @ORM\Table(name="building", indexes={
 *    @ORM\Index(name="idx_name", columns={"short_name"})
 * })
 */
class Building extends AbstractEntity
{
    /**
     * @ORM\ManyToOne(targetEntity="Campus", inversedBy="buildings", cascade={"all"}, fetch="EAGER")
     * @Serializer\Groups(groups={"building_full"})
     * @Serializer\MaxDepth(1)
     * 
     * @var Campus
     */
    protected $campus;
    
    /**
     * @ORM\OneToMany(targetEntity="Room", mappedBy="building", cascade={"all"}, fetch="EAGER")
     * @Serializer\Exclude()
     *
     * @var Room[]
     */
    protected $rooms;
    
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue(strategy="AUTO")
     * @ORM\Column(name="id", type="bigint")
     * @Serializer\Groups(groups={"building", "building_full"})
     * 
     * @var Integer
     */
    protected $id;
    
    /**
     * The building abbreviation (found in TheBook and ODS).
     *
     * @ORM\Column(type="string")
     * @Serializer\Groups(groups={"building", "building_full"})
     *
     * @var string
     */
    protected $short_name;

    /**
     * The building's full name.
     *
     * This field is only used when working with TheBook imports.
     *
     * @ORM\Column(type="string", nullable=true, length=150)
     * @Serializer\Exclude()
     *
     * @var string
     */
    protected $full_name;

    /**
     * Building constructor.
     *
     * @param Campus $campus
     * @param string $abbreviation
     * @param string $full_name
     */
    public function __construct(Campus $campus, string $abbreviation, string $full_name = null)
    {
        $this
            ->setCampus($campus)
            ->setShortname($abbreviation)
            ->setFullName($full_name)
        ;
        
        $this->rooms = new ArrayCollection();
    }

    /**
     * Provide the best name available for front-end users to select from.
     *
     * @Serializer\VirtualProperty()
     * @Serializer\Groups(groups={"building", "building_full"})
     * @Serializer\Type("string")
     *
     * @return string
     */
    public function getName(): string
    {
        return $this->full_name ?: $this->short_name;
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
     * @return Building
     */
    public function setCampus(Campus $campus)
    {
        $this->campus = $campus;
        
        return $this;
    }
    
    /**
     * @Serializer\VirtualProperty(name="rooms")
     * @Serializer\Groups(groups={"building_full"})
     * @Serializer\Type("ArrayCollection<App\Entity\Room>")
     * @Serializer\MaxDepth(1)
     *
     * @return Room[]
     */
    public function getRooms()
    {
        return $this->rooms;
    }
    
    /**
     * @param Room $room
     *
     * @return Building
     */
    public function addRoom(Room $room)
    {
        $this->rooms->add($room);
        
        return $this;
    }
    
    /**
     * @param Room $room
     *
     * @return Building
     */
    public function removeRoom(Room $room)
    {
        $this->rooms->removeElement($room);
        
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
     * @param int $id
     *
     * @return Building
     */
    public function setId($id)
    {
        $this->id = $id;
        
        return $this;
    }
    
    /**
     * @return string
     */
    public function getFullName()
    {
        return $this->full_name;
    }
    
    /**
     * @param string $full_name
     *
     * @return Building
     */
    public function setFullName(?string $full_name)
    {
        $this->full_name = Encoding::toUTF8($full_name);
        
        return $this;
    }

    /**
     * @return string
     */
    public function getShortname(): string
    {
        return $this->short_name;
    }

    /**
     * @param string $short_name
     */
    public function setShortname(string $short_name)
    {
        $this->short_name = Encoding::toUTF8($short_name);

        return $this;
    }
}
