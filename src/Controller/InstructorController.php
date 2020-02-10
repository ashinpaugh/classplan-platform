<?php

namespace App\Controller;

use App\Entity\Instructor;
use FOS\RestBundle\Controller\Annotations\QueryParam;
use FOS\RestBundle\Request\ParamFetcher;
use FOS\RestBundle\Routing\ClassResourceInterface;
use FOS\RestBundle\Controller\Annotations as Rest;

/**
 * The instructor controller.
 *
 * @Rest\RouteResource("Instructor", pluralize=false)
 * 
 * @author Austin Shinpaugh <ashinpaugh@ou.edu>
 */
class InstructorController extends AbstractController
{
    /**
     * Fetches all the known instructors.
     *
     * @Rest\Route("/instructors")
     * @Rest\View(serializerEnableMaxDepthChecks=true)
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
     * @Rest\Route("/instructor")
     * @Rest\View(serializerEnableMaxDepthChecks=true)
     * 
     * @QueryParam(
     *     name="id",
     *     requirements="\d+",
     *     description="The instructor's campus ID.",
     *     strict=true,
     *     allowBlank=false
     * )
     */
    public function getAction(ParamFetcher $fetcher)
    {
        $instructor = $this->getRepo(Instructor::class)
            ->find($fetcher->get('id'))
        ;
        
        if (!$instructor instanceof Instructor) {
            return null;
        }
        
        return ['instructor' => $instructor];
    }
    
    /**
     * Fetches all the known instructors and groups them by subject name.
     *
     * @Rest\Route("/instructor/by-subject")
     * @Rest\View(serializerEnableMaxDepthChecks=true)
     */
    public function getBySubjectAction()
    {
        $instructors = $this->getRepo(Instructor::class)
            ->getInstructorsBySubject()
        ;
        
        return ['instructors' => $instructors];
    }
}
