<?php

namespace App\Controller;

use App\Entity\Building;
use App\Entity\Room;
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
     * @Rest\View(serializerEnableMaxDepthChecks=true, serializerGroups={"building_full", "room"})
     *
     * @Operation(
     *   tags={"Collections", "Building"},
     *   @SWG\Response(
     *     response="200",
     *     description="Success.",
     *     @SWG\Schema(
     *       type="object",
     *       @SWG\Property(property="buildings", type="array", @SWG\Items(ref=@Model(type=Building::class, groups={"building_full", "room"})))
     *     )
     *   )
     * )
     */
    public function cgetAction()
    {
        $buildings = $this->getRepo(Building::class)
            ->fetchAll()
        ;

        return ['buildings' => $buildings];
    }

    /**
     * Fetch a specific building.
     *
     * @Rest\Route("/building/{idOrName}", requirements={"idOrName": "\d+|\w+"})
     * @Rest\View(serializerEnableMaxDepthChecks=true, serializerGroups={"building_full", "room"})
     *
     * @Operation(
     *   tags={"Building"},
     *   summary="Fetch a specific building by id or short-name.",
     *   @SWG\Parameter(
     *     name="idOrName",
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
     *       @SWG\Property(property="building", ref=@Model(type=Building::class, groups={"building_full", "room"}))
     *     )
     *   )
     * )
     */
    public function getAction($idOrName)
    {
        if (is_numeric($idOrName) && $idOrName > 0) {
            $params = ['id' => $idOrName];
        } else {
            // Replace any url encoded spaces with real spaces.
            $idOrName = str_replace('%20', ' ', $idOrName);
            $params   = ['short_name' => $idOrName];
        }

        $building = $this->getRepo(Building::class)
            ->findOneBy($params)
        ;

        return ['building' => $building];
    }

    /**
     * Fetch all of the known buildings.
     *
     * @Rest\Route("/building/{id}/rooms")
     * @Rest\View(serializerEnableMaxDepthChecks=true, serializerGroups={"building", "room"})
     *
     * @Operation(
     *   tags={"Collections", "Building", "Room"},
     *   @SWG\Parameter(
     *     name="id",
     *     in="path",
     *     description="The building id.",
     *     required=true,
     *     type="string",
     *     @SWG\Schema(type="integer"),
     *   ),
     *   @SWG\Response(
     *     response="200",
     *     description="Success.",
     *     @SWG\Schema(
     *       type="object",
     *       @SWG\Property(property="building", ref=@Model(type=Building::class, groups={"building"})),
     *       @SWG\Property(property="room", type="array", @SWG\Items(ref=@Model(type=Room::class, groups={"room"})))
     *     )
     *   )
     * )
     */
    public function getRoomAction(Building $building)
    {
        $rooms = $this->getRepo(Room::class)
            ->findBy(
                ['building' => $building],
                ['number' => 'ASC']
            )
        ;

        return [
            'building' => $building,
            'rooms'    => $rooms,
        ];
    }
}
