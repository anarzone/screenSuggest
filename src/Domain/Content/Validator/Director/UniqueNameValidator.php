<?php

namespace App\Domain\Content\Validator\Director;

use App\Repository\DirectorRepository;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

class UniqueNameValidator extends ConstraintValidator
{
    public function __construct(readonly private DirectorRepository $movieRepository)
    {
    }

    public function validate(mixed $value, Constraint $constraint): void
    {
        if ($value == null && $value == '') {
            return;
        }

        $existingMovie = $this->movieRepository->findOneBy(['name' => $value]);

        if ($existingMovie) {
            $this->context->buildViolation($constraint->message)
                ->setParameter('{{ title }}', $value)
                ->addViolation();
        }
    }
}