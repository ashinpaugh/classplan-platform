<?php

namespace App\Repository;

use App\Entity\Subject;
use App\Entity\TermBlock;
use Doctrine\DBAL\Driver\Connection;
use Doctrine\ORM\EntityRepository;

/**
 * Instructor repository.
 * 
 * @author Austin Shinpaugh
 */
class InstructorRepository extends EntityRepository
{
    /**
     * Group the instructors by the subjects they teach.
     * 
     * Teachers who teach courses that belong to different subjects are duplicated
     * under each new subject.
     * 
     * @return array
     */
    public function getInstructorsBySubject(TermBlock $block, ?Subject $subject)
    {
        $where  = '';
        $params = ['block' => $block->getId()];

        if (!empty($subject)) {
            $where .= "AND sub.id = :subject";
            $params['subject'] = $subject->getId();
        }

        /* @var Connection $conn */
        $conn      = $this->getEntityManager()->getConnection();
        $statement = $conn->prepare("
            SELECT sub.name AS subject_name, i.id, i.name, COUNT(s.id) AS num_sections
            FROM section AS s
            JOIN subject AS sub
              ON s.subject_id = sub.id
            JOIN instructor AS i
              ON s.instructor_id = i.id
            WHERE s.block_id = :block {$where}
            GROUP BY sub.id, i.id, i.name
            ORDER BY i.name
        ");
        
        $statement->execute($params);
        $results = [];
        
        foreach ($statement->fetchAll(\PDO::FETCH_ASSOC) as $item) {
            $subject_name = $item['subject_name'];
            
            if (!array_key_exists($subject_name, $results)) {
                $results[$subject_name] = [];
            }
            
            $results[$subject_name][] = [
                'id'   => (int) $item['id'],
                'name' => $item['name'],
            ];
        }
        
        return $results;
    }
}
