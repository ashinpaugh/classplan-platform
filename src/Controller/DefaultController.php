<?php

namespace App\Controller;

use App\Entity\UpdateLog;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\KernelInterface;
use Symfony\Component\Routing\Annotation\Route;

/**
 * Homepage.
 * 
 * Actions/routing here are not handled by FOSRest.
 *
 * @author Austin Shinpaugh <ashinpuagh@ou.edu>
 */
class DefaultController extends AbstractController
{
    /**
     * Page index.
     *
     * @return Response
     */
    public function indexAction(KernelInterface $kernel)
    {
        $angular = file_get_contents($kernel->getProjectDir() . '/public/app/index.html');
        $angular = str_replace('src="', 'src="app/', $angular);

        return Response::create($angular)
            ->setPublic()
            ->setSharedMaxAge(86400)
        ;
    }
}
