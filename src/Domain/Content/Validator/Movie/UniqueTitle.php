<?php

namespace App\Domain\Content\Validator\Movie;

use Symfony\Component\Validator\Constraint;

#[\Attribute]
class UniqueTitle extends Constraint
{
    public string $message = 'The title "{{ title }}" is already in use.';
    public string $mode = 'strict';

    public function __construct(
        ?string $mode = null,
        ?string $message = null,
        ?array $groups = null,
        $payload = null,
    )
    {
        parent::__construct([], $groups, $payload);

        $this->mode = $mode ?? $this->mode;
        $this->message = $message ?? $this->message;
    }
}
