<?php

namespace App\Command;

use App\Entity\Building;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Exception\LogicException;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\DomCrawler\Crawler;
use Symfony\Component\HttpClient\HttpClient;
use Symfony\Component\HttpKernel\KernelInterface;
use Symfony\Contracts\HttpClient\Exception\ExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\HttpExceptionInterface;

/**
 * Command that should be run after setting the environmental vars
 * in parameters.yml.
 *
 * @author Austin Shinpaugh
 */
class UpdateBuildingsCommand extends AbstractCommand
{
    /**
     * @var EntityManagerInterface
     */
    protected $doctrine;

    public function __construct(
        EntityManagerInterface $doctrine,
        KernelInterface $kernel
    ) {
        parent::__construct('classplan:buildings:update');

        $this->doctrine = $doctrine;
        $this->projectDir = $kernel->getProjectDir();
    }

    /**
     * {@inheritDoc}
     */
    protected function configure()
    {
        parent::configure();

        $this
            ->setDescription('Update the building entities with their full names.')
        ;
    }

    /**
     * {@inheritDoc}
     *
     * @throws \Exception
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        try {
            $this->doUpdate($input, $output);

            return 0;
        } catch (LogicException $e) {
            $output->writeln($e->getMessage());
            return $e->getCode();
        }
    }

    protected function doUpdate(InputInterface $input, OutputInterface $output)
    {
        $metadata = $this->getBuildingMeta($output);

        $repo = $this->doctrine->getRepository(Building::class);
        $buildings = $repo->findAll();

        foreach ($buildings as $building) {
            $building_meta = array_filter($metadata, function (array $item) use ($building, $output) {
                return $building->getShortname() === $item['abbr'];
            });

            if (empty($building_meta)) {
                $output->writeln('OU directory missing: ' . $building->getShortname());
                continue;
            }

            $meta = current($building_meta);
            $building
                ->setCode($meta['code'])
                ->setFullName($meta['name'])
            ;

            $this->doctrine->persist($building);
        }

        $this->doctrine->flush();
    }

    protected function getBuildingMeta(OutputInterface $output)
    {
        try {
            $scrape = $this->scrapeBuildingInfo($output);
        } catch (HttpExceptionInterface $exception) {
            $output->write('HttpException: ' . $exception->getMessage());
        } catch (ExceptionInterface $exception) {
            $output->write('Exception: ' . $exception->getMessage());
        }

        if (!empty($scrape) && count($scrape) > 0) {
            return $scrape;
        }

        throw new LogicException('Implement scrape backup logic.');
    }

    /**
     * @return array
     * @throws HttpExceptionInterface
     * @throws ExceptionInterface
     */
    protected function scrapeBuildingInfo(OutputInterface $output)
    {
        $client   = HttpClient::create();
        $response = $client->request('GET', 'https://directory.ouhsc.edu/Contacts/BuildingLocations.aspx');
        $content  = $response->getContent(true);

        $crawler = new Crawler($content);
        $tables  = $crawler->filter('table');

        // Guard against a table getting added to the page in the future.
        if ($tables->count() > 1) {
            $tables = $tables->filter('#theList');
        }

        return $tables->filter('tbody tr')->each(function (Crawler $row) {
            $columns = $row->filter('td');

            return [
                'abbr' => $columns->eq(2)->text(),
                'code' => $columns->eq(1)->text(),
                'name' => $columns->eq(3)->text(),
            ];
        });
    }
}
