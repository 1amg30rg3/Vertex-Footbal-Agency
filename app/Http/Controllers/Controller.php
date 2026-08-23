<?php

namespace App\Http\Controllers;

use App\Support\Seo;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

abstract class Controller
{
    protected function seo(): Seo
    {
        return app(Seo::class);
    }

    /**
     * @return array<string, mixed>
     */
    protected function paginationMeta(LengthAwarePaginator $paginator): array
    {
        return [
            'current_page' => $paginator->currentPage(),
            'last_page' => $paginator->lastPage(),
            'per_page' => $paginator->perPage(),
            'total' => $paginator->total(),
            'from' => $paginator->firstItem(),
            'to' => $paginator->lastItem(),
            'prev_url' => $paginator->previousPageUrl(),
            'next_url' => $paginator->nextPageUrl(),
            'links' => collect($paginator->linkCollection())->values()->all(),
        ];
    }
}
