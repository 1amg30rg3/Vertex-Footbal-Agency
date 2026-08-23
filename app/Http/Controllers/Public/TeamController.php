<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\TeamMember;
use App\Support\Presenters\TeamMemberPresenter;
use Inertia\Inertia;
use Inertia\Response;

class TeamController extends Controller
{
    public function __invoke(): Response
    {
        $this->seo()->title(__('ui.team.title'))->description(__('ui.team.lead'));

        return Inertia::render('Public/Team', [
            'members' => TeamMemberPresenter::make()->collection(
                TeamMember::query()->published()->ordered()->get()
            ),
        ]);
    }
}
