<?php

namespace App\Controller;

use App\Entity\Building;
use FOS\RestBundle\Routing\ClassResourceInterface;
use Nelmio\ApiDocBundle\Annotation\Operation;
use Nelmio\ApiDocBundle\Annotation\Model;
use Swagger\Annotations as SWG;
use FOS\RestBundle\Controller\Annotations as Rest;

/**
 * Building controller.
 *
 * @Rest\RouteResource("Building", pluralize=false)
 *
 * @author Austin Shinpaugh <ashinpaugh@ou.edu>
 */
class BuildingController extends AbstractController implements ClassResourceInterface
{

    /**
     * @Rest\Route("/buildings")
     * @Rest\View(serializerEnableMaxDepthChecks=true, serializerGroups={"building"})
     *
     * @SWG\Response(
     *    response="200",
     *    description="Buildings returned successfully.",
     *    @SWG\Schema(
     *        type="array",
     *        @SWG\Items(ref=@Model(type=Building::class))
     *    )
     * )
     */
    public function cgetAction()
    {
        $buildings = $this->getRepo(Building::class)
            ->findBy([], ['short_name' => 'ASC'])
        ;

        return ['buildings' => $buildings];
    }

    /**
     * Fetch a specific building.
     *
     * @Operation(
     *     tags={""},
     *     summary="Fetch a specific building.",
     *     @SWG\Response(
     *         response="200",
     *         description="Returned when successful"
     *     )
     * )
     *
     * @Rest\Route("/building/{id}", requirements={
     *     "id": "\d+"
     * })
     *
     * @Rest\View(serializerEnableMaxDepthChecks=true, serializerGroups={"building_full"})
     */
    public function getAction(int $id)
    {
        $building = $this->getRepo(Building::class)
            ->find($id)
        ;

        return ['building' => $building];
    }

    /**
     * @Rest\Route("/building/{name}", requirements={
     *     "name": "\w+"
     * })
     *
     * @Rest\View(serializerEnableMaxDepthChecks=true, serializerGroups={"building_full"})
     */
    public function getByNameAction(string $name)
    {
        $buildings = $this->getRepo(Building::class)
            ->findBy([
                'short_name' => $name,
            ])
        ;

        return ['buildings' => $buildings];
    }
}
