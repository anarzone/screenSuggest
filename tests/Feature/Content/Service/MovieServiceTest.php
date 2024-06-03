<?php

use App\Domain\Content\Dto\ContentDtoInterface;
use App\Domain\Content\Dto\Director\DirectorDto;
use App\Domain\Content\ValueObject\Content\Duration;
use App\Domain\Content\ValueObject\Content\Title;
use App\Entity\Director;
use App\Entity\Movie;
use App\Tests\Feature\Content\MovieApiTestCase;

uses(MovieApiTestCase::class);

beforeEach(function (){
    $this->loadFixtures();
});

describe('MovieService::all method', function () {
    it('may contain array of 12 movies', function () {
        $movies = $this->movieService->all();

        expect($movies)->toBeArray()->toHaveCount(12);
    });

    it('confirms that each movie has correct properties', function () {
        $movies = $this->movieService->all();

        foreach ($movies as $movie) {
            expect($movie)->toHaveKeys(['id', 'title', 'description', 'release_date', 'duration', 'director']);
        }
    });

    it('confirms that each movie has correct types', function () {
        $movies = $this->movieService->all();

        foreach ($movies as $movie) {

            expect($movie->id)->toBeNumeric()
                ->and($movie->title)->toBeString()
                ->and($movie->description)->toBeString()
                ->and($movie->release_date)->toBeInstanceOf(DateTimeInterface::class)
                ->and($movie->duration)->toBeInt()
                ->and($movie->director)->toBeInstanceOf(DirectorDto::class);
        }
    });
});

describe('MovieService::single method', function () {
    it('may return ContentDtoInterface type with entered data', function () {
        $movie = new Movie();
        $movie->setTitle(Title::fromString('The Matrix'));
        $movie->setDescription('A computer hacker learns from mysterious rebels about the true nature of his reality and his role in the war against its controllers.');
        $movie->setReleaseDate(new DateTime('1999-03-31'));
        $movie->setDuration(Duration::fromInt(136));

        $director = new Director();

        $director->setName('Lana Wachowski');
        $director->setBirthdate(new DateTime('1965-06-21'));
        $movie->setDirector($director);

        $movie = $this->movieService->single($movie);

        expect($movie)
            ->toBeInstanceOf(ContentDtoInterface::class)
            ->and($movie->title)->toBe('The Matrix')
            ->and($movie->duration)->toBe(136)
            ->and($movie->director)->toBeInstanceOf(DirectorDto::class)
            ->and($movie->director->name)->toBe('Lana Wachowski')
        ;

    });
});

describe('MovieService::store method', function (){
    it('may store a new movie', function (){
        $movieData = [
            'title' => 'The Matrix',
            'description' => 'A computer hacker learns from mysterious rebels about the true nature of his reality and his role in the war against its controllers.',
            'release_date' => '1999-03-31',
            'duration' => 136,
            'director' => [
                'name' => 'Lana Wachowski',
                'birthdate' => '1965-06-21'
            ]
        ];

        $movieDto = $this->movieHydrator->hydrate($movieData);

        $movie = $this->movieService->store($movieDto);

        expect($movie)->toHaveKeys(['id', 'title', 'description', 'release_date', 'duration', 'director'])
            ->and($movie->id)->toBeNumeric()
            ->and($movie->title)->toBe('The Matrix')
            ->and($movie->description)->toBe('A computer hacker learns from mysterious rebels about the true nature of his reality and his role in the war against its controllers.')
            ->and($movie->release_date)->toBeInstanceOf(DateTimeInterface::class)
            ->and($movie->duration)->toBe(136)
            ->and($movie->director)->toBeInstanceOf(DirectorDto::class);
    });
});

