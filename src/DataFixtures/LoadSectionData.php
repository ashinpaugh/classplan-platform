<?php

namespace App\DataFixtures;

use App\Helpers\ImportDriverHelper;
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
        $count     = 0;

        $progress->setMessage('Importing section data...');

        while ($entry = $importer->getEntry()) {
            $count++;

            $subject = $this->getSubject();
            $course  = $this->getCourse($subject);
            $section = $this->getSection($course);
            $term    = $section->getBlock()->getTerm();

            // First cycle.
            if (!$prev_term) {
                $prev_term = $term;
            }

            if ($count % 500 === 0) {
                $progress->advance(500);
            }

            if ($prev_term->getId() !== $term->getId()) {
                $this->updateLogProgress($progress->getProgressPercent());

                $manager->clear();

                $prev_term = $term;
            } elseif ($count % 1000 === 0) {
                $this->updateLogProgress($progress->getProgressPercent());
            }

            $importer->nextEntry();
        }

        $this->updateLogProgress(1);
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

    /**
     * Update the current UpdateLog's progress percentage.
     *
     * @param float $progress
     */
    protected function updateLogProgress(float $progress)
    {
        $helper = $this->container->get(ImportDriverHelper::class);
        $log    = $helper->getLogEntry();

        $log->setProgress($progress);

        $this->getDoctrine()->getManager()->flush();
    }
}
