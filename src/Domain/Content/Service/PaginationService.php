<?php

namespace App\Domain\Content\Service;

use Symfony\Component\HttpFoundation\Request;

class PaginationService
{
    private const DEFAULT_PAGE = 1;
    private const DEFAULT_LIMIT = 10;
    private const MAX_LIMIT = 100;

    public function getPaginationParameters(Request $request): array
    {
        $page = max(1, (int) $request->query->get('page', self::DEFAULT_PAGE));
        $limit = min(self::MAX_LIMIT, max(1, (int) $request->query->get('limit', self::DEFAULT_LIMIT)));
        $offset = ($page - 1) * $limit;

        return [
            'page' => $page,
            'limit' => $limit,
            'offset' => $offset
        ];
    }

    public function createPaginationData(int $page, int $limit, int $totalItems): array
    {
        $totalPages = ceil($totalItems / $limit);

        return [
            'current_page' => $page,
            'per_page' => $limit,
            'total_items' => $totalItems,
            'total_pages' => $totalPages,
            'has_next_page' => $page < $totalPages,
            'has_previous_page' => $page > 1
        ];
    }
}
