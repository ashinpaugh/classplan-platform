<?php

namespace App\Controller;

use App\Entity\Room;
use FOS\RestBundle\Routing\ClassResourceInterface;
use Nelmio\ApiDocBundle\Annotation\Operation;
use Nelmio\ApiDocBundle\Annotation\Model;
use Swagger\Annotations as SWG;
use FOS\RestBundle\Controller\Annotations as Rest;

/**
 * Building controller.
 *
 * @Rest\RouteResource("Room", pluralize=false)
 *
 * @author Austin Shinpaugh <ashinpaugh@ou.edu>
 */
class RoomController extends AbstractController implements ClassResourceInterface
{
    /**
     * Fetch a specific room.
     *
     * @Rest\Route("/room/{id}", requirements={"id": "\d+"})
     * @Rest\View(serializerEnableMaxDepthChecks=true, serializerGroups={"building", "room_full", "section"})
     *
     * @Operation(
     *   tags={"Room"},
     *   summary="Fetch a specific room by id.",
     *   @SWG\Parameter(
     *     name="id",
     *     in="path",
     *     description="The room id.",
     *     required=true,
     *     type="string",
     *     @SWG\Schema(type="integer")
     *   ),
     *   @SWG\Response(
     *     response="200",
     *     description="Success.",
     *     @SWG\Schema(
     *       type="object",
     *       @SWG\Property(property="room", ref=@Model(type=Room::class, groups={"building", "room_full", "section"}))
     *     )
     *   )
     * )
     */
    public function getAction(Room $room)
    {
        return ['room' => $room];
    }

    /**
     * Fetch a specific room.
     *
     * @Rest\Route("/room/{id}/sections", requirements={"id": "\d+"})
     * @Rest\View(serializerEnableMaxDepthChecks=true, serializerGroups={"building", "room_full", "room_sections", "section_full"})
     *
     * @Operation(
     *   tags={"Room"},
     *   summary="Fetch a specific room by id.",
     *   @SWG\Parameter(
     *     name="id",
     *     in="path",
     *     description="The room id.",
     *     required=true,
     *     type="string",
     *     @SWG\Schema(type="integer")
     *   ),
     *   @SWG\Response(
     *     response="200",
     *     description="Success.",
     *     @SWG\Schema(
     *       type="object",
     *       @SWG\Property(property="room", ref=@Model(type=Room::class, groups={"building", "room_full", "room_sections", "section_full"}))
     *     )
     *   )
     * )
     */
    public function getSectionsAction(Room $room)
    {
        return ['room' => $room];
    }
}
