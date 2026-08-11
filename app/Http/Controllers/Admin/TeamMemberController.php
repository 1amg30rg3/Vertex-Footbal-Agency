<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Admin\Concerns\EditsTranslations;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\TeamMemberRequest;
use App\Models\ActivityLog;
use App\Models\TeamMember;
use App\Support\Locales;
use App\Support\MediaUploader;
use App\Support\RichText;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Inertia\Response;

class TeamMemberController extends Controller
{
    use EditsTranslations;

    public function index(Request $request): Response
    {
        $members = TeamMember::query()
            ->search($request->string('search')->toString() ?: null)
            ->when($request->string('status')->toString(), fn ($q, $status) => $q->where('status', $status))
            ->ordered()
            ->paginate(20)
            ->withQueryString();

        return Inertia::render('Admin/Team/Index', [
            'members' => [
                'data' => collect($members->items())->map(fn (TeamMember $member) => [
                    'id' => $member->id,
                    'slug' => $member->slug,
                    'name' => Locales::pick($member->getTranslations('name'), Locales::default()),
                    'role' => Locales::pick($member->getTranslations('role'), Locales::default()),
                    'photo' => TeamMember::mediaUrl($member->photo_path),
                    'email' => $member->email,
                    'status' => $member->status,
                    'sort_order' => $member->sort_order,
                    'updated_at' => $member->updated_at?->diffForHumans(),
                ])->all(),
                'meta' => $this->paginationMeta($members),
            ],
            'filters' => $request->only('search', 'status'),
            'statuses' => TeamMember::STATUSES,
        ]);
    }

    public function create(): Response
    {
        return Inertia::render('Admin/Team/Form', [
            'member' => $this->blank(),
            'options' => ['statuses' => TeamMember::STATUSES],
        ]);
    }

    public function store(TeamMemberRequest $request): RedirectResponse
    {
        $member = DB::transaction(fn () => $this->persist(new TeamMember, $request));

        ActivityLog::record('created', $member, 'Created team member "'.$this->name($member).'"');

        return redirect()->route('admin.team.members.edit', $member)->with('success', 'Team member created.');
    }

    public function edit(TeamMember $member): Response
    {
        return Inertia::render('Admin/Team/Form', [
            'member' => $this->payload($member),
            'options' => ['statuses' => TeamMember::STATUSES],
        ]);
    }

    public function update(TeamMemberRequest $request, TeamMember $member): RedirectResponse
    {
        DB::transaction(fn () => $this->persist($member, $request));

        ActivityLog::record('updated', $member, 'Updated team member "'.$this->name($member).'"');

        return back()->with('success', 'Team member saved.');
    }

    public function destroy(TeamMember $member): RedirectResponse
    {
        $name = $this->name($member);
        $member->delete();

        ActivityLog::record('deleted', $member, "Deleted team member \"{$name}\"");

        return redirect()->route('admin.team.members.index')->with('success', 'Team member deleted.');
    }

    public function reorder(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'ids' => ['required', 'array'],
            'ids.*' => ['integer', 'exists:team_members,id'],
        ]);

        DB::transaction(function () use ($data) {
            foreach ($data['ids'] as $index => $id) {
                TeamMember::whereKey($id)->update(['sort_order' => $index]);
            }
        });

        return back()->with('success', 'Order updated.');
    }

    protected function persist(TeamMember $member, TeamMemberRequest $request): TeamMember
    {
        $data = $request->validated();

        $member->fill([
            'slug' => ($data['slug'] ?? null) ?: $member->slug,
            'name' => $data['name'],
            'role' => $data['role'] ?? Locales::blankMap(),
            'bio' => RichText::cleanMap($data['bio'] ?? []),
            'email' => $data['email'] ?? null,
            'phone' => $data['phone'] ?? null,
            'social_links' => array_values($data['social_links'] ?? []),
            'status' => $data['status'],
            'sort_order' => $data['sort_order'] ?? 0,
            'photo_path' => MediaUploader::store($data['photo_path'] ?? null, 'team/photos', $member->photo_path),
        ])->save();

        return $member;
    }

    protected function payload(TeamMember $member): array
    {
        return [
            'id' => $member->id,
            'slug' => $member->slug,
            'name' => $this->map($member, 'name'),
            'role' => $this->map($member, 'role'),
            'bio' => $this->map($member, 'bio'),
            'photo_path' => $member->photo_path,
            'photo_url' => TeamMember::mediaUrl($member->photo_path),
            'email' => $member->email,
            'phone' => $member->phone,
            'social_links' => $member->social_links ?? [],
            'status' => $member->status,
            'sort_order' => $member->sort_order,
        ];
    }

    protected function blank(): array
    {
        return [
            'id' => null,
            'slug' => '',
            'name' => Locales::blankMap(''),
            'role' => Locales::blankMap(''),
            'bio' => Locales::blankMap(''),
            'photo_path' => null, 'photo_url' => null,
            'email' => null, 'phone' => null,
            'social_links' => [],
            'status' => 'published',
            'sort_order' => 0,
        ];
    }

    protected function name(TeamMember $member): string
    {
        return (string) Locales::pick($member->getTranslations('name'), Locales::default());
    }
}
