<?php

namespace App\Controller;

use App\Entity\Building;
use App\Entity\Course;
use App\Entity\Instructor;
use App\Entity\Room;
use App\Entity\Section;
use App\Entity\Subject;
use App\Entity\TermBlock;
use FOS\RestBundle\Request\ParamFetcherInterface;
use Nelmio\ApiDocBundle\Annotation\Model;
use Nelmio\ApiDocBundle\Annotation\Operation;
use Swagger\Annotations as SWG;
use FOS\RestBundle\Controller\Annotations as Rest;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Cache;

/**
 * The endpoint used when interacting with events.
 * 
 * @author Austin Shinpaugh <ashinpaugh@ou.edu>
 */
class SectionController extends AbstractController
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
        $block       = null;
        $subject     = null;
        $course      = null;
        $instructor  = null;
        $building    = null;
        $room        = null;

        /*if (!$this->checkTimestamp($fetcher->get('u'))) {
            throw new ConflictHttpException();
        }*/

        if ($block_id = $fetcher->get('block')) {
            $block = $this->getRepo(TermBlock::class)
                ->findById($block_id)
            ;
        }

        if ($subject_id = $fetcher->get('subject')) {
            $subject = $this->getRepo(Subject::class)
                ->findById($subject_id)
            ;
        }

        if ($course_id = $fetcher->get('course')) {
            $course = $this->getRepo(Course::class)
                ->findById($course_id)
            ;
        }

        if ($instructor_id = $fetcher->get('instructor')) {
            $instructor = $this->getRepo(Instructor::class)
                ->findById($instructor_id)
            ;
        }

        if ($building_id = $fetcher->get('building')) {
            $building = $this->getRepo(Building::class)
                ->findById($building_id)
            ;
        }

        if ($room_id = $fetcher->get('room')) {
            $room = $this->getRepo(Room::class)
                ->findById($room_id)
            ;
        }

        $filters = array_filter([
            'block'      => $block,
            'subject'    => $subject,
            'course'     => $course,
            'instructor' => $instructor,
            'building'   => $building,
            'room'       => $room,
        ]);

        if (($meeting_type = $fetcher->get('meetingType')) && count($meeting_type) > 0) {
            $filters['meeting_type'] = $meeting_type;
        }

        $sections = $this->getRepo(Section::class)
            ->findBy($filters)
        ;
        
        return ['sections' => $sections];
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
