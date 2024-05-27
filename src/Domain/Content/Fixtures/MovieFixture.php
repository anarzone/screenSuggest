<?php

namespace App\Domain\Content\Fixtures;

use App\Domain\Content\Hydrator\MovieHydrator;
use App\Domain\Content\Service\MovieService;
use App\Repository\DirectorRepository;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Bundle\FixturesBundle\FixtureGroupInterface;
use Doctrine\Persistence\ObjectManager;

class MovieFixture extends Fixture implements FixtureGroupInterface
{

    public function __construct(
        readonly private DirectorRepository $directorRepository,
        readonly private MovieHydrator $movieHydrator,
        readonly private MovieService $movieService,
    )
    {
    }

    public function load(ObjectManager $manager): void
    {
        $davidFincher = $this->directorRepository->findOneBy(['name' => 'David Fincher']);
        $christopherNolan = $this->directorRepository->findOneBy(['name' => 'Christopher Nolan']);
        $quentinTarantino = $this->directorRepository->findOneBy(['name' => 'Quentin Tarantino']);
        $martinScorsese = $this->directorRepository->findOneBy(['name' => 'Martin Scorsese']);
        $stevenSpielberg = $this->directorRepository->findOneBy(['name' => 'Steven Spielberg']);
        $guyRitchie = $this->directorRepository->findOneBy(['name' => 'Guy Ritchie']);

        $movies = [
            [
                'title' => 'Fight Club',
                'description' => 'An insomniac office worker and a devil-may-care soap maker form an underground fight club that evolves into much more.',
                'release_date' => new \DateTime('1999-10-15'),
                'duration' => 139,
                'director' => $davidFincher,
            ],
            [
                'title' => 'Se7en',
                'description' => 'Two detectives, a rookie and a veteran, hunt a serial killer who uses the seven deadly sins as his motives.',
                'release_date' => new \DateTime('1995-09-22'),
                'duration' => 127,
                'director' => $davidFincher,
            ],
            [
                'title' => 'The Dark Knight',
                'description' => 'When the menace known as the Joker wreaks havoc and chaos on the people of Gotham, Batman must accept one of the greatest psychological and physical tests of his ability to fight injustice.',
                'release_date' => new \DateTime('2008-07-18'),
                'duration' => 152,
                'director' => $christopherNolan,
            ],
            [
                'title' => 'Inception',
                'description' => 'A thief who steals corporate secrets through the use of dream-sharing technology is given the inverse task of planting an idea into the mind of a C.E.O.',
                'release_date' => new \DateTime('2010-07-16'),
                'duration' => 148,
                'director' => $christopherNolan,
            ],
            [
                'title' => 'Pulp Fiction',
                'description' => 'The lives of two mob hitmen, a boxer, a gangster and his wife, and a pair of diner bandits intertwine in four tales of violence and redemption.',
                'release_date' => new \DateTime('1994-10-14'),
                'duration' => 154,
                'director' => $quentinTarantino,
            ],
            [
                'title' => 'Kill Bill: Vol. 1',
                'description' => 'After awakening from a four-year coma, a former assassin wreaks vengeance on the team of assassins who betrayed her.',
                'release_date' => new \DateTime('2003-10-10'),
                'duration' => 111,
                'director' => $quentinTarantino,
            ],
            [
                'title' => 'The Departed',
                'description' => 'An undercover cop and a mole in the police attempt to identify each other while infiltrating an Irish gang in South Boston.',
                'release_date' => new \DateTime('2006-10-06'),
                'duration' => 151,
                'director' => $martinScorsese,
            ],
            [
                'title' => 'Shutter Island',
                'description' => 'In 1954, a U.S. Marshal investigates the disappearance',
                'release_date' => new \DateTime('2010-02-19'),
                'duration' => 138,
                'director' => $martinScorsese,
            ],
            [
                'title' => 'Schindler\'s List',
                'description' => 'In German-occupied Poland during World War II, industrialist Oskar Schindler gradually becomes concerned for his Jewish workforce after witnessing their',
                'release_date' => new \DateTime('1994-02-04'),
                'duration' => 195,
                'director' => $stevenSpielberg,
            ],
            [
                'title' => 'Saving Private Ryan',
                'description' => 'Following the Normandy Landings, a group of U.S. soldiers go behind enemy lines to retrieve a paratrooper whose brothers have been killed in action.',
                'release_date' => new \DateTime('1998-07-24'),
                'duration' => 169,
                'director' => $stevenSpielberg,
            ],
            [
                'title' => 'Snatch',
                'description' => 'Unscrupulous boxing promoters, violent bookmakers, a Russian gangster, incompetent amateur robbers and supposedly Jewish jewelers fight to track down a priceless stolen diamond.',
                'release_date' => new \DateTime('2000-01-19'),
                'duration' => 104,
                'director' => $guyRitchie,
            ],
            [
                'title' => 'Lock, Stock and Two Smoking Barrels',
                'description' => 'A botched card game in London triggers four friends, thugs, weed-growers, hard gangsters, loan sharks and debt collectors to collide with each other in a series of unexpected events, all for the sake of weed, cash and two antique shotguns.',
                'release_date' => new \DateTime('1999-08-28'),
                'duration' => 107,
                'director' => $guyRitchie,
            ],
        ];
        
        foreach ($movies as $movie) {
            $movieDto = $this->movieHydrator->hydrate($movie);
            $this->movieService->store($movieDto);
        }
    }

    public function getDependencies(): array
    {
        return [DirectorFixture::class];
    }

    public static function getGroups(): array
    {
        return ['content', 'movie'];
    }
}