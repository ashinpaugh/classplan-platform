<?php

namespace App\Controller;

use App\Entity\Subject;
use FOS\RestBundle\Controller\Annotations as Rest;
use FOS\RestBundle\Request\ParamFetcher;

/**
 * Loads a subject.
 *
 * @Rest\RouteResource("Subject", pluralize=false)
 * 
 * @author Austin Shinpaugh <ashinpaugh@ou.edu>
 */
class SubjectController extends AbstractController
{
    /**
     * Get the list of available subjects.
     *
     * @Rest\Route("/subjects")
     * @Rest\View(serializerEnableMaxDepthChecks=true, serializerGroups={"default"})
     */
    public function cgetAction()
    {
        $subjects = $this->getRepo(Subject::class)
            ->findAll()
        ;

        return ['subjects' => $subjects];
    }

    /**
     * Get the courses related to a subject.
     *
     * @Rest\Route("/subject/{id}", requirements={
     *     "id": "\d+"
     * }))
     *
     * @Rest\View(serializerGroups={"Default", "courses", "sections"}, serializerEnableMaxDepthChecks=true)
     *
     * @param Subject $subject
     */
    public function getAction(Subject $subject)
    {
        return ['subject' => $subject];
    }
    
    /**
     * Get the courses related to a subject.
     * 
     * @Rest\Route("/subject/{name}/name")
     * 
     * @Rest\QueryParam(
     *     name="name",
     *     allowBlank=false,
     *     description="The short-name of the subject/department to look up."
     * )
     */
    public function getByNameAction(ParamFetcher $fetcher)
    {
        $subject = $this->getRepo(Subject::class)
            ->findOneBy([
                'name' => $fetcher->get('name'),
            ])
        ;

        return ['subject' => $subject];
    }
}
