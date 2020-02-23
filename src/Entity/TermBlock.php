<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation as Serializer;

/**
 * @ORM\Entity()
 */
class TermBlock extends AbstractEntity
{
    /**
     * The term this block belongs to.
     *
     * @ORM\ManyToOne(targetEntity="Term", inversedBy="blocks", fetch="EXTRA_LAZY", cascade={"persist"})
     * @Serializer\Groups(groups={"block_full", "section_full"})
     * @Serializer\MaxDepth(1)
     *
     * @var Term
     */
    protected $term;
    
    /**
     * The unique block id.
     *
     * @ORM\Id()
     * @ORM\GeneratedValue(strategy="AUTO")
     * @ORM\Column(type="integer")
     * @Serializer\Groups(groups={"block", "block_full", "term", "term_full", "section_full"})
     * 
     * @var Integer
     */
    protected $id;
    
    /**
     * The book provides the full name, ODS provides an abbreviation.
     *
     * @ORM\Column(type="string")
     * @Serializer\Exclude()
     *
     * @var string
     */
    protected $short_name;
    
    /**
     * TermBlock constructor.
     *
     * @param Term   $term
     * @param string $name
     */
    public function __construct(Term $term, $name)
    {
        $this
            ->setTerm($term)
            ->setShortName($name)
        ;
    }

    /**
     * The owning term's id.
     *
     * @Serializer\VirtualProperty()
     * @Serializer\Groups(groups={"block"})
     * @Serializer\Type("integer")
     */
    public function getTermId(): int
    {
        return $this->term->getId();
    }

    /**
     * Maps the block's short-name to a human readable format.
     *
     * @Serializer\VirtualProperty()
     * @Serializer\Groups(groups={"block", "block_full", "term", "term_full", "section_full"})
     * @Serializer\Type("string")
     */
    public function getName(): string
    {
        switch ($this->getShortName()) {
            case 1:
                return 'Full Semester';
            case 2:
                return 'Module 1 (1st Half)';
            case 3:
                return 'Module 2 (2nd Half)';
            case 4:
                // 4 doesn't exist in the ODS DB.
                return 'Exam';
            case 'DEC':
                return 'December';
            case 'NCE':
                return 'Norman Contract Enrollment';
            case 'JNX':
                return 'JANIX credit';
            case 'L01':
                return 'Liberal Studies 1';
            case 'L02':
                return 'Liberal Studies 2';
            case 'L03':
                return 'Liberal Studies 3';
            default:
                return $this->getShortName();
        }
    }
    
    /**
     * @return Term
     */
    public function getTerm()
    {
        return $this->term;
    }
    
    /**
     * @param Term $term
     *
     * @return TermBlock
     */
    public function setTerm(Term $term)
    {
        $this->term = $term;
        
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
    public function getShortName()
    {
        return $this->short_name;
    }
    
    /**
     * @param String $name
     *
     * @return TermBlock
     */
    public function setShortName(string $name)
    {
        if ('exam' === strtolower($name)) {
            $name = 4;
        }
        
        $this->short_name = $name;
        
        return $this;
    }
}
