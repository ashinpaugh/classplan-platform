<?php

namespace App\Controller;

use App\Entity\Course;
use App\Entity\Subject;
use FOS\RestBundle\Routing\ClassResourceInterface;
use FOS\RestBundle\Controller\Annotations as Rest;
use Nelmio\ApiDocBundle\Annotation\Model;
use Nelmio\ApiDocBundle\Annotation\Operation;
use Swagger\Annotations as SWG;

/**
 * Loads a subject.
 *
 * @Rest\RouteResource("Subject", pluralize=false)
 * 
 * @author Austin Shinpaugh <ashinpaugh@ou.edu>
 */
class SubjectController extends AbstractController implements ClassResourceInterface
{
    /**
     * Get the list of all known subjects.
     *
     * @Rest\Route("/subjects")
     * @Rest\View(serializerEnableMaxDepthChecks=true, serializerGroups={"subject"})
     *
     * @Operation(
     *   tags={"Collections", "Subject"},
     *   @SWG\Response(
     *     response="200",
     *     description="Success.",
     *     @SWG\Schema(
     *       type="object",
     *       @SWG\Property(property="subjects", type="array", @SWG\Items(ref=@Model(type=Subject::class, groups={"subject"})))
     *     )
     *   )
     * )
     */
    public function cgetAction()
    {
        $subjects = $this->getRepo(Subject::class)
            ->findAll()
        ;

        return ['subjects' => $subjects];
    }

    /**
     * Fetch a subject.
     *
     * @Rest\Route("/subject/{id}", requirements={"id": "\d+|\w+"})
     * @Rest\View(serializerEnableMaxDepthChecks=true, serializerGroups={"subject_full"})
     *
     * @Operation(
     *   tags={"Subject"},
     *   @SWG\Parameter(
     *     name="id",
     *     in="path",
     *     description="The subject id or short-name.",
     *     required=true,
     *     type="string"
     *   ),
     *   @SWG\Response(
     *     response="200",
     *     description="Success.",
     *     @SWG\Schema(
     *       type="object",
     *       @SWG\Property(property="subject", ref=@Model(type=Subject::class, groups={"subject_full"}))
     *     )
     *   )
     * )
     *
     * @return array
     */
    public function getAction($id)
    {
        $repo = $this->getRepo(Subject::class);

        return ['subject' => $repo->getOneByIndex($id)];
    }

    /**
     * Fetch a specific course.
     *
     * @Rest\Route("/subject/{subject}/course/{number}",
     *   name="_by_number",
     *   requirements={
     *     "subject": "\w+|\d+",
     *     "number": "\d+"
     * })
     * @Rest\View(serializerEnableMaxDepthChecks=true, serializerGroups={"subject", "course_full"})
     *
     * @Operation(
     *   tags={"Course"},
     *   summary="Fetch a specific course.",
     *   @SWG\Parameter(
     *     name="subject",
     *     in="path",
     *     description="The subject id or short-name.",
     *     required=true,
     *     type="string"
     *   ),
     *   @SWG\Parameter(
     *     name="number",
     *     in="path",
     *     description="The course number.",
     *     required=true,
     *     type="integer"
     *   ),
     *   @SWG\Response(
     *     response="200",
     *     description="Success.",
     *     @SWG\Schema(
     *       type="object",
     *       @SWG\Property(property="subject", ref=@Model(type=Subject::class, groups={"subject"})),
     *       @SWG\Property(property="course", ref=@Model(type=Course::class, groups={"course_full"}))
     *     )
     *   )
     * )
     *
     * @return array
     */
    public function getCourseAction(string $subject, int $number)
    {
        $subject = $this->getRepo(Subject::class)->getOneByIndex($subject);
        $course  = $this->getRepo(Course::class)
            ->findOneBy([
                'subject' => $subject,
                'number'  => $number,
            ])
        ;

        return [
            'subject' => $subject,
            'course'  => $course,
        ];
    }
}
