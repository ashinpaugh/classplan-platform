<?php

namespace App\Controller;

use App\Entity\UpdateLog;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController as BaseSymfonyController;

/**
 * Base controller class for all controllers in the bundle.
 * 
 * @author Austin Shinpaugh <ashinpaugh@ou.edu>
 */
abstract class AbstractController extends BaseSymfonyController
{
    /**
     * @param $className
     *
     * @return \Doctrine\Persistence\ObjectRepository
     */
    protected function getRepo($className)
    {
        return $this->getDoctrine()->getRepository($className);
    }
    
    /**
     * Fetch the most recent update log.
     * 
     * @return UpdateLog
     */
    protected function getLastUpdateLog()
    {
        $repo   = $this->getDoctrine()->getRepository(UpdateLog::class);
        $update = $repo->findBy([], ['start' => 'DESC'], 1);
        
        return current($update);
    }
}
