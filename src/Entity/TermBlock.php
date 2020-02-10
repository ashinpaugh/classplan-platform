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
     * @Serializer\MaxDepth(1)
     * 
     * @ORM\ManyToOne(targetEntity="Term", inversedBy="blocks", fetch="EXTRA_LAZY", cascade={"persist"})
     * @Serializer\Groups(groups={"default"})
     *
     * @var Term
     */
    protected $term;
    
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue(strategy="AUTO")
     * @ORM\Column(type="integer")
     * @Serializer\Groups(groups={"default"})
     * 
     * @var Integer
     */
    protected $id;
    
    /**
     * @ORM\Column(type="string")
     * @Serializer\Groups(groups={"default"})
     *
     * @var String
     */
    protected $name;
    
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
            ->setName($name)
        ;
    }
    
    /**
     * {@inheritdoc}
     */
    public function getKeyArr()
    {
        return [
            'term' => $this->getTerm()->getName(),
            'name' => $this->name,
        ];
    }
    
    /**
     * @Serializer\VirtualProperty()
     * @Serializer\Groups(groups={"default"})
     */
    public function getDisplayName()
    {
        switch ($this->getName()) {
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
                return $this->getName();
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
    public function getName()
    {
        return $this->name;
    }
    
    /**
     * @param String $name
     *
     * @return TermBlock
     */
    public function setName($name)
    {
        if ('exam' === strtolower($name)) {
            $name = 4;
        }
        
        $this->name = $name;
        
        return $this;
    }
}
