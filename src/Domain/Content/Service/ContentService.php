<?php

namespace App\Domain\Content\Service;

use App\Domain\Common\Enum\ContentType;
use App\Domain\Content\Dto\ContentDtoInterface;
use App\Domain\Content\Dto\Movie\TvShowInputDto;
use App\Domain\Content\Hydrator\MovieHydrator;
use App\Domain\Content\ValueObject\Content\Duration;
use App\Domain\Content\ValueObject\Content\Title;
use App\Domain\Content\ValueObject\Content\Type;
use App\Entity\Content;
use App\Entity\Director;
use App\Repository\ContentRepository;
use App\Repository\DirectorRepository;
use Doctrine\ORM\EntityManagerInterface;

final readonly class ContentService
{
    public function __construct(
        private ContentRepository $contentRepository,
        private MovieHydrator     $contentHydrator,
    )
    {
    }

    public function content(ContentType $contentType, bool $full): ContentDtoInterface
    {
        $content = $this->getContent($contentType);

        return $this->contentHydrator->hydrate($content, $full);
    }

    public function getAll(): array
    {
        return $this->contentRepository->findAll();
    }

    public function getById(int $id): Content
    {
        return $this->contentRepository->findOneBy(['id' => $id]);
    }

    private function getContent(ContentType $contentType): mixed
    {
        if ($contentType === ContentType::DEFAULT){
            $content = $this->contentRepository->findAll();
        }else{
            $content = $this->contentRepository->findBy(['type' => $contentType], ['release_date' => 'DESC']);
        }

        return $content;
    }
}