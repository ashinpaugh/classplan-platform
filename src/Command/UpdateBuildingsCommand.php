<?php

namespace App\Command;

use App\Entity\Building;
use Doctrine\ORM\EntityManagerInterface;
use Exception;
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

    /**
     * @var string
     */
    protected $building_directory;

    /**
     * @var string[]
     */
    protected $building_dictionary;

    public function __construct(
        EntityManagerInterface $doctrine,
        KernelInterface $kernel
    ) {
        parent::__construct('classplan:buildings:update');

        $this->doctrine = $doctrine;
        $this->project_dir = $kernel->getProjectDir();
    }

    /**
     * @param string $directory
     * @param array $dictionary
     */
    public function setBuildingDirectory(string $directory, array $dictionary)
    {
        $this->building_directory  = $directory;
        $this->building_dictionary = $dictionary;
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
     * @throws Exception
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        try {
            $this->doUpdate($output);

            return 0;
        } catch (LogicException $e) {
            $output->writeln($e->getMessage());
            return $e->getCode();
        }
    }

    protected function doUpdate(OutputInterface $output)
    {
        $metadata = $this->getBuildingMeta($output);

        $repo = $this->doctrine->getRepository(Building::class);
        $buildings = $repo->findAll();

        foreach ($buildings as $building) {
            if (in_array($building->getShortname(), ['', 'WEB'])) {
                continue;
            }

            if (array_key_exists($building->getShortname(), $this->building_dictionary)) {
                $this->setBuildingMetadata($building, $this->building_dictionary[$building->getShortname()]);
                continue;
            }

            $this->parseDirectoryListing($output, $building, $metadata);
        }

        $this->doctrine->flush();
    }

    protected function getBuildingMeta(OutputInterface $output)
    {
        $info = null;

        try {
            $info = $this->scrapeBuildingInfo();
        } catch (HttpExceptionInterface $exception) {
            $output->write('HttpException: ' . $exception->getMessage());
        } catch (ExceptionInterface $exception) {
            $output->write('Exception: ' . $exception->getMessage());
        }

        return $info;
    }

    /**
     * @return array
     * @throws HttpExceptionInterface
     * @throws ExceptionInterface
     */
    protected function scrapeBuildingInfo()
    {
        $client   = HttpClient::create();
        $response = $client->request('GET', $this->building_directory);
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

    protected function parseDirectoryListing(OutputInterface $output, Building $building, $metadata)
    {
        if (empty($metadata)) {
            return;
        }

        $building_meta = array_filter($metadata, function (array $item) use ($building, $output) {
            return $building->getShortname() === $item['abbr'];
        });

        if (empty($building_meta)) {
            $output->writeln('OU directory missing: ' . $building->getShortname());
            return;
        }

        $meta = current($building_meta);
        $this->setBuildingMetadata($building, $meta['name'], $meta['code']);
    }

    protected function setBuildingMetadata(Building $building, $full_name, $code = null)
    {
        $building->setFullName($full_name);

        if (null !== $code) {
            $building->setCode($code);
        }

        $this->doctrine->persist($building);
    }
}
