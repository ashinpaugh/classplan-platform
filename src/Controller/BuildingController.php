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
     * Fetch all of the known buildings.
     *
     * @Rest\Route("/buildings")
     * @Rest\View(serializerEnableMaxDepthChecks=true, serializerGroups={"building"})
     *
     * @Operation(
     *   tags={"Collections", "Building"},
     *   @SWG\Response(
     *     response="200",
     *     description="Success.",
     *     @SWG\Schema(
     *       type="object",
     *       @SWG\Property(property="buildings", type="array", @SWG\Items(ref=@Model(type=Building::class, groups={"building"})))
     *     )
     *   )
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
     * @Rest\Route("/building/{id}", requirements={"idOrName": "\d+\w+"})
     * @Rest\View(serializerEnableMaxDepthChecks=true, serializerGroups={"building_full"})
     *
     * @Operation(
     *   tags={"Building"},
     *   summary="Fetch a specific building by id or short-name.",
     *   @SWG\Parameter(
     *     name="id",
     *     in="path",
     *     description="The building id or short-name.",
     *     required=true,
     *     type="string",
     *     @SWG\Schema(type="integer"),
     *   ),
     *   @SWG\Response(
     *     response="200",
     *     description="Success.",
     *     @SWG\Schema(
     *       type="object",
     *       @SWG\Property(property="building", ref=@Model(type=Building::class, groups={"building_full"}))
     *     )
     *   )
     * )
     */
    public function getAction($idOrName)
    {
        $filter = is_numeric($idOrName) && $idOrName > 0
            ? ['id' => $idOrName]
            : ['short_name' => $idOrName]
        ;

        $building = $this->getRepo(Building::class)
            ->find($filter)
        ;

        return ['building' => $building];
    }
}
