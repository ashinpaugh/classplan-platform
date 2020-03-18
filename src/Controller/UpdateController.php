<?php

namespace App\Controller;

use App\Entity\UpdateLog;
use FOS\RestBundle\Routing\ClassResourceInterface;
use FOS\RestBundle\Controller\Annotations as Rest;
use Nelmio\ApiDocBundle\Annotation\Model;
use Nelmio\ApiDocBundle\Annotation\Operation;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Cache;
use Swagger\Annotations as SWG;

/**
 * The update controller.
 *
 * @Rest\RouteResource("Update", pluralize=false)
 *
 * @author Austin Shinpaugh <ashinpaugh@ou.edu>
 */
class UpdateController extends AbstractController implements ClassResourceInterface
{
    /**
     * Get the latest UpdateLog.
     *
     * @Rest\Route("/update")
     * @Rest\View(serializerGroups={"update"})
     * @Cache(public=true, smaxage=3)
     *
     * @Operation(
     *   tags={"Update"},
     *   @SWG\Response(
     *     response="200",
     *     description="Success.",
     *     ref=@Model(type=UpdateLog::class, groups={"update"})
     *   )
     * )
     */
    public function getAction()
    {
        return $this->getLastUpdateLog();
    }

    /**
     * Poll the update log for changes in state.
     *
     * @Rest\Route("/update/check")
     * @Rest\View(serializerGroups={"update"})
     *
     * @Operation(
     *   tags={"Update"},
     *   @SWG\Response(
     *     response="200",
     *     description="Success.",
     *     ref=@Model(type=UpdateLog::class, groups={"update"})
     *   )
     * )
     */
    public function checkAction()
    {
        $this->doPoll();

        return $this->getLastUpdateLog();
    }

    /**
     * Poll the update log.
     *
     * @return bool
     */
    protected function doPoll()
    {
        $tries          = 0;
        $max_tries      = 3;
        $sleep_duration = 5;

        while (($is_updating = $this->isUpdating()) && ++$tries <= $max_tries) {
            sleep($sleep_duration);
        }

        return $is_updating;
    }

    /**
     * Determine if the last log entry indicates that the batch import process is working.
     *
     * @return bool
     */
    protected function isUpdating(): bool
    {
        // Remove the current log from memory.
        $this->getDoctrine()->getManager()->clear();

        return $this->getLastUpdateLog()->getEnd() === null;
    }
}
