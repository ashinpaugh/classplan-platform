<?php

namespace App\DataFixtures;

use Doctrine\Persistence\ObjectManager;

/**
 * Import the Section entities.
 * 
 * @author Austin Shinpaugh
 */
class LoadSectionData extends AbstractDataFixture
{
    /**
     * {@inheritdoc}
     */
    public function load(ObjectManager $manager)
    {
        $prev_term = null;
        $importer  = $this->getImporter(true);
        $progress  = static::getProgressBar(count($importer->getEntries()));
        
        $progress->setMessage('Importing section data...');

        $count = 0;
        
        while ($entry = $importer->getEntry()) {
            $count++;

            $subject = $this->getSubject();
            $course  = $this->getCourse($subject);
            $section = $this->getSection($course);
            $term    = $section->getBlock()->getTerm();

            if (!$prev_term) {
                // First cycle.
                $prev_term = $term;
            }
            
            if ($prev_term->getId() !== $term->getId()) {
                $manager->flush();
                $manager->clear();
                
                $prev_term = $term;
            } elseif ($count % 100 === 0) {
                $manager->flush();
            }

            $importer->nextEntry();
            $progress->advance();
        }
        
        $manager->flush();
        $progress->finish();
        
        static::getOutput()->writeln("\n");
    }
    
    /**
     * {@inheritdoc}
     */
    public function getOrder()
    {
        return 3;
    }
}
