<?php

namespace App\Doctrine\Custom\Type;

use Doctrine\DBAL\Platforms\AbstractPlatform;

abstract class StringType extends \Doctrine\DBAL\Types\StringType
{
    const TYPE_NAME = '';

    public function getSQLDeclaration(array $column, AbstractPlatform $platform): string
    {
        return $platform->getClobTypeDeclarationSQL(['length' => 255]);
    }

    public function convertToPHPValue(mixed $value, AbstractPlatform $platform): mixed
    {
        $className = $this->getClassName();
        return new $className($value);
    }

    public function convertToDatabaseValue(mixed $value, AbstractPlatform $platform): mixed
    {
        return $value;
    }

    public function getName(): string
    {
        return self::TYPE_NAME;
    }

    abstract public function getClassName(): string;
}