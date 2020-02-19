<?php

namespace App\Controller;

use App\Entity\Instructor;
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
        $subjects = $this->getRepo(Subject::class)
            ->getByBlock($block)
        ;

        return [
            'block'    => $block,
            'subjects' => $subjects,
        ];
    }

    /**
     * Fetches all the known instructors and groups them by subject name.
     *
     * @Rest\Route("/term/{block}/subject/{subject}/instructors", defaults={"subject"=0}, requirements={
     *     "block": "\d+",
     *     "subject": "\d+"
     * })
     *
     * @Rest\View(serializerEnableMaxDepthChecks=true, serializerGroups={"block_full", "instructor"})
     */
    public function getSubjectInstructorsAction(TermBlock $block, int $subject)
    {
        $instructors = $this->getRepo(Instructor::class)
            ->getInstructorsBySubject($block, $subject)
        ;

        return [
            'block'       => $block,
            'instructors' => $instructors,
        ];
    }

    /**
     * @Rest\Route("/term/{block}/instructor/{instructor}/subjects", requirements={
     *   "block": "\d+",
     *   "instructor": "\d+"
     * })
     *
     * @Rest\View(serializerEnableMaxDepthChecks=true, serializerGroups={"block_full", "instructor", "subject"})
     *
     * @param TermBlock  $block
     * @param Instructor $instructor
     *
     * @return array
     */
    public function getInstructorSubjectsAction(TermBlock $block, Instructor $instructor)
    {
        $sections = $this->getRepo(Section::class)
            ->findBy([
                'block'      => $block,
                'instructor' => $instructor,
            ])
        ;

        $subjects = [];

        /* @var Section $section */
        foreach ($sections as $section) {
            $subject = $section->getSubject();

            if (array_key_exists($subject->getId(), $subjects)) {
                continue;
            }

            $subjects[$subject->getId()] = $subject;
        }


        return [
            'block'      => $block,
            'instructor' => $instructor,
            'subjects'   => array_values($subjects),
        ];
    }
}
