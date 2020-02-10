<?php

namespace App\Controller;

use App\Entity\Term;
use FOS\RestBundle\Controller\Annotations as Rest;

/**
 * Term controller.
 *
 * @Rest\RouteResource("Term", pluralize=false)
 * 
 * @author Austin Shinpaugh <ashinpaugh@ou.edu>
 */
class TermController extends AbstractController
{
    /**
     * Fetch a collection of terms and term blocks.
     *
     * @Rest\Route("/terms")
     * @Rest\View(serializerEnableMaxDepthChecks=true, serializerGroups={"default"})
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
     * @Rest\Route("/term/{id}", requirements={
     *     "id": "\d+"
     * })
     *
     * @Rest\View(serializerEnableMaxDepthChecks=true, serializerGroups={"default"})
     */
    public function getAction(Term $term)
    {
        return ['term' => $term];
    }
}
