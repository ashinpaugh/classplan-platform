<?php

namespace App\DataFixtures;

use DateTime;
use App\Entity\UpdateLog;
use App\Helpers\ImportDriverHelper;
use Doctrine\Persistence\ObjectManager;


/**
 * Clean up any loose ends during the import process.
 * 
 * @author Austin Shinpaugh
 */
class OnCompleteFixture extends AbstractDataFixture
{
    /**
     * {@inheritdoc}
     */
    public function load(ObjectManager $manager)
    {
        $helper = $this->container->get(ImportDriverHelper::class);

        /* @var UpdateLog $log */
        $log = $helper->getLogEntry();
        
        $log
            ->setEnd(new DateTime())
            ->setPeakMemory(memory_get_peak_usage())
            ->setStatus(UpdateLog::COMPLETED)
        ;
        
        $manager->flush();
        
        static::getOutput()->writeln("\nImport complete.");
    }
    
    /**
     * {@inheritdoc}
     */
    public function getOrder()
    {
        return 4;
    }
}
