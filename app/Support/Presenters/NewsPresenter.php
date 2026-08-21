<?php

namespace App\Support\Presenters;

use App\Models\News;
use App\Support\RichText;
use Illuminate\Database\Eloquent\Model;

class NewsPresenter extends Presenter
{
    public function card(Model $article): array
    {
        /** @var News $article */
        return [
            'id' => $article->id,
            'slug' => $article->slug,
            'title' => $this->t($article, 'title'),
            'excerpt' => $this->t($article, 'excerpt')
                ?: RichText::excerpt($this->t($article, 'body'), 150),
            'cover' => News::mediaUrl($article->cover_path),
            'external_url' => $article->external_url ?: null,
            'published_at' => $article->published_at?->toIso8601String(),
            'published_date' => $this->date($article->published_at),
            'is_featured' => $article->is_featured,
            'reading_minutes' => $article->readingMinutes($this->locale),
            'category' => $article->relationLoaded('category') && $article->category
                ? [
                    'id' => $article->category->id,
                    'slug' => $article->category->slug,
                    'name' => $this->t($article->category, 'name'),
                    'color' => $article->category->color,
                ]
                : null,
        ];
    }

    public function detail(Model $article): array
    {
        /** @var News $article */
        return array_merge($this->card($article), [
            'body' => $this->t($article, 'body'),
            'author' => $article->relationLoaded('author') && $article->author
                ? ['name' => $article->author->name, 'avatar' => $article->author->avatarUrl()]
                : null,
            'seo' => [
                'title' => $this->t($article, 'seo_title') ?: $this->t($article, 'title'),
                'description' => $this->t($article, 'seo_description')
                    ?: ($this->t($article, 'excerpt') ?: RichText::excerpt($this->t($article, 'body'))),
                'image' => News::absoluteMediaUrl($article->cover_path),
            ],
        ]);
    }

    public function category(Model $category): array
    {
        return [
            'id' => $category->id,
            'slug' => $category->slug,
            'name' => $this->t($category, 'name'),
            'color' => $category->color,
            'count' => $category->news_count ?? null,
        ];
    }
}
