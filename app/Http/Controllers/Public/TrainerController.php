<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\Trainer;
use App\Support\Presenters\TrainerPresenter;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class TrainerController extends Controller
{
    public function index(Request $request): Response
    {
        $search = $request->string('search')->toString() ?: null;

        $trainers = Trainer::query()
            ->published()
            ->search($search)
            ->ordered()
            ->paginate(12)
            ->withQueryString();

        return Inertia::render('Public/Trainers/Index', [
            'trainers' => [
                'data' => TrainerPresenter::make()->collection($trainers->items()),
                'meta' => $this->paginationMeta($trainers),
            ],
            'filters' => ['search' => $search],
        ]);
    }

    public function show(string $locale, Trainer $trainer): Response
    {
        abort_unless($trainer->status === 'published', 404);

        $trainer->load('workEntries');

        $presenter = TrainerPresenter::make();

        return Inertia::render('Public/Trainers/Show', [
            'trainer' => $presenter->detail($trainer),
            'related' => $presenter->collection(
                Trainer::query()->published()->whereKeyNot($trainer->getKey())->ordered()->limit(3)->get()
            ),
        ]);
    }
}
