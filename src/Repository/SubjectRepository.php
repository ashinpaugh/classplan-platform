<?php

namespace App\Repository;

use App\Entity\Subject;
use App\Entity\TermBlock;
use Doctrine\DBAL\Driver\Connection;
use Doctrine\ORM\EntityRepository;

/**
 * Subject repository.
 *
 * @author Austin Shinpaugh
 */
class SubjectRepository extends EntityRepository
{
    public function findOneByIndex($id): ?Subject
    {
        $params = is_numeric($id) && $id > 0
            ? ['id' => $id]
            : ['name' => $id]
        ;

        return $this->findOneBy($params);
    }

    public function getByBlock(TermBlock $block)
    {
        /* @var Connection $conn */
        $conn = $this->getEntityManager()->getConnection();

        $statement = $conn->prepare('
            SELECT sub.id, sub.name
            FROM subject AS sub
            JOIN section AS s
              ON s.subject_id = sub.id
            WHERE s.block_id = :block
            GROUP BY sub.id
        ');

        $statement->execute(['block' => $block->getId()]);
        $results = [];

        foreach ($statement->fetchAll(\PDO::FETCH_ASSOC) as $item) {
            $results[] = [
                'id'   => (int) $item['id'],
                'name' => $item['name'],
            ];
        }

        return $results;
    }
}
