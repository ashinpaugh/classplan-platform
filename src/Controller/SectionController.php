<?php

namespace App\Controller;

use App\Entity\Course;
use App\Entity\Instructor;
use App\Entity\Section;
use App\Entity\Subject;
use App\Entity\TermBlock;
use FOS\RestBundle\Request\ParamFetcher;
use FOS\RestBundle\Routing\ClassResourceInterface;
use Nelmio\ApiDocBundle\Annotation\Operation;
use Swagger\Annotations as SWG;
use FOS\RestBundle\Controller\Annotations as Rest;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Cache;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\ConflictHttpException;

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
     * @Rest\Route("/section/{id}", requirements={
     *     "id": "\d+",
     * })
     *
     * @Rest\View(serializerEnableMaxDepthChecks=true, serializerGroups={"section_full", "subject", "course", "campus", "building", "room", "instructor"})
     *
     * @param int $id
     *
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
     * @Operation(
     *     tags={""},
     *     summary="Fetch a subset of sections based on the provided filter criteria.",
     *     @SWG\Parameter(
     *         name="block",
     *         in="query",
     *         description="The block ID(s).",
     *         required=false,
     *         type="string"
     *     ),
     *     @SWG\Parameter(
     *         name="subject",
     *         in="query",
     *         description="Optional. The subject ID(s).",
     *         required=false,
     *         type="string"
     *     ),
     *     @SWG\Parameter(
     *         name="instructor",
     *         in="query",
     *         description="Optional. The instructor ID(s) to filter on.",
     *         required=false,
     *         type="string"
     *     ),
     *     @SWG\Parameter(
     *         name="course",
     *         in="query",
     *         description="Optional. The course number ID(s) to filter on.",
     *         required=false,
     *         type="string"
     *     ),
     *     @SWG\Parameter(
     *         name="update",
     *         in="query",
     *         description="The date that the other IDs were created on.",
     *         required=false,
     *         type="string"
     *     ),
     *     @SWG\Response(
     *         response="200",
     *         description="Returned when successful"
     *     )
     * )
     *
     * @Rest\QueryParam(name="block", nullable=false, description="The block ID(s).")
     * @Rest\QueryParam(name="subject", nullable=true,  description="Optional. The subject ID(s).")
     * @Rest\QueryParam(name="instructor", nullable=true,  description="Optional. The instructor ID(s) to filter on.")
     * @Rest\QueryParam(name="course", nullable=true,  description="Optional. The course number ID(s) to filter on.")
     * @Rest\QueryParam(name="update", nullable=true, description="The date that the other IDs were created on.")
     *
     * 
     * @Rest\Route("/section/find", methods={"POST"})
     * 
     * @Rest\View(serializerEnableMaxDepthChecks=true, serializerGroups={"section_full", "subject", "course", "campus", "building", "room", "instructor"})
     * @Cache(public=true, expires="+10 minutes", maxage=600, smaxage=600)
     * 
     * @param Request      $request
     * @param ParamFetcher $fetcher
     * 
     * @return array
     */
    public function findAction(Request $request, ParamFetcher $fetcher)
    {
        $block       = null;
        $subject     = null;
        $course      = null;
        $instructor  = null;
        
        /*if (!$this->checkTimestamp($fetcher->get('u'))) {
            throw new ConflictHttpException();
        }*/

        // print_r($request->request->all());die;
        
        if ($block_id = $request->request->get('block')) {
            $block = $this->getRepo(TermBlock::class)
                ->findById($block_id)
            ;
        }
        
        if ($instructor_id = $request->request->get('instructor')) {
            $instructor = $this->getRepo(Instructor::class)
                ->findById($instructor_id)
            ;
        }
        
        if ($subject_id = $request->request->get('subject')) {
            $subject = $this->getRepo(Subject::class)
                ->findById($subject_id)
            ;
        }
        
        if ($course_id = $request->request->get('course')) {
            $course = $this->getRepo(Course::class)
                ->findById($course_id)
            ;
        }
        
        // $this->get('session')->set('last_query', $request->getQueryString());

        $sections = $this->getRepo(Section::class)
            ->findBy(array_filter([
                    'block'      => $block,
                    'subject'    => $subject,
                    'course'     => $course,
                    'instructor' => $instructor,
                ])
            )
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
