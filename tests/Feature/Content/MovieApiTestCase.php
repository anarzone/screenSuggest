<?php

namespace App\Tests\Feature\Content;

use App\Domain\Content\Dto\ContentDtoInterface;
use App\Domain\Content\Fixtures\DirectorFixture;
use App\Domain\Content\Fixtures\MovieFixture;
use App\Domain\Content\Hydrator\MovieHydrator;
use App\Domain\Content\Service\MovieService;
use App\Repository\MovieRepository;
use Doctrine\Common\DataFixtures\Executor\ORMExecutor;
use Doctrine\Common\DataFixtures\Loader;
use Doctrine\Common\DataFixtures\Purger\ORMPurger;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\DependencyInjection\Container;

class MovieApiTestCase extends WebTestCase
{
    protected EntityManagerInterface $entityManager;
    protected MovieHydrator $movieHydrator;
    protected MovieRepository $movieRepository;
    protected MovieService $movieService;

    protected Container $container;
    protected KernelBrowser $client;

    protected function setUp(): void
    {
//        self::bootKernel();
        $this->client = self::createClient();
        $this->container = self::getContainer();


        $this->entityManager = $this->container->get(EntityManagerInterface::class);
        $this->movieRepository = $this->container->get(MovieRepository::class);
        $this->movieHydrator = $this->container->get(MovieHydrator::class);
        $this->movieService = $this->container->get(MovieService::class);
    }

    protected function loadFixtures(): void
    {
        $loader = new Loader();
        $fixtures = [
            $this->container->get(DirectorFixture::class),
            $this->container->get(MovieFixture::class),
        ];
        foreach ($fixtures as $fixture) {
            $loader->addFixture($fixture);
        }

        $purger = new ORMPurger();
        $executor = new ORMExecutor($this->entityManager, $purger);
        $executor->execute($loader->getFixtures());
    }

    protected function createMovie(?array $movieData = null): ContentDtoInterface
    {
        $movieDto = $this->movieHydrator->hydrate(movieData: $movieData ?? [
            'title' => 'The Matrix',
            'description' => 'A computer hacker learns from mysterious rebels about the true nature of his reality and his role in the war against its controllers.',
            'release_date' => '1999-03-31',
            'duration' => 136,
            'director' => [
                'name' => 'Lana Wachowski',
                'birthdate' => '1965-06-21',
                'nationality' => 'US'
            ]
        ]);

        return $this->movieService->store($movieDto);
    }
}