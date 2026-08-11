<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Admin\Concerns\EditsTranslations;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\PlayerRequest;
use App\Models\ActivityLog;
use App\Models\Player;
use App\Models\PlayerSeason;
use App\Support\Locales;
use App\Support\MediaUploader;
use App\Support\RepeaterSync;
use App\Support\RichText;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Inertia\Response;

class PlayerController extends Controller
{
    use EditsTranslations;

    public function index(Request $request): Response
    {
        $players = Player::query()
            ->search($request->string('search')->toString() ?: null)
            ->when($request->string('status')->toString(), fn ($q, $status) => $q->where('status', $status))
            ->when($request->string('position')->toString(), fn ($q, $position) => $q->where('position', $position))
            ->withCount(['seasons', 'photos'])
            ->orderBy($this->sortColumn($request), $request->string('direction')->toString() === 'desc' ? 'desc' : 'asc')
            ->paginate(15)
            ->withQueryString();

        return Inertia::render('Admin/Players/Index', [
            'players' => [
                'data' => collect($players->items())->map(fn (Player $player) => [
                    'id' => $player->id,
                    'slug' => $player->slug,
                    'name' => $player->fullName(Locales::default()),
                    'photo' => Player::mediaUrl($player->photo_path),
                    'position' => $player->position,
                    'current_club' => $player->current_club,
                    'status' => $player->status,
                    'sort_order' => $player->sort_order,
                    'is_featured' => $player->is_featured,
                    'seasons_count' => $player->seasons_count,
                    'photos_count' => $player->photos_count,
                    'updated_at' => $player->updated_at?->diffForHumans(),
                ])->all(),
                'meta' => $this->paginationMeta($players),
            ],
            'filters' => $request->only('search', 'status', 'position', 'sort', 'direction'),
            'positions' => Player::POSITIONS,
            'statuses' => Player::STATUSES,
        ]);
    }

    public function create(): Response
    {
        return Inertia::render('Admin/Players/Form', [
            'player' => $this->blank(),
            'options' => $this->options(),
        ]);
    }

    public function store(PlayerRequest $request): RedirectResponse
    {
        $player = DB::transaction(fn () => $this->persist(new Player, $request));

        ActivityLog::record('created', $player, "Created player \"{$player->fullName(Locales::default())}\"");

        return redirect()
            ->route('admin.players.edit', $player)
            ->with('success', 'Player created.')
            ->with('info', $this->warningText($request));
    }

    public function edit(Player $player): Response
    {
        $player->load(['skills', 'careerEntries', 'achievements', 'seasons.months', 'photos']);

        return Inertia::render('Admin/Players/Form', [
            'player' => $this->payload($player),
            'options' => $this->options(),
        ]);
    }

    public function update(PlayerRequest $request, Player $player): RedirectResponse
    {
        DB::transaction(fn () => $this->persist($player, $request));

        ActivityLog::record('updated', $player, "Updated player \"{$player->fullName(Locales::default())}\"");

        return back()
            ->with('success', 'Player saved.')
            ->with('info', $this->warningText($request));
    }

    public function destroy(Player $player): RedirectResponse
    {
        $name = $player->fullName(Locales::default());
        $player->delete();

        ActivityLog::record('deleted', $player, "Deleted player \"{$name}\"");

        return redirect()->route('admin.players.index')->with('success', 'Player deleted.');
    }

