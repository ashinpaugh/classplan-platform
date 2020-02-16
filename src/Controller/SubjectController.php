<?php

namespace App\Controller;

use App\Entity\Instructor;
use App\Entity\Section;
use App\Entity\Subject;
use FOS\RestBundle\Controller\Annotations as Rest;
use FOS\RestBundle\Request\ParamFetcher;
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
     *     "id": "\d+"
     * })
     *
     * @Rest\View(serializerEnableMaxDepthChecks=true, serializerGroups={"subject_full"})
     *
     * @param Subject $subject
     */
    public function getAction(Subject $subject)
    {
        return ['subject' => $subject];
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
    public function getByNameAction(ParamFetcher $fetcher)
    {
        $subject = $this->getRepo(Subject::class)
            ->findOneBy([
                'name' => $fetcher->get('name'),
            ])
        ;

        return ['subject' => $subject];
    }

    /**
     * @Rest\Route("/subject/{id}/instructor", requirements={
     *     "id": "\d+"
     * })
     *
     * @Rest\View(serializerEnableMaxDepthChecks=true, serializerGroups={"subject"})
     *
     * @param Instructor $instructor
     * @return array
     */
    public function getByInstructorAction(Instructor $instructor)
    {
        $sections = $this->getRepo(Section::class)
            ->findBy([
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


        return ['subjects' => $subjects];
    }
}
