<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Admin\Concerns\EditsTranslations;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\NewsRequest;
use App\Models\ActivityLog;
use App\Models\News;
use App\Models\NewsCategory;
use App\Support\Locales;
use App\Support\MediaUploader;
use App\Support\RichText;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Inertia\Response;

class NewsController extends Controller
{
    use EditsTranslations;

    public function index(Request $request): Response
    {
        $articles = News::query()
            ->with('category')
            ->search($request->string('search')->toString() ?: null)
            ->when($request->string('status')->toString(), fn ($q, $status) => $q->where('status', $status))
            ->when(
                $request->string('category')->toString(),
                fn ($q, $slug) => $q->whereHas('category', fn ($c) => $c->where('slug', $slug))
            )
            ->when($request->boolean('featured'), fn ($q) => $q->where('is_featured', true))
            ->recent()
            ->paginate(15)
            ->withQueryString();

        return Inertia::render('Admin/News/Index', [
            'articles' => [
                'data' => collect($articles->items())->map(fn (News $article) => [
                    'id' => $article->id,
                    'slug' => $article->slug,
                    'title' => Locales::pick($article->getTranslations('title'), Locales::default()),
                    'cover' => News::mediaUrl($article->cover_path),
                    'status' => $article->status,
                    'is_live' => $article->isLive(),
                    'is_featured' => $article->is_featured,
                    'featured_order' => $article->featured_order,
                    'published_at' => $article->published_at?->format('Y-m-d H:i'),
                    'category' => $article->category
                        ? Locales::pick($article->category->getTranslations('name'), Locales::default())
                        : null,
                    'views' => $article->views,
                    'updated_at' => $article->updated_at?->diffForHumans(),
                ])->all(),
                'meta' => $this->paginationMeta($articles),
            ],
            'filters' => $request->only('search', 'status', 'category', 'featured'),
            'statuses' => News::STATUSES,
            'categories' => $this->categoryOptions(),
        ]);
    }

    public function create(): Response
    {
        return Inertia::render('Admin/News/Form', [
            'article' => $this->blank(),
            'options' => [
                'statuses' => News::STATUSES,
                'categories' => $this->categoryOptions(),
            ],
        ]);
    }

    public function store(NewsRequest $request): RedirectResponse
    {
        $article = DB::transaction(fn () => $this->persist(new News, $request));

        ActivityLog::record('created', $article, 'Created article "'.$this->title($article).'"');

        return redirect()->route('admin.news.edit', $article)->with('success', 'Article created.');
    }

    public function edit(News $news): Response
    {
        return Inertia::render('Admin/News/Form', [
            'article' => $this->payload($news),
            'options' => [
                'statuses' => News::STATUSES,
                'categories' => $this->categoryOptions(),
            ],
        ]);
    }

    public function update(NewsRequest $request, News $news): RedirectResponse
    {
        DB::transaction(fn () => $this->persist($news, $request));

        ActivityLog::record('updated', $news, 'Updated article "'.$this->title($news).'"');

        return back()->with('success', 'Article saved.');
    }

    public function destroy(News $news): RedirectResponse
    {
        $title = $this->title($news);
        $news->delete();

        ActivityLog::record('deleted', $news, "Deleted article \"{$title}\"");

        return redirect()->route('admin.news.index')->with('success', 'Article deleted.');
    }

    public function toggleFeatured(News $news): RedirectResponse
    {
        $news->forceFill(['is_featured' => ! $news->is_featured])->save();

        return back()->with(
            'success',
            $news->is_featured ? 'Article added to the homepage.' : 'Article removed from the homepage.'
        );
    }

    protected function persist(News $article, NewsRequest $request): News
    {
        $data = $request->validated();

        $publishedAt = $data['published_at'] ?? null;

        if ($data['status'] === 'published' && blank($publishedAt)) {
            $publishedAt = $article->published_at ?? now();
        }

        $article->fill([
            'slug' => ($data['slug'] ?? null) ?: $article->slug,
            'news_category_id' => $data['news_category_id'] ?? null,
            'user_id' => $article->user_id ?? $request->user()?->id,
            'title' => $data['title'],
            'excerpt' => $data['excerpt'] ?? Locales::blankMap(),
            'body' => RichText::cleanMap($data['body'] ?? []),
            'status' => $data['status'],
            'published_at' => $publishedAt,
            'is_featured' => $data['is_featured'] ?? false,
            'featured_order' => $data['featured_order'] ?? 0,
            'seo_title' => $data['seo_title'] ?? Locales::blankMap(),
            'seo_description' => $data['seo_description'] ?? Locales::blankMap(),
            'external_url' => trim((string) ($data['external_url'] ?? '')) ?: null,
            'cover_path' => MediaUploader::store($data['cover_path'] ?? null, 'news/covers', $article->cover_path),
        ])->save();

        return $article;
    }

    protected function payload(News $article): array
    {
        return [
            'id' => $article->id,
            'slug' => $article->slug,
            'news_category_id' => $article->news_category_id,
            'title' => $this->map($article, 'title'),
            'excerpt' => $this->map($article, 'excerpt'),
            'body' => $this->map($article, 'body'),
            'cover_path' => $article->cover_path,
            'cover_url' => News::mediaUrl($article->cover_path),
            'external_url' => $article->external_url,
            'status' => $article->status,
            'published_at' => $article->published_at?->format('Y-m-d\TH:i'),
            'is_featured' => $article->is_featured,
            'featured_order' => $article->featured_order,
            'seo_title' => $this->map($article, 'seo_title'),
            'seo_description' => $this->map($article, 'seo_description'),
            'views' => $article->views,
            'author' => $article->author?->name,
        ];
    }

    protected function blank(): array
    {
        return [
            'id' => null,
            'slug' => '',
            'news_category_id' => null,
            'title' => Locales::blankMap(''),
            'excerpt' => Locales::blankMap(''),
            'body' => Locales::blankMap(''),
            'cover_path' => null, 'cover_url' => null,
            'external_url' => '',
            'status' => 'draft',
            'published_at' => null,
            'is_featured' => false,
            'featured_order' => 0,
            'seo_title' => Locales::blankMap(''),
            'seo_description' => Locales::blankMap(''),
            'views' => 0,
            'author' => null,
        ];
    }

    protected function categoryOptions(): array
    {
        return NewsCategory::query()
            ->orderBy('sort_order')
            ->get()
            ->map(fn (NewsCategory $category) => [
                'value' => $category->id,
                'slug' => $category->slug,
                'label' => Locales::pick($category->getTranslations('name'), Locales::default()),
                'color' => $category->color,
            ])->all();
    }

    protected function title(News $article): string
    {
        return (string) Locales::pick($article->getTranslations('title'), Locales::default());
    }
}
