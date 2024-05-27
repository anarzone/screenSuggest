<?php

namespace App\Domain\Content\Fixtures;

use App\Entity\Director;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Bundle\FixturesBundle\FixtureGroupInterface;
use Doctrine\Persistence\ObjectManager;

class DirectorFixture extends Fixture implements FixtureGroupInterface
{
    public function load(ObjectManager $manager): void
    {
        $directors = [
            [
                'name' => 'David Fincher',
                'birth_date' => new \DateTime('1962-08-28'),
                'nationality' => 'US',
            ],
            [
                'name' => 'Christopher Nolan',
                'birth_date' => new \DateTime('1970-07-30'),
                'nationality' => 'UK',
            ],
            [
                'name' => 'Quentin Tarantino',
                'birth_date' => new \DateTime('1963-03-27'),
                'nationality' => 'US',
            ],
            [
                'name' => 'Martin Scorsese',
                'birth_date' => new \DateTime('1942-11-17'),
                'nationality' => 'US',
            ],
            [
                'name' => 'Steven Spielberg',
                'birth_date' => new \DateTime('1946-12-18'),
                'nationality' => 'US',
            ],
            [
                'name' => 'Guy Ritchie',
                'birth_date' => new \DateTime('1968-09-10'),
                'nationality' => 'UK',
            ]
        ];

        foreach ($directors as $director) {
            $directorEntity = new Director();
            $directorEntity->setName($director['name']);
            $directorEntity->setBirthDate($director['birth_date']);
            $directorEntity->setNationality($director['nationality']);

            $manager->persist($directorEntity);
        }

        $manager->flush();
    }

    public static function getGroups(): array
    {
        return ['content', 'director', 'movie'];
    }
}