<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\Player;
use App\Support\Presenters\PlayerPresenter;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class PlayerController extends Controller
{
    public function index(Request $request): Response
    {
        $filters = [
            'search' => $request->string('search')->toString() ?: null,
            'position' => $request->string('position')->toString() ?: null,
        ];

        $players = Player::query()
            ->published()
            ->search($filters['search'])
            ->when(
                in_array($filters['position'], Player::POSITIONS, true),
                fn ($query) => $query->where('position', $filters['position'])
            )
            ->ordered()
            ->paginate(12)
            ->withQueryString();

        return Inertia::render('Public/Players/Index', [
            'players' => [
                'data' => PlayerPresenter::make()->collection($players->items()),
                'meta' => $this->paginationMeta($players),
            ],
            'filters' => $filters,
            'positions' => Player::POSITIONS,
        ]);
    }

    public function show(string $locale, Player $player): Response
    {
        abort_unless($player->status === 'published', 404);

        $player->load(['skills', 'careerEntries', 'achievements', 'seasons.months', 'photos', 'links']);

        $presenter = PlayerPresenter::make();

        return Inertia::render('Public/Players/Show', [
            'player' => $presenter->detail($player),
            'related' => $presenter->collection(
                Player::query()
                    ->published()
                    ->whereKeyNot($player->getKey())
                    ->when($player->position, fn ($q) => $q->where('position', $player->position))
                    ->ordered()
                    ->limit(3)
                    ->get()
            ),
        ]);
    }
}
