<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\News;
use App\Models\NewsCategory;
use App\Support\Presenters\NewsPresenter;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class NewsController extends Controller
{
    public function index(Request $request): Response
    {
        $filters = [
            'search' => $request->string('search')->toString() ?: null,
            'category' => $request->string('category')->toString() ?: null,
        ];

        $articles = News::query()
            ->with('category')
            ->live()
            ->search($filters['search'])
            ->when($filters['category'], fn ($query, string $slug) => $query->whereHas(
                'category',
                fn ($q) => $q->where('slug', $slug)
            ))
            ->recent()
            ->paginate(9)
            ->withQueryString();

        $presenter = NewsPresenter::make();

        $this->seo()->title(__('ui.news.title'))->description(__('ui.news.lead'));

        return Inertia::render('Public/News/Index', [
            'articles' => [
                'data' => $presenter->collection($articles->items()),
                'meta' => $this->paginationMeta($articles),
            ],
            'filters' => $filters,
            'categories' => NewsCategory::query()
                ->withCount(['news' => fn ($q) => $q->live()])
                ->orderBy('sort_order')
                ->get()
                ->map(fn ($category) => $presenter->category($category))
                ->all(),
        ]);
    }

    public function show(string $locale, News $article): Response
    {
        abort_unless($article->isLive(), 404);

        $article->load(['category', 'author']);
        $article->incrementQuietly('views');

        $presenter = NewsPresenter::make();
        $detail = $presenter->detail($article);

        $this->seo()
            ->title($detail['seo']['title'] ?? null)
            ->description($detail['seo']['description'] ?? null)
            ->image($detail['seo']['image'] ?? null)
            ->type('article')
            ->property('article:published_time', $article->published_at?->toIso8601String())
            ->property('article:modified_time', $article->updated_at?->toIso8601String())
            ->property('article:section', $detail['category']['name'] ?? null)
            ->schema(array_filter([
                '@context' => 'https://schema.org',
                '@type' => 'NewsArticle',
                'headline' => $detail['title'] ?? null,
                'description' => $detail['seo']['description'] ?? null,
                'image' => $detail['seo']['image'] ?? null,
                'datePublished' => $article->published_at?->toIso8601String(),
                'dateModified' => $article->updated_at?->toIso8601String(),
                'mainEntityOfPage' => url()->current(),
                'author' => $article->author?->name
                    ? ['@type' => 'Person', 'name' => $article->author->name]
                    : ['@type' => 'Organization', 'name' => config('app.name')],
                'publisher' => ['@type' => 'Organization', 'name' => config('app.name')],
            ]));

        return Inertia::render('Public/News/Show', [
            'article' => $detail,
            'related' => $presenter->collection(
                News::query()
                    ->with('category')
                    ->live()
                    ->whereKeyNot($article->getKey())
                    ->when($article->news_category_id, fn ($q) => $q->where('news_category_id', $article->news_category_id))
                    ->recent()
                    ->limit(3)
                    ->get()
            ),
        ]);
    }
}
