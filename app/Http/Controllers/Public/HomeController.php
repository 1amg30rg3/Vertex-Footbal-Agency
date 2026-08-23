<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\News;
use App\Models\Player;
use App\Models\Setting;
use App\Models\Trainer;
use App\Support\Presenters\NewsPresenter;
use App\Support\Presenters\PlayerPresenter;
use Illuminate\Support\Str;
use Inertia\Inertia;
use Inertia\Response;

class HomeController extends Controller
{
    public function __invoke(): Response
    {
        $limit = max(1, min(12, (int) Setting::get('featured_news_limit', 3)));

        $featured = News::query()
            ->with('category')
            ->live()
            ->featured()
            ->limit($limit)
            ->get();

        if ($featured->isEmpty()) {
            $featured = News::query()->with('category')->live()->recent()->limit($limit)->get();
        }

        $this->seo()->description(__('ui.home.info_heading'));

        $clubs = $this->clubs();

        return Inertia::render('Public/Home', [
            'featuredNews' => NewsPresenter::make()->collection($featured),
            'players' => PlayerPresenter::make()->collection(
                Player::query()->published()->ordered()->limit(6)->get()
            ),
            'stats' => $this->stats(count($clubs)),
            'clubs' => $clubs,
        ]);
    }

    protected function stats(int $clubCount): array
    {
        $roster = Player::query()
            ->published()
            ->selectRaw('count(*) as players')
            ->selectRaw('count(distinct nullif(trim(nationality), \'\')) as countries')
            ->first();

        return [
            'players' => (int) ($roster?->players ?? 0),
            'clubs' => $clubCount,
            'countries' => (int) ($roster?->countries ?? 0),
            'trainers' => Trainer::query()->published()->count(),
        ];
    }

    protected function clubs(): array
    {
        return Player::query()
            ->published()
            ->whereNotNull('current_club')
            ->where('current_club', '!=', '')
            ->pluck('current_club')
            ->map(fn (string $club) => Str::upper(preg_replace('/\s+/u', ' ', trim($club))))
            ->filter()
            ->unique()
            ->sort(SORT_NATURAL)
            ->values()
            ->all();
    }
}
