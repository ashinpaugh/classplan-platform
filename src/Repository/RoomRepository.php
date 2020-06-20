<?php

namespace App\Repository;

use App\Entity\Subject;
use App\Entity\TermBlock;
use Doctrine\DBAL\Driver\Connection;
use Doctrine\DBAL\FetchMode;
use Doctrine\ORM\EntityRepository;

/**
 * Room repository.
 *
 * @author Austin Shinpaugh
 */
class RoomRepository extends EntityRepository
{
    /**
     *
     */
    public function findByBlock(TermBlock $block)
    {
        $params = ['block' => $block->getId()];

        /* @var Connection $conn */
        $conn      = $this->getEntityManager()->getConnection();
        $statement = $conn->prepare("
            SELECT r.id, COUNT(s.id) as numSections
            FROM section AS s
            JOIN room AS r
              ON s.room_id = r.id
            JOIN building AS b
              ON r.building_id = b.id
            WHERE s.block_id = :block
            GROUP BY r.id
            ORDER BY numSections
        ");

        $statement->execute($params);
        $room_ids = $statement->fetchAll(FetchMode::COLUMN);

        if (empty($room_ids)) {
            return [];
        }

        return $this->findById($room_ids);
    }
}
