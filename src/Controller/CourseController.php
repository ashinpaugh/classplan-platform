<?php

namespace App\Controller;

use App\Entity\Course;
use FOS\RestBundle\Routing\ClassResourceInterface;
use Nelmio\ApiDocBundle\Annotation\Operation;
use Nelmio\ApiDocBundle\Annotation\Model;
use Swagger\Annotations as SWG;
use FOS\RestBundle\Controller\Annotations as Rest;

/**
 * Course controller.
 *
 * @Rest\RouteResource("Course", pluralize=false)
 *
 * @author Austin Shinpaugh <ashinpaugh@ou.edu>
 */
class CourseController extends AbstractController implements ClassResourceInterface
{

    /**
     * Fetch all the known courses.
     *
     * @Rest\Route("/courses")
     * @Rest\View(serializerEnableMaxDepthChecks=true, serializerGroups={"course"})
     *
     * @Operation(
     *   tags={"Collections", "Course"},
     *   @SWG\Response(
     *     response="200",
     *     description="Success.",
     *     @SWG\Schema(
     *       type="object",
     *       @SWG\Property(property="courses", type="array", @SWG\Items(ref=@Model(type=Course::class, groups={"course"})))
     *     )
     *   )
     * )
     */
    public function cgetAction()
    {
        $courses = $this->getRepo(Course::class)
            ->findBy([], ['number' => 'ASC'])
        ;
        
        return ['courses' => $courses];
    }

    /**
     * Fetch a course object.
     *
     * @Rest\Route("/course/{id}", requirements={"id": "\d+"})
     * @Rest\View(serializerEnableMaxDepthChecks=true, serializerGroups={"course_full"})
     *
     * @Operation(
     *   tags={"Course"},
     *   @SWG\Parameter(
     *     name="id",
     *     in="path",
     *     description="The course ID.",
     *     required=true,
     *     type="integer",
     *     @SWG\Schema(type="integer"),
     *   ),
     *   @SWG\Response(
     *     response="200",
     *     description="Success.",
     *     @SWG\Schema(
     *       type="object",
     *       @SWG\Property(property="course", ref=@Model(type=Course::class, groups={"course_full"}))
     *     )
     *   )
     * )
     */
    public function getAction(Course $course)
    {
        return ['course' => $course];
    }
}
