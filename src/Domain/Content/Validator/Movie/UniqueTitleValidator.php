<?php

namespace App\Domain\Content\Validator\Movie;

use App\Domain\Content\Dto\Movie\MovieDto;
use App\Entity\Movie;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

class UniqueTitleValidator extends ConstraintValidator
{
    public function __construct(
        readonly private EntityManagerInterface $em
    )
    {
    }

    public function validate(mixed $value, Constraint $constraint): void
    {
        if ($value == null && $value == '') {
            return;
        }

        $object = $this->context->getObject();

        $qb = $this->em->getRepository(Movie::class)
            ->createQueryBuilder('m')
            ->where('m.title = :title')
            ->setParameter('title', $value);

        if ($object instanceof MovieDto && $object->id) {
            $qb->andWhere('m.id != :id')
                ->setParameter('id', $object->id);
        }

        $existingMovie = $qb->getQuery()->getOneOrNullResult();

        if ($existingMovie) {
            $this->context->buildViolation($constraint->message)
                ->setParameter('{{ title }}', $value)
                ->addViolation();
        }
    }
}