<?php

namespace App\Controller;

use App\Entity\Subject;
use FOS\RestBundle\Controller\Annotations as Rest;
use FOS\RestBundle\Routing\ClassResourceInterface;

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
     * Get the list of available subjects.
     *
     * @Rest\Route("/subjects")
     * @Rest\View(serializerEnableMaxDepthChecks=true, serializerGroups={"subject"})
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
     *     "id": "\d+|\w+"
     * })
     *
     * @Rest\View(serializerEnableMaxDepthChecks=true, serializerGroups={"subject_full"})
     *
     * @param Subject $subject
     */
    public function getAction($id)
    {
        $repo = $this->getRepo(Subject::class);

        return ['subject' => $repo->getOneByIndex($id)];
    }

    /**
     * Get the subject with the provided name.
     * 
     * @Rest\Route("/subject/{name}/name")
     * 
     * @Rest\QueryParam(
     *     name="name",
     *     allowBlank=false,
     *     description="The short-name of the subject/department to look up."
     * )
     */
    public function getByNameAction(string $name)
    {
        $subject = $this->getRepo(Subject::class)
            ->findOneBy([
                'name' => $name,
            ])
        ;

        return ['subject' => $subject];
    }
}
