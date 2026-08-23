<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\News;
use App\Models\Player;
use App\Models\Trainer;
use App\Support\Locales;
use Illuminate\Http\Response;

class SitemapController extends Controller
{
    /**
     * Every public URL in every language, with hreflang alternates on each
     * entry so Google can group the translations rather than treating them as
     * duplicates of one another.
     */
    public function __invoke(): Response
    {
        $urls = [];

        foreach ($this->staticRoutes() as $name => $priority) {
            $urls[] = $this->entry(fn (string $locale) => route($name, ['locale' => $locale]), null, $priority, 'weekly');
        }

        foreach (Player::query()->published()->get(['slug', 'updated_at']) as $player) {
            $urls[] = $this->entry(
                fn (string $locale) => route('public.players.show', ['locale' => $locale, 'player' => $player->slug]),
                $player->updated_at,
                0.8,
            );
        }

        foreach (Trainer::query()->published()->get(['slug', 'updated_at']) as $trainer) {
            $urls[] = $this->entry(
                fn (string $locale) => route('public.trainers.show', ['locale' => $locale, 'trainer' => $trainer->slug]),
                $trainer->updated_at,
                0.7,
            );
        }

        foreach (News::query()->live()->get(['slug', 'updated_at']) as $article) {
            $urls[] = $this->entry(
                fn (string $locale) => route('public.news.show', ['locale' => $locale, 'article' => $article->slug]),
                $article->updated_at,
                0.7,
            );
        }

        return response()
            ->view('sitemap', ['urls' => $urls])
            ->header('Content-Type', 'application/xml');
    }

    /** @return array<string, float> route name => priority */
    private function staticRoutes(): array
    {
        return [
            'public.home' => 1.0,
            'public.players.index' => 0.9,
            'public.trainers.index' => 0.8,
            'public.team' => 0.7,
            'public.news.index' => 0.8,
            'public.about' => 0.7,
            'public.contacts' => 0.6,
        ];
    }

    /**
     * @param  callable(string): string  $url
     * @return array<string, mixed>
     */
    private function entry(callable $url, mixed $lastmod, float $priority, string $frequency = 'monthly'): array
    {
        $alternates = [];

        foreach (Locales::codes() as $code) {
            $alternates[$code] = $url($code);
        }

        return [
            'loc' => $alternates[Locales::default()],
            'lastmod' => $lastmod?->toAtomString(),
            'changefreq' => $frequency,
            'priority' => number_format($priority, 1),
            'alternates' => $alternates,
        ];
    }
}
