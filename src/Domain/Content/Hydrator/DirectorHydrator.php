<?php

namespace App\Domain\Content\Hydrator;

use App\Domain\Content\Dto\Director\DirectorDto;
use App\Entity\Director;

class DirectorHydrator
{
    public function hydrate(string|object|array $directorData): DirectorDto
    {
        return is_string($directorData) ? $this->populateFromString($directorData) : $this->populateFromObjectOrArray($directorData);
    }

    public function hydrateCollection(array $directors): array
    {
        return array_map(fn($director) => $this->hydrate($director), $directors);
    }

    /*
     * @todo: modify this method for better handling of string data.
     * Here we are assuming that the string is the name of the director.
     * This is not a good assumption.
     */
    private function populateFromString(string $directorData): DirectorDto
    {
        $directorOutputDto = new DirectorDto();
        $directorOutputDto->name = $directorData;

        return $directorOutputDto;
    }

    private function populateFromObjectOrArray(object|array $directorData): DirectorDto
    {
        $directorOutputDto = new DirectorDto();

        $directorOutputDto->id = $this->requestValue($directorData, 'id');
        $directorOutputDto->name = $this->requestValue($directorData, 'name');

        $birthdate = $this->requestValue($directorData, 'birthdate');
        $directorOutputDto->birthdate = is_string($birthdate) ? new \DateTime($birthdate) : $birthdate;
        $directorOutputDto->nationality = $this->requestValue($directorData, 'nationality');

        return $directorOutputDto;
    }

    private function requestValue($directorData, $key)
    {
        if (is_array($directorData)){
            return $directorData[$key] ?? null;
        }
        if ($directorData instanceof Director){
            return $directorData->{"get".ucfirst($key)}();
        }

        return property_exists($directorData, $key) ? $directorData->$key : null;
    }
}