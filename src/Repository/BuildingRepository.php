<?php

namespace App\Repository;

use App\Entity\TermBlock;
use Doctrine\DBAL\Driver\Connection;
use Doctrine\DBAL\FetchMode;
use Doctrine\ORM\EntityRepository;

/**
 * Instructor repository.
 *
 * @author Austin Shinpaugh
 */
class BuildingRepository extends EntityRepository
{
    /**
     * Find the buildings used for a given block.
     */
    public function findByBlock(TermBlock $block)
    {
        $params = ['block' => $block->getId()];

        /* @var Connection $conn */
        $conn      = $this->getEntityManager()->getConnection();
        $statement = $conn->prepare("
            SELECT r.building_id, b.full_name, b.short_name
            FROM section AS s
            JOIN room AS r
              ON s.room_id = r.id
            JOIN building AS b
              ON r.building_id = b.id
            WHERE s.block_id = :block
            GROUP BY b.full_name, b.short_name, r.building_id
            ORDER BY LOWER(b.full_name), LOWER(b.short_name)
        ");

        $statement->execute($params);
        $building_ids = $statement->fetchAll(FetchMode::COLUMN);

        if (empty($building_ids)) {
            return [];
        }

        return $this->findById($building_ids);
    }

    public function fetchAll($exclude_empty = true)
    {
        $where = $exclude_empty
            ? "WHERE b.short_name != '' AND r.number != ''"
            : ''
        ;

        /* @var Connection $conn */
        $conn      = $this->getEntityManager()->getConnection();
        $statement = $conn->prepare("
            SELECT r.building_id
            FROM building AS b
            JOIN room AS r ON r.building_id = b.id
            {$where}
            ORDER BY b.campus_id, LOWER(b.full_name), LOWER(b.short_name)
        ");

        $statement->execute();
        $building_ids = $statement->fetchAll(FetchMode::COLUMN);

        if (empty($building_ids)) {
            return [];
        }

        return $this->findById($building_ids);
    }
}
