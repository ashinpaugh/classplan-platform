<?php

namespace App\Controller;

use App\Entity\Instructor;
use App\Entity\Section;
use App\Entity\Subject;
use App\Entity\Term;
use App\Entity\TermBlock;
use FOS\RestBundle\Controller\Annotations as Rest;
use FOS\RestBundle\Routing\ClassResourceInterface;
use Nelmio\ApiDocBundle\Annotation\Model;
use Nelmio\ApiDocBundle\Annotation\Operation;
use Swagger\Annotations as SWG;

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
     * @Rest\View(serializerEnableMaxDepthChecks=true, serializerGroups={"term_full"})
     *
     * @Operation(
     *   method="get",
     *   tags={"Collections", "Term"},
     *   @SWG\Response(
     *     response="200",
     *     description="Success.",
     *     @SWG\Schema(
     *       type="object",
     *       @SWG\Property(property="terms", type="array", @SWG\Items(ref=@Model(type=Term::class, groups={"term"})))
     *     )
     *   )
     * )
     *
     * @return array
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
     * @Rest\Route("/term/{id}", requirements={"id": "\d+"})
     * @Rest\View(serializerEnableMaxDepthChecks=true, serializerGroups={"term_full"})
     *
     * @Operation(
     *   method="get",
     *   tags={"Term"},
     *   @SWG\Response(
     *     response="200",
     *     description="Success.",
     *     @SWG\Schema(
     *       type="object",
     *       @SWG\Property(property="term", @SWG\Items(ref=@Model(type=Term::class, groups={"term_full", "block_full"})))
     *     )
     *   )
     * )
     *
     * @param Term $term
     * @return array
     */
    public function getAction(Term $term)
    {
        return ['term' => $term];
    }

    /**
     * Fetch all the subjects taught in a block.
     *
     * @Rest\Route("/term/{block}/subjects", requirements={"block": "\d+"})
     * @Rest\View(serializerEnableMaxDepthChecks=true, serializerGroups={"block_full", "subject"})
     *
     * @Operation(
     *   tags={"Subject"},
     *   summary="Fetch the subjects taught in a term.",
     *   @SWG\Parameter(
     *     name="block",
     *     in="path",
     *     description="The term id.",
     *     required=true,
     *     type="integer",
     *     @SWG\Schema(type="integer"),
     *   ),
     *   @SWG\Response(
     *     response="200",
     *     description="Success.",
     *     @SWG\Schema(
     *       type="object",
     *       @SWG\Property(property="block", ref=@Model(type=TermBlock::class, groups={"block_full"})),
     *       @SWG\Property(property="subjects", type="array", @SWG\Items(
     *         type="object",
     *         @SWG\Property(property="id", type="number"),
     *         @SWG\Property(property="name", type="string")
     *       ))
     *     )
     *   )
     * )
     *
     * @param TermBlock $block
     * @return array
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
     * Fetches all the instructors and groups them by subject name for a given block.
     *
     * @Rest\Route("/term/{block}/subject/{subject}/instructors",
     *   requirements={
     *     "block": "\d+",
     *     "subject": "\d+|\w+"
     *   }
     * )
     * @Rest\View(serializerEnableMaxDepthChecks=true, serializerGroups={"block_full", "subject_full", "instructor"})
     *
     * @Operation(
     *   tags={"Instructor"},
     *   summary="Fetch the subjects taught in a term.",
     *   @SWG\Parameter(
     *     name="block",
     *     in="path",
     *     description="The term id.",
     *     required=true,
     *     type="integer",
     *     @SWG\Schema(type="integer"),
     *   ),
     *   @SWG\Parameter(
     *     name="subject",
     *     in="path",
     *     description="The subject id or name.",
     *     required=true,
     *     type="string",
     *     @SWG\Schema(type="string"),
     *   ),
     *   @SWG\Response(
     *     response="200",
     *     description="Success.",
     *     @SWG\Schema(
     *       type="object",
     *       @SWG\Property(property="block", ref=@Model(type=TermBlock::class, groups={"block_full"})),
     *       @SWG\Property(property="subject", ref=@Model(type=Subject::class, groups={"subject_full"})),
     *       @SWG\Property(property="instructors", type="object", additionalProperties=@SWG\Schema(
     *         type="array",
     *         @SWG\Items(
     *           type="object",
     *           @SWG\Property(property="id", type="number"),
     *           @SWG\Property(property="name", type="string")
     *         ))
     *       )
     *     )
     *   )
     * )
     */
    public function getSubjectInstructorsAction(TermBlock $block, $subject)
    {
        $subject     = $this->getRepo(Subject::class)->getOneByIndex($subject);
        $instructors = $this->getRepo(Instructor::class)
            ->getInstructorsBySubject($block, $subject)
        ;

        return [
            'block'       => $block,
            'subject'     => $subject,
            'instructors' => $instructors,
        ];
    }

    /**
     * Fetches all the known instructors and groups them by subject name for a given block.
     *
     * @Rest\Route("/term/{block}/instructors",requirements={"block": "\d+"})
     * @Rest\View(serializerEnableMaxDepthChecks=true, serializerGroups={"block_full", "instructor"})
     *
     * @Operation(
     *   tags={"Instructor"},
     *   summary="Fetch the instructors who are teaching this term.",
     *   @SWG\Parameter(
     *     name="block",
     *     in="path",
     *     description="The term id.",
     *     required=true,
     *     type="integer",
     *     @SWG\Schema(type="integer"),
     *   ),
     *   @SWG\Response(
     *     response="200",
     *     description="Success.",
     *     @SWG\Schema(
     *       type="object",
     *       @SWG\Property(property="block", ref=@Model(type=TermBlock::class, groups={"block_full"})),
     *       @SWG\Property(property="instructors", type="object", additionalProperties=@SWG\Schema(
     *         type="array",
     *         @SWG\Items(
     *           type="object",
     *           @SWG\Property(property="id", type="number"),
     *           @SWG\Property(property="name", type="string")
     *         ))
     *       )
     *     )
     *   )
     * )
     */
    public function getInstructorsAction(TermBlock $block)
    {
        $instructors = $this->getRepo(Instructor::class)
            ->getInstructorsBySubject($block)
        ;

        return [
            'block'       => $block,
            'instructors' => $instructors,
        ];
    }

    /**
     * Get the subjects taught by an instructor.
     *
     * @Rest\Route("/term/{block}/instructor/{instructor}/subjects",
     *   requirements={
     *     "block": "\d+",
     *     "instructor": "\d+"
     *   }
     * )
     * @Rest\View(serializerEnableMaxDepthChecks=true, serializerGroups={"block_full", "instructor", "subject"})
     *
     * @Operation(
     *   tags={"Subject"},
     *   summary="Fetch the subjects taught in a term.",
     *   @SWG\Parameter(
     *     name="block",
     *     in="path",
     *     description="The term id.",
     *     required=true,
     *     type="integer",
     *     @SWG\Schema(type="integer"),
     *   ),
     *   @SWG\Parameter(
     *     name="instructor",
     *     in="path",
     *     description="The instructor id.",
     *     required=true,
     *     type="integer",
     *     @SWG\Schema(type="integer"),
     *   ),
     *   @SWG\Response(
     *     response="200",
     *     description="Success.",
     *     @SWG\Schema(
     *       type="object",
     *       @SWG\Property(property="block", ref=@Model(type=TermBlock::class, groups={"block_full"})),
     *       @SWG\Property(property="instructor", ref=@Model(type=Instructor::class, groups={"instructor"})),
     *       @SWG\Property(property="subjects", type="array", @SWG\Items(ref=@Model(type=Subject::class, groups={"subject"})))
     *     )
     *   )
     * )
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
