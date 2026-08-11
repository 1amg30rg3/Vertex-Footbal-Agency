<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Admin\Concerns\EditsTranslations;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\NewsCategoryRequest;
use App\Models\NewsCategory;
use App\Support\Locales;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;

class NewsCategoryController extends Controller
{
    use EditsTranslations;

    public function index(): Response
    {
        return Inertia::render('Admin/News/Categories', [
            'categories' => NewsCategory::query()
                ->withCount('news')
                ->orderBy('sort_order')
                ->get()
                ->map(fn (NewsCategory $category) => [
                    'id' => $category->id,
                    'slug' => $category->slug,
                    'name' => $this->map($category, 'name'),
                    'display_name' => Locales::pick($category->getTranslations('name'), Locales::default()),
                    'color' => $category->color,
                    'sort_order' => $category->sort_order,
                    'news_count' => $category->news_count,
                ])->all(),
        ]);
    }

    public function store(NewsCategoryRequest $request): RedirectResponse
    {
        NewsCategory::create($this->attributes($request));

        return back()->with('success', 'Category created.');
    }

    public function update(NewsCategoryRequest $request, NewsCategory $category): RedirectResponse
    {
        $category->update($this->attributes($request));

        return back()->with('success', 'Category saved.');
    }

    public function destroy(NewsCategory $category): RedirectResponse
    {
        $category->delete();

        return back()->with('success', 'Category deleted.');
    }

    protected function attributes(NewsCategoryRequest $request): array
    {
        $data = $request->validated();

        return array_filter([
            'slug' => ($data['slug'] ?? null) ?: null,
            'name' => $data['name'],
            'color' => $data['color'] ?? null,
            'sort_order' => $data['sort_order'] ?? 0,
        ], fn ($value) => $value !== null);
    }
}
