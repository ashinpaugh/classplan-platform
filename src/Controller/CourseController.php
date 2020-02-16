<?php

namespace App\Controller;

use App\Entity\Course;
use FOS\RestBundle\Request\ParamFetcher;
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
     * @Rest\Route("/courses")
     * @Rest\View(serializerEnableMaxDepthChecks=true, serializerGroups={"course"})
     *
     * @SWG\Response(
     *    response="200",
     *    description="Courses returned successfully.",
     *    @SWG\Schema(
     *        type="array",
     *        @SWG\Items(ref=@Model(type=Course::class))
     *    )
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
     * Fetch a specific course.
     * 
     * @Operation(
     *     tags={""},
     *     summary="Fetch a specific course.",
     *     @SWG\Response(
     *         response="200",
     *         description="Returned when successful"
     *     )
     * )
     *
     * @Rest\Route("/course", requirements={
     *     "subject": "\d+",
     *     "course":  "\d+"
     * })
     *
     * @Rest\QueryParam(
     *     name="subject",
     *     description="The subject/department id.",
     *     allowBlank=false,
     *     requirements="\d+"
     * )
     *
     * @Rest\QueryParam(
     *     name="course",
     *     description="The course id.",
     *     allowBlank=false,
     *     requirements="\d+"
     * )
     *
     * @Rest\View(serializerEnableMaxDepthChecks=true, serializerGroups={"course_full"})
     */
    public function getAction(ParamFetcher $params)
    {
        $course = $this->getRepo(Course::class)
            ->findOneBy([
                'subject' => [
                    'id' => $params->get('subject'),
                ],
                'id' => $params->get('course'),
            ])
        ;

        return ['course' => $course];
    }
    
    /**
     * @Rest\Route(
     *     path="/course/{subject}/{number}"
     * )
     * 
     * @Rest\QueryParam(
     *     name="subject",
     *     description="The subject/department short name.",
     *     allowBlank=false
     * )
     * 
     * @Rest\QueryParam(
     *     name="number",
     *     description="The course number.",
     *     allowBlank=false,
     *     requirements="\d+"
     * )
     * 
     * @Rest\View(serializerEnableMaxDepthChecks=true)
     */
    public function getByNumberAction(ParamFetcher $fetcher)
    {
        $courses = $this->getRepo(Course::class)
            ->findBy([
                'subject' => [
                    'name' => $fetcher->get('subject')
                ],
                'number' => $fetcher->get('number'),
            ])
        ;

        return ['courses' => $courses];
    }
}
