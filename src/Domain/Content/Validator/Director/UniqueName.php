<?php

namespace App\Domain\Content\Validator\Director;

use Symfony\Component\Validator\Constraint;

#[\Attribute]
class UniqueName extends Constraint
{
    public string $message = 'The title "{{ name }}" is already in use.';
    public string $mode = 'strict';

    public function __construct(?string $mode = null, ?string $message = null, ?array $groups = null, $payload = null)
    {
        parent::__construct([], $groups, $payload);

        $this->mode = $mode ?? $this->mode;
        $this->message = $message ?? $this->message;
    }
}
