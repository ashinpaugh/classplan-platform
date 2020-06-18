<?php

namespace App\Controller;

use App\Entity\UpdateLog;
use FOS\RestBundle\Controller\Annotations as Rest;
use Nelmio\ApiDocBundle\Annotation\Model;
use Nelmio\ApiDocBundle\Annotation\Operation;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Cache;
use Swagger\Annotations as SWG;

/**
 * The update controller.
 *
 * @SWG\Swagger(
 *     @SWG\Info(title="Update API", version="1.0"),
 *     x={"format-suffix": {
 *         "enabled": true,
 *         "pattern": "json|xml"
 *     }},
 * )
 */
class UpdateController extends AbstractController
{
    /**
     * Get the latest update log.
     *
     * @Rest\View(serializerGroups={"update"})
     * @Cache(public=true, smaxage=3)
     *
     * @SWG\Get(
     *   path="/update",
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
     * Poll the update log for changes in state or progress.
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
