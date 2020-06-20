<?php

namespace App\Repository;

use App\Entity\Section;
use Doctrine\ORM\EntityRepository;
use FOS\RestBundle\Request\ParamFetcherInterface;

/**
 * Section repository.
 *
 * @author Austin Shinpaugh
 */
class SectionRepository extends EntityRepository
{
    /**
     * Fetches all sections matching the provided inputs.
     *
     * @return Section[]
     */
    public function fetchAll(ParamFetcherInterface $fetcher)
    {
        $query = $this->createQueryBuilder('s');

        if ($block_ids = $fetcher->get('block')) {
            $query = $query->where(
                $query->expr()->in('s.block', $block_ids)
            );
        }

        if ($subject_ids = $fetcher->get('subject')) {
            $query = $query->andWhere(
                $query->expr()->in('s.subject', $subject_ids)
            );
        }

        if ($course_ids = $fetcher->get('course')) {
            $query = $query->andWhere(
                $query->expr()->in('s.course', $course_ids)
            );
        }

        if ($instructor_ids = $fetcher->get('instructor')) {
            $query = $query->andWhere(
                $query->expr()->in('s.instructor', $instructor_ids)
            );
        }

        if ($building_ids = $fetcher->get('building')) {
            $query = $query->andWhere(
                $query->expr()->in('s.building', $building_ids)
            );
        }

        if ($room_ids = $fetcher->get('room')) {
            $query = $query->andWhere(
                $query->expr()->in('s.room', $room_ids)
            );
        }

        if (($meeting_types = $fetcher->get('meetingType')) && count($meeting_types) > 0) {
            $query = $query->andWhere(
                $query->expr()->in('s.meeting_type', $meeting_types)
            );
        }

        return $query->getQuery()->getResult();
    }
}
