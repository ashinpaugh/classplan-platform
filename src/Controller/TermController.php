<?php

namespace App\Controller;

use App\Entity\Section;
use App\Entity\Subject;
use App\Entity\Term;
use App\Entity\TermBlock;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Util\Debug;
use FOS\RestBundle\Controller\Annotations as Rest;
use FOS\RestBundle\Routing\ClassResourceInterface;

/**
 * Term controller.
 *
 * @Rest\RouteResource("Term", pluralize=false)
 * 
 * @author Austin Shinpaugh <ashinpaugh@ou.edu>
 */
class TermController extends AbstractController implements ClassResourceInterface
{
    /**
     * Fetch a collection of terms and term blocks.
     *
     * @Rest\Route("/terms")
     * @Rest\View(serializerEnableMaxDepthChecks=true, serializerGroups={"term"})
     */
    public function cgetAction()
    {
        $terms = $this->getRepo(Term::class)
            ->findBy([], ['year' => 'DESC', 'semester' => 'ASC'])
        ;
        
        return ['terms' => $terms];
    }

    /**
     * Fetch a collection of terms and term blocks.
     *
     * @Rest\Route("/term/{id}", requirements={
     *     "id": "\d+"
     * })
     *
     * @Rest\View(serializerEnableMaxDepthChecks=true, serializerGroups={"term_full"})
     */
    public function getAction(Term $term)
    {
        return ['term' => $term];
    }

    /**
     * Fetch all the subjects taught in a block.
     *
     * @Rest\Route("/term/{block}/subjects", requirements={
     *     "block": "\d+"
     * })
     *
     * @Rest\View(serializerEnableMaxDepthChecks=true, serializerGroups={"block_full", "subject"})
     *
     * @param TermBlock $block
     */
    public function getSubjectsAction(TermBlock $block)
    {
        /*$sections = $this->getRepo(Section::class)
            ->findBy([
                'block' => $block,
            ])
        ;

        $subjects = [];
        */

        $subjects = $this->getRepo(Subject::class)
            ->getByBlock($block)
        ;

        /* @var Section $section */
        /*foreach ($sections as $section) {
            if (!$section instanceof Section) {
                continue;
            }

            $subject    = $section->getSubject();
            $subject_id = $subject->getId();

            if (array_key_exists($subject_id, $subjects)) {
                continue;
            }

            $subjects[$subject_id] = $subject;
        }*/

        return [
            'block'    => $block,
            'subjects' => $subjects,
        ];
    }
}
