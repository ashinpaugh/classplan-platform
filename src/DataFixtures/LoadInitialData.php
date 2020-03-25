<?php

namespace App\DataFixtures;

use App\Helpers\ImportDriverHelper;
use Doctrine\Persistence\ObjectManager;

/**
 * Import the Term / Location / Instructor entities.
 * 
 * @author Austin Shinpaugh
 */
class LoadInitialData extends AbstractDataFixture
{
    /**
     * Load data fixtures with the passed EntityManager
     *
     * @param ObjectManager $manager
     */
    public function load(ObjectManager $manager)
    {
        $helper   = $this->container->get(ImportDriverHelper::class);
        $importer = $this->getImporter(true);
        $progress = static::getProgressBar(count($importer->getEntries()));

        $helper->toggleFKChecks(true);

        $progress->start();
        $progress->setMessage('Importing initial data...');

        $count = 0;
        
        while ($entry = $importer->getEntry()) {
            $count++;
            $this->getTerm();
            $this->getRoom();
            $this->getInstructor();

            if ($count % 500 === 0) {
                $manager->flush();
                $progress->advance(500);
            }

            $importer->nextEntry();
        }
        
        $manager->flush();
        $progress->finish();
        
        // Clear the line.
        static::getOutput()->writeln('');
    }
    
    /**
     * The lower the number, the sooner that this fixture is loaded.
     *
     * @return int
     */
    public function getOrder()
    {
        return 2;
    }
}
