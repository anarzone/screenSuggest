<?php

use Symfony\Component\HttpFoundation\JsonResponse;

uses(App\Tests\Feature\Content\MovieApiTestCase::class);

beforeEach(function () {
    $this->loadFixtures();
});

describe('MovieController::index method', function () {
    it('should return all movies with status code of 200', function () {
        $this->client->request('GET', '/api/movies');

        expect($this->client->getResponse())
            ->toBeInstanceOf(JsonResponse::class)
            ->and($this->client->getResponse()->getStatusCode())->toBe(200)
            ->and($this->client->getResponse()->getContent())->toContain('message', 'data');
    });
});

describe('MovieController::show method', function () {
    it('should return a single movie with status code of 200', function () {
        $movieDto = $this->createMovie();

        $this->client->request('GET', '/api/movies/' . $movieDto->id);

        expect($this->client->getResponse())
            ->toBeInstanceOf(JsonResponse::class)
            ->and($this->client->getResponse()->getStatusCode())->toBe(200)
            ->and($this->client->getResponse()->getContent())->toContain('message', 'data');;
    });

    it('should return a 404 status code when movie is not found', function () {
        $this->client->request('GET', '/api/movies/0');

        expect($this->client->getResponse())
            ->toBeInstanceOf(JsonResponse::class)
            ->and($this->client->getResponse()->getStatusCode())->toBe(404)
            ->and($this->client->getResponse()->getContent())->toContain('message', 'data');
    });
});

describe('MovieController::store method', function () {
    it('should create a movie with status code of 201', function () {
        $this->client->request('POST', '/api/movies', [], [], [], json_encode([
            'title' => 'The Matrix',
            'description' => 'A computer hacker learns from mysterious rebels about the true nature of his reality and his role in the war against its controllers.',
            'release_date' => '1999-03-31',
            'duration' => 136,
            'director' => [
                'name' => 'Lana Wachowski',
                'birthdate' => '1965-06-21',
                'nationality' => 'US'
            ],
        ]));

        expect($this->client->getResponse())
            ->toBeInstanceOf(JsonResponse::class)
            ->and($this->client->getResponse()->getStatusCode())->toBe(201)
            ->and($this->client->getResponse()->getContent())->toContain('message', 'data');
    });
});

describe('MovieController::update method', function () {
    it('should update a movie with status code of 201', function () {
        $movieDto = $this->createMovie([
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

        expect($movieDto)
            ->and($movieDto->title)->toBe('The Matrix')
            ->and($movieDto->duration)->toBe(136)
            ->and($movieDto->director->name)->toBe('Lana Wachowski');

        $this->client->request('PATCH', '/api/movies/' . $movieDto->id, [], [], [], json_encode([
            'title' => 'The Matrix Reloaded',
            'description' => 'Neo and the rebel leaders estimate that they have 72 hours until 250,000 probes discover Zion and destroy it and its inhabitants.',
            'release_date' => '2003-05-15',
            'duration' => 138,
            'director' => [
                'name' => 'Lana Wachowski',
                'birthdate' => '1965-06-21',
                'nationality' => 'US'
            ],
        ]));

        $movieData = json_decode($this->client->getResponse()->getContent(), true);

        expect($this->client->getResponse())
            ->toBeInstanceOf(JsonResponse::class)
            ->and($this->client->getResponse()->getStatusCode())->toBe(201)
            ->and($movieData['data']['title'])->toBe('The Matrix Reloaded')
            ->and($movieData['data']['duration'])->toBe(138)
            ->and($movieData['data']['director']['name'])->toBe('Lana Wachowski');
    });

    it('should return a 404 status code when movie is not found', function () {
        $this->client->request('PATCH', '/api/movies/0');

        expect($this->client->getResponse())
            ->toBeInstanceOf(JsonResponse::class)
            ->and($this->client->getResponse()->getStatusCode())->toBe(404)
            ->and($this->client->getResponse()->getContent())->toContain('message', 'data');
    });
});

describe('MovieController::delete method', function () {
    it('should delete a movie with status code of 204', function () {
        $movieDto = $this->createMovie();

        $this->client->request('DELETE', '/api/movies/' . $movieDto->id);

        expect($this->client->getResponse())
            ->toBeInstanceOf(JsonResponse::class)
            ->and($this->client->getResponse()->getStatusCode())->toBe(200)
            ->and($this->client->getResponse()->getContent())->toContain('message', 'data');

        $this->client->request('GET', '/api/movies/0');

        expect($this->client->getResponse())
            ->toBeInstanceOf(JsonResponse::class)
            ->and($this->client->getResponse()->getStatusCode())->toBe(404)
            ->and($this->client->getResponse()->getContent())->toContain('message', 'data');
    });
});