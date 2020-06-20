<?php

namespace App\Controller;

use App\Entity\Section;
use FOS\RestBundle\Request\ParamFetcherInterface;
use FOS\RestBundle\Routing\ClassResourceInterface;
use Nelmio\ApiDocBundle\Annotation\Model;
use Nelmio\ApiDocBundle\Annotation\Operation;
use Swagger\Annotations as SWG;
use FOS\RestBundle\Controller\Annotations as Rest;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Cache;

/**
 * The endpoint used when interacting with events.
 *
 * @Rest\RouteResource("Section", pluralize=false)
 * 
 * @author Austin Shinpaugh <ashinpaugh@ou.edu>
 */
class SectionController extends AbstractController implements ClassResourceInterface
{
    /**
     * Get a section.
     *
     * @Rest\Route("/section/{id}", requirements={"id": "\d+"})
     * @Rest\View(serializerEnableMaxDepthChecks=true, serializerGroups={"section_full", "subject", "course", "campus", "building", "room", "instructor"})
     *
     * @Operation(
     *   tags={"Section"},
     *   summary="Fetch a specific section.",
     *   @SWG\Response(
     *     response="200",
     *     description="Success.",
     *     @SWG\Schema(
     *       type="object",
     *       @SWG\Property(property="section", ref=@Model(type=Section::class, groups={"section_full", "subject", "course", "campus", "building", "room", "instructor"}))
     *     )
     *   )
     * )
     *
     * @param int $id
     * @return array
     */
    public function getAction(int $id)
    {
        $section = $this->getRepo(Section::class)
            ->find($id)
        ;

        return ['section' => $section];
    }

    /**
     * Fetch a subset of sections based on the provided filter criteria.
     *
     * @Rest\Route("/section/find", methods={"POST"})
     * @Rest\View(serializerEnableMaxDepthChecks=true, serializerGroups={"section_full", "subject", "course", "campus", "building", "room", "instructor"})
     * @Cache(public=true, expires="+10 minutes", maxage=600, smaxage=600)
     *
     * @Operation(
     *   tags={"Section"},
     *   summary="Fetch a subset of sections based on the provided filter criteria.",
     *   @SWG\Parameter(
     *     name="block",
     *     in="body",
     *     description="The block id(s).",
     *     required=true,
     *     @SWG\Schema(type="array", @SWG\Items(type="integer"))
     *   ),
     *   @SWG\Parameter(
     *     name="subject",
     *     in="body",
     *     description="Optional. The subject id(s).",
     *     required=false,
     *     minimum="1",
     *     @SWG\Schema(type="array", @SWG\Items(type="integer"))
     *   ),
     *   @SWG\Parameter(
     *     name="instructor",
     *     in="body",
     *     description="Optional. The instructor id(s).",
     *     required=false,
     *     @SWG\Schema(type="array", @SWG\Items(type="integer"))
     *   ),
     *   @SWG\Parameter(
     *     name="course",
     *     in="body",
     *     description="Optional. The course number id(s).",
     *     required=false,
     *     @SWG\Schema(type="array", @SWG\Items(type="integer"))
     *   ),
     *   @SWG\Response(
     *     response="200",
     *     description="Success.",
     *     @SWG\Schema(
     *       type="object",
     *       @SWG\Property(property="sections", type="array", @SWG\Items(ref=@Model(type=Section::class, groups={"section_full", "subject", "course", "campus", "building", "room", "instructor"})))
     *     )
     *   )
     * )
     *
     * @Rest\RequestParam(
     *   name="block",
     *   map=true,
     *   nullable=false,
     *   requirements="\d+",
     *   description="The block id(s)."
     * )
     * @Rest\RequestParam(
     *   name="subject",
     *   map=true,
     *   nullable=true,
     *   requirements="\d+",
     *   description="Optional. The subject id(s)."
     * )
     * @Rest\RequestParam(
     *   name="instructor",
     *   map=true,
     *   nullable=true,
     *   requirements="\d+",
     *   description="Optional. The instructor id(s)."
     * )
     * @Rest\RequestParam(
     *   name="course",
     *   map=true,
     *   nullable=true,
     *   requirements="\d+",
     *   description="Optional. The course id(s)."
     * )
     * @Rest\RequestParam(
     *   name="building",
     *   map=true,
     *   nullable=true,
     *   requirements="\d+",
     *   description="Optional. The building id(s)."
     * )
     * @Rest\RequestParam(
     *   name="room",
     *   map=true,
     *   nullable=true,
     *   requirements="\d+",
     *   description="Optional. The room id(s)."
     * )
     * @Rest\RequestParam(
     *   name="meetingType",
     *   map=true,
     *   nullable=true,
     *   requirements="\d+",
     *   description="Optional. The meeting types."
     * )
     *
     * @param ParamFetcherInterface $fetcher
     * @return array
     */
    public function findAction(ParamFetcherInterface $fetcher)
    {
        return ['sections' => $this->getRepo(Section::class)->fetchAll($fetcher)];
    }
    
    /**
     * Verify that the timestamp of the last update matches the most recent
     * UpdateLog entry.
     * 
     * Since the Term / Instructor / Course data is inserted into the page
     * on page load, their id's could differ after an update occurs and return
     * mismatched results.
     * 
     * @param string $timestamp
     *
     * @return bool
     */
    private function checkTimestamp($timestamp)
    {
        $update    = $this->getLastUpdateLog();
        $timestamp = strtotime($timestamp);
        
        return !empty($update) && $update->getStart()->getTimestamp() === $timestamp;
    }
}
