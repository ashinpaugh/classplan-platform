<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use ForceUTF8\Encoding;
use JMS\Serializer\Annotation as Serializer;

/**
 * @see https://directory.ouhsc.edu/Contacts/BuildingLocations.aspx
 *
 * @ORM\Entity()
 * @ORM\Table(name="building", indexes={
 *    @ORM\Index(name="idx_name", columns={"abbreviation"})
 * })
 */
class Building extends AbstractEntity
{
    /**
     * @ORM\ManyToOne(targetEntity="Campus", inversedBy="buildings", cascade={"all"})
     * @Serializer\MaxDepth(1)
     * @Serializer\Groups(groups={"building_full"})
     * 
     * @var Campus
     */
    protected $campus;
    
    /**
     * @ORM\OneToMany(targetEntity="Room", mappedBy="building", cascade={"all"})
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
     * @Serializer\Groups(groups={"building_full"})
     *
     * @var string
     */
    protected $abbreviation;

    /**
     * The five digit ou building code.
     *
     * @ORM\Column(type="string", nullable=true, length=5)
     * @Serializer\Groups(groups={"building_full"})
     * @Serializer\SkipWhenEmpty()
     *
     * @var string
     */
    protected $code;

    /**
     * The building's full name.
     *
     * @ORM\Column(type="string", nullable=true, length=120)
     * @Serializer\Exclude()
     *
     * @var string
     */
    protected $short_name;
    
    /**
     * Building constructor.
     *
     * @param Campus $campus
     * @param string $name
     */
    public function __construct(Campus $campus, string $abbreviation, string $code = null, string $name = null)
    {
        $this
            ->setCampus($campus)
            ->setAbbreviation($abbreviation)
            ->setCode($code)
            ->setShortName($name)
        ;
        
        $this->rooms = new ArrayCollection();
    }

    /**
     * @Serializer\VirtualProperty(name="rooms")
     * @Serializer\Groups(groups={"building_full"})
     *
     * @return int[]
     */
    public function getRoomIds(): array
    {
        $collection = $this->rooms->map(function (Room $room) {
            return $room->getId();
        });

        return $collection->toArray();
    }

    /**
     * Provide the best name available for front-end users to select from.
     *
     * @Serializer\VirtualProperty()
     * @Serializer\Groups(groups={"building", "building_full"})
     *
     * @return string
     */
    public function getName(): string
    {
        return $this->short_name ?: $this->abbreviation;
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
    public function getShortName()
    {
        return $this->short_name;
    }
    
    /**
     * @param string $short_name
     *
     * @return Building
     */
    public function setShortName(?string $short_name)
    {
        $this->short_name = Encoding::toUTF8($short_name);
        
        return $this;
    }

    /**
     * @return string
     */
    public function getAbbreviation(): string
    {
        return $this->abbreviation;
    }

    /**
     * @param string $abbreviation
     */
    public function setAbbreviation(string $abbreviation)
    {
        $this->abbreviation = Encoding::toUTF8($abbreviation);

        return $this;
    }

    /**
     * @return string
     */
    public function getCode(): string
    {
        return $this->code;
    }

    /**
     * @param string $code
     */
    public function setCode(?string $code)
    {
        $this->code = Encoding::toUTF8($code);

        return $this;
    }

}