    public function reorder(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'ids' => ['required', 'array'],
            'ids.*' => ['integer', 'exists:players,id'],
        ]);

        DB::transaction(function () use ($data) {
            foreach ($data['ids'] as $index => $id) {
                Player::whereKey($id)->update(['sort_order' => $index]);
            }
        });

        return back()->with('success', 'Order updated.');
    }

    protected function persist(Player $player, PlayerRequest $request): Player
    {
        $data = $request->validated();

        $player->fill([
            'slug' => ($data['slug'] ?? null) ?: $player->slug,
            'first_name' => $data['first_name'],
            'last_name' => $data['last_name'],
            'specific_position' => $data['specific_position'] ?? Locales::blankMap(),
            'date_of_birth' => $data['date_of_birth'] ?? null,
            'nationality' => $data['nationality'] ?? null,
            'height_cm' => $data['height_cm'] ?? null,
            'weight_kg' => $data['weight_kg'] ?? null,
            'position' => $data['position'] ?? null,
            'preferred_foot' => $data['preferred_foot'] ?? null,
            'current_club' => $data['current_club'] ?? null,
            'contract_until' => $data['contract_until'] ?? null,
            'phone' => $data['phone'] ?? null,
            'email' => $data['email'] ?? null,
            'instagram' => $data['instagram'] ?? null,
            'city' => $data['city'] ?? null,
            'country' => $data['country'] ?? null,
            'playing_style' => RichText::cleanMap($data['playing_style'] ?? []),
            'pitch_x' => $data['pitch_x'] ?? null,
            'pitch_y' => $data['pitch_y'] ?? null,
            'goals_short_term' => RichText::cleanMap($data['goals_short_term'] ?? []),
            'goals_mid_term' => RichText::cleanMap($data['goals_mid_term'] ?? []),
            'goals_long_term' => RichText::cleanMap($data['goals_long_term'] ?? []),
            'quote' => $data['quote'] ?? Locales::blankMap(),
            'status' => $data['status'],
            'sort_order' => $data['sort_order'] ?? 0,
            'is_featured' => $data['is_featured'] ?? false,
            'seo_title' => $data['seo_title'] ?? Locales::blankMap(),
            'seo_description' => $data['seo_description'] ?? Locales::blankMap(),
            'photo_path' => MediaUploader::store($data['photo_path'] ?? null, 'players/photos', $player->photo_path),
            'cover_path' => MediaUploader::store($data['cover_path'] ?? null, 'players/covers', $player->cover_path),
            'current_club_logo_path' => MediaUploader::store(
                $data['current_club_logo_path'] ?? null,
                'players/clubs',
                $player->current_club_logo_path
            ),
        ])->save();

        $this->syncSkills($player, $data['skills'] ?? []);
        $this->syncCareer($player, $data['career'] ?? []);
        $this->syncAchievements($player, $data['achievements'] ?? []);
        $this->syncSeasons($player, $data['seasons'] ?? []);
        $this->syncPhotos($player, $data['photos'] ?? []);

        return $player;
    }

    protected function syncSkills(Player $player, array $rows): void
    {
        RepeaterSync::sync($player->skills(), $rows, fn (array $row, int $index) => [
            'label' => Locales::normalizeMap($row['label'] ?? []),
            'value' => (int) ($row['value'] ?? 0),
            'sort_order' => $index,
        ]);
    }

    protected function syncCareer(Player $player, array $rows): void
    {
        RepeaterSync::sync(
            $player->careerEntries(),
            $rows,
            fn (array $row, int $index) => [
                'club_name' => $row['club_name'],
                'started_on' => $row['started_on'] ?? null,
                'ended_on' => $row['ended_on'] ?? null,
                'category' => $row['category'] ?? null,
                'league' => Locales::normalizeMap($row['league'] ?? []),
                'notes' => Locales::normalizeMap($row['notes'] ?? []),
                'sort_order' => $index,
            ],
            mediaFields: ['club_logo_path' => 'players/clubs'],
        );
    }

    protected function syncAchievements(Player $player, array $rows): void
    {
        RepeaterSync::sync($player->achievements(), $rows, fn (array $row, int $index) => [
            'text' => Locales::normalizeMap($row['text'] ?? []),
            'year' => $row['year'] ?? null,
            'sort_order' => $index,
        ]);
    }

    protected function syncSeasons(Player $player, array $rows): void
    {
        RepeaterSync::sync(
            $player->seasons(),
            $rows,
            fn (array $row, int $index) => [
                'label' => $row['label'],
                'club_name' => $row['club_name'] ?? null,
                'matches_played' => (int) ($row['matches_played'] ?? 0),
                'goals' => (int) ($row['goals'] ?? 0),
                'assists' => (int) ($row['assists'] ?? 0),
                'minutes_played' => (int) ($row['minutes_played'] ?? 0),
                'starting_pct' => (int) ($row['starting_pct'] ?? 0),
                'substitute_pct' => (int) ($row['substitute_pct'] ?? 0),
                'not_in_squad_pct' => (int) ($row['not_in_squad_pct'] ?? 0),
                'is_current' => (bool) ($row['is_current'] ?? false),
                'sort_order' => $index,
            ],
            afterEach: function (PlayerSeason $season, array $row) {
                RepeaterSync::sync($season->months(), $row['months'] ?? [], fn (array $month, int $i) => [
                    'month' => (int) $month['month'],
                    'goals' => (int) ($month['goals'] ?? 0),
                    'assists' => (int) ($month['assists'] ?? 0),
                    'sort_order' => $i,
                ]);
            },
        );

        $current = $player->seasons()->where('is_current', true)->latest('id')->first();

        if ($current) {
            $player->seasons()->whereKeyNot($current->getKey())->update(['is_current' => false]);
        }
    }

    protected function syncPhotos(Player $player, array $rows): void
    {
        RepeaterSync::sync(
            $player->photos(),
            $rows,
            fn (array $row, int $index) => [
                'caption' => Locales::normalizeMap($row['caption'] ?? []),
                'sort_order' => $index,
            ],
            mediaFields: ['path' => 'players/gallery'],
        );
    }

    protected function payload(Player $player): array
    {
        return [
            'id' => $player->id,
            'slug' => $player->slug,
            'first_name' => $this->map($player, 'first_name'),
            'last_name' => $this->map($player, 'last_name'),
            'specific_position' => $this->map($player, 'specific_position'),
            'photo_path' => $player->photo_path,
            'photo_url' => Player::mediaUrl($player->photo_path),
            'cover_path' => $player->cover_path,
            'cover_url' => Player::mediaUrl($player->cover_path),
            'current_club_logo_path' => $player->current_club_logo_path,
            'current_club_logo_url' => Player::mediaUrl($player->current_club_logo_path),
            'date_of_birth' => $player->date_of_birth?->format('Y-m-d'),
            'nationality' => $player->nationality,
            'height_cm' => $player->height_cm,
            'weight_kg' => $player->weight_kg,
            'position' => $player->position,
            'preferred_foot' => $player->preferred_foot,
            'current_club' => $player->current_club,
            'contract_until' => $player->contract_until?->format('Y-m-d'),
            'phone' => $player->phone,
            'email' => $player->email,
            'instagram' => $player->instagram,
            'city' => $player->city,
            'country' => $player->country,
            'playing_style' => $this->map($player, 'playing_style'),
            'pitch_x' => $player->pitch_x,
            'pitch_y' => $player->pitch_y,
            'goals_short_term' => $this->map($player, 'goals_short_term'),
            'goals_mid_term' => $this->map($player, 'goals_mid_term'),
            'goals_long_term' => $this->map($player, 'goals_long_term'),
            'quote' => $this->map($player, 'quote'),
            'status' => $player->status,
            'sort_order' => $player->sort_order,
            'is_featured' => $player->is_featured,
            'seo_title' => $this->map($player, 'seo_title'),
            'seo_description' => $this->map($player, 'seo_description'),

            'skills' => $player->skills->map(fn ($skill) => [
                'id' => $skill->id,
                'label' => $this->map($skill, 'label'),
                'value' => $skill->value,
            ])->values()->all(),

            'career' => $player->careerEntries->map(fn ($entry) => [
                'id' => $entry->id,
                'club_name' => $entry->club_name,
                'club_logo_path' => $entry->club_logo_path,
                'club_logo_url' => $entry::mediaUrl($entry->club_logo_path),
                'started_on' => $entry->started_on?->format('Y-m-d'),
                'ended_on' => $entry->ended_on?->format('Y-m-d'),
                'category' => $entry->category,
                'league' => $this->map($entry, 'league'),
                'notes' => $this->map($entry, 'notes'),
            ])->values()->all(),

            'achievements' => $player->achievements->map(fn ($item) => [
                'id' => $item->id,
                'text' => $this->map($item, 'text'),
                'year' => $item->year,
            ])->values()->all(),

            'seasons' => $player->seasons->map(fn (PlayerSeason $season) => [
                'id' => $season->id,
                'label' => $season->label,
                'club_name' => $season->club_name,
                'matches_played' => $season->matches_played,
                'goals' => $season->goals,
                'assists' => $season->assists,
                'minutes_played' => $season->minutes_played,
                'starting_pct' => $season->starting_pct,
                'substitute_pct' => $season->substitute_pct,
                'not_in_squad_pct' => $season->not_in_squad_pct,
                'is_current' => $season->is_current,
                'months' => $season->months->map(fn ($month) => [
                    'id' => $month->id,
                    'month' => $month->month,
                    'goals' => $month->goals,
                    'assists' => $month->assists,
                ])->values()->all(),
            ])->values()->all(),

            'photos' => $player->photos->map(fn ($photo) => [
                'id' => $photo->id,
                'path' => $photo->path,
                'url' => $photo::mediaUrl($photo->path),
                'caption' => $this->map($photo, 'caption'),
            ])->values()->all(),
        ];
    }

    protected function blank(): array
    {
        return [
            'id' => null,
            'slug' => '',
            'first_name' => Locales::blankMap(''),
            'last_name' => Locales::blankMap(''),
            'specific_position' => Locales::blankMap(''),
            'photo_path' => null, 'photo_url' => null,
            'cover_path' => null, 'cover_url' => null,
            'current_club_logo_path' => null, 'current_club_logo_url' => null,
            'date_of_birth' => null,
            'nationality' => null,
            'height_cm' => null,
            'weight_kg' => null,
            'position' => null,
            'preferred_foot' => null,
            'current_club' => null,
            'contract_until' => null,
            'phone' => null, 'email' => null, 'instagram' => null,
            'city' => null, 'country' => null,
            'playing_style' => Locales::blankMap(''),
            'pitch_x' => 50, 'pitch_y' => 50,
            'goals_short_term' => Locales::blankMap(''),
            'goals_mid_term' => Locales::blankMap(''),
            'goals_long_term' => Locales::blankMap(''),
            'quote' => Locales::blankMap(''),
            'status' => 'draft',
            'sort_order' => 0,
            'is_featured' => false,
            'seo_title' => Locales::blankMap(''),
            'seo_description' => Locales::blankMap(''),
            'skills' => [],
            'career' => [],
            'achievements' => [],
            'seasons' => [],
            'photos' => [],
        ];
    }

    protected function options(): array
    {
        return [
            'positions' => Player::POSITIONS,
            'feet' => Player::FEET,
            'statuses' => Player::STATUSES,
            'suggestedSkills' => [
                'Game Intelligence', 'Passing Accuracy', 'Duel / Tackling',
                'Work Rate / Stamina', 'Tactical Understanding', 'Shot Power',
            ],
        ];
    }

    protected function sortColumn(Request $request): string
    {
        $sort = $request->string('sort')->toString();

        return in_array($sort, ['sort_order', 'status', 'position', 'current_club', 'updated_at'], true)
            ? $sort
            : 'sort_order';
    }

    protected function warningText(PlayerRequest $request): ?string
    {
        $warnings = $request->playingTimeWarnings();

        return $warnings ? implode(' ', $warnings) : null;
    }
}
