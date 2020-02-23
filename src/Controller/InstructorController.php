<?php

namespace App\Controller;

use App\Entity\Instructor;
use FOS\RestBundle\Routing\ClassResourceInterface;
use FOS\RestBundle\Controller\Annotations as Rest;
use Nelmio\ApiDocBundle\Annotation\Model;
use Nelmio\ApiDocBundle\Annotation\Operation;
use Swagger\Annotations as SWG;

/**
 * The instructor controller.
 *
 * @Rest\RouteResource("Instructor", pluralize=false)
 * 
 * @author Austin Shinpaugh <ashinpaugh@ou.edu>
 */
class InstructorController extends AbstractController implements ClassResourceInterface
{
    /**
     * Fetches all the known instructors.
     *
     * @Rest\Route("/instructors")
     * @Rest\View(serializerEnableMaxDepthChecks=true, serializerGroups={"instructor"})
     *
     * @Operation(
     *   tags={"Collections", "Instructor"},
     *   @SWG\Response(
     *     response="200",
     *     description="Success.",
     *     @SWG\Schema(
     *       type="object",
     *       @SWG\Property(property="instructors", type="array", @SWG\Items(ref=@Model(type=Instructor::class, groups={"instructor"})))
     *     )
     *   )
     * )
     */
    public function cgetAction()
    {
        $instructors = $this->getRepo(Instructor::class)
            ->findAll()
        ;
        
        return ['instructors' => $instructors];
    }
    
    /**
     * Get all the sections taught by an instructor.
     *
     * @Rest\Route("/instructor/{id}", requirements={"id": "\d+"})
     * @Rest\View(serializerEnableMaxDepthChecks=true, serializerGroups={"instructor_full"})
     * 
     * @Operation(
     *   tags={"Instructor"},
     *   @SWG\Parameter(
     *     name="id",
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
     *       @SWG\Property(property="instructor", ref=@Model(type=Instructor::class, groups={"instructor_full"}))
     *     )
     *   )
     * )
     *
     * @param Instructor $instructor
     * @return array
     */
    public function getAction(Instructor $instructor)
    {
        return ['instructor' => $instructor];
    }
}
