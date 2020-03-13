<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use ForceUTF8\Encoding;
use JMS\Serializer\Annotation as Serializer;

/**
 * @ORM\Entity(repositoryClass="App\Repository\RoomRepository")
 * @ORM\Table(name="room", indexes={
 *    @ORM\Index(name="idx_number", columns={"number"})
 * })
 */
class Room extends AbstractEntity
{
    /**
     * @ORM\ManyToOne(targetEntity="Building", inversedBy="rooms", fetch="EAGER")
     * @Serializer\Groups(groups={"room_full"})
     * 
     * @var Building
     */
    protected $building;
    
    /**
     * @ORM\OneToMany(targetEntity="Section", mappedBy="room")
     * @Serializer\Groups(groups={"room_sections"})
     * @Serializer\MaxDepth(1)
     *
     * @var Section[]
     */
    protected $sections;
    
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue(strategy="AUTO")
     * @ORM\Column(name="id", type="integer")
     * @Serializer\Groups(groups={"room", "room_full"})
     * 
     * @var string
     */
    protected $id;
    
    /**
     * @ORM\Column(type="string")
     * @Serializer\Groups(groups={"room", "room_full"})
     *
     * @var string
     */
    protected $number;
    
    /**
     * Room constructor.
     *
     * @param Building $building
     * @param string   $number
     */
    public function __construct(Building $building, $number)
    {
        $this->setNumber($number);
        
        $this->building = $building;
        $this->sections = new ArrayCollection();
    }
    
    /**
     * @return Building
     */
    public function getBuilding()
    {
        return $this->building;
    }
    
    /**
     * @return string
     */
    public function getId()
    {
        return $this->id;
    }
    
    /**
     * @return mixed
     */
    public function getNumber()
    {
        return $this->number;
    }
    
    /**
     * @param mixed $number
     *
     * @return Room
     */
    public function setNumber($number)
    {
        $this->number = Encoding::toUTF8($number);
        
        return $this;
    }
}
