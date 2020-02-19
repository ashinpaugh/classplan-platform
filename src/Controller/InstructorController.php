<?php

namespace App\Controller;

use App\Entity\Instructor;
use App\Entity\Section;
use FOS\RestBundle\Controller\Annotations\QueryParam;
use FOS\RestBundle\Routing\ClassResourceInterface;
use FOS\RestBundle\Controller\Annotations as Rest;

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
     * @Rest\Route("/instructor/{id}", requirements={
     *     "id": "\d+"
     * })
     *
     * @Rest\View(serializerEnableMaxDepthChecks=true, serializerGroups={"instructor_full"})
     * 
     * @QueryParam(
     *     name="id",
     *     requirements="\d+",
     *     description="The instructor's campus ID.",
     *     strict=true,
     *     allowBlank=false
     * )
     */
    public function getAction(int $id)
    {
        $instructor = $this->getRepo(Instructor::class)
            ->find($id)
        ;
        
        if (!$instructor instanceof Instructor) {
            return null;
        }
        
        return ['instructor' => $instructor];
    }
}
