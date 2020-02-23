<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation as Serializer;

/**
 * @ORM\Entity()
 * @ORM\Table(name="term", indexes={
 *    @ORM\Index(name="idx_year_semester", columns={"year", "semester"}),
 * })
 */
class Term extends AbstractEntity
{
    /**
     * The blocks assigned to this term.
     *
     * @ORM\OneToMany(targetEntity="TermBlock", mappedBy="term", fetch="EAGER", cascade={"detach"})
     * @Serializer\Groups(groups={"term", "term_full"})
     *
     * @var TermBlock[]
     */
    protected $blocks;
    
    /**
     * The unique building id.
     *
     * @ORM\Id()
     * @ORM\GeneratedValue(strategy="AUTO")
     * @ORM\Column(type="integer")
     * @Serializer\Groups(groups={"term", "term_full", "block_full", "section_full"})
     * 
     * @var Integer
     */
    protected $id;
    
    /**
     * The full name of the term (Semester + Year).
     *
     * @ORM\Column(type="string")
     * @Serializer\Groups(groups={"term", "term_full", "block_full", "section_full"})
     *
     * @var String
     */
    protected $name;
    
    /**
     * The term year.
     *
     * @ORM\Column(type="integer")
     * @Serializer\Groups(groups={"term", "term_full", "block_full", "section_full"})
     *
     * @var Integer
     */
    protected $year;
    
    /**
     * The term semester.
     *
     * @ORM\Column(type="string")
     * @Serializer\Groups(groups={"term", "term_full", "block_full", "section_full"})
     *
     * @var String
     */
    protected $semester;
    
    /**
     * Term constructor.
     *
     * @param string  $name
     * @param integer $year
     * @param string  $semester
     */
    public function __construct($name, $year, $semester)
    {
        $this->setName($name);
        
        $this->year     = $year;
        $this->semester = $semester;
        $this->blocks   = new ArrayCollection();
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
     * @return Term
     */
    public function setName($name)
    {
        $this->name = $name;
        
        return $this;
    }
    
    /**
     * @return ArrayCollection<TermBlock>
     */
    public function getBlocks()
    {
        return $this->blocks;
    }
    
    /**
     * @param TermBlock $block
     *
     * @return Term
     */
    public function addBlock(TermBlock $block)
    {
        $this->blocks->add($block);
        $block->setTerm($this);
        
        return $this;
    }
    
    /**
     * @return int
     */
    public function getYear()
    {
        return $this->year;
    }
    
    /**
     * @return String
     */
    public function getSemester()
    {
        return $this->semester;
    }
}
