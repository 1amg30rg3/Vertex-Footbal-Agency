<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Admin\Concerns\EditsTranslations;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\TrainerRequest;
use App\Models\ActivityLog;
use App\Models\Trainer;
use App\Support\Locales;
use App\Support\MediaUploader;
use App\Support\RepeaterSync;
use App\Support\RichText;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Inertia\Response;

class TrainerController extends Controller
{
    use EditsTranslations;

    public function index(Request $request): Response
    {
        $trainers = Trainer::query()
            ->search($request->string('search')->toString() ?: null)
            ->when($request->string('status')->toString(), fn ($q, $status) => $q->where('status', $status))
            ->withCount('workEntries')
            ->ordered()
            ->paginate(15)
            ->withQueryString();

        return Inertia::render('Admin/Trainers/Index', [
            'trainers' => [
                'data' => collect($trainers->items())->map(fn (Trainer $trainer) => [
                    'id' => $trainer->id,
                    'slug' => $trainer->slug,
                    'name' => $trainer->fullName(Locales::default()),
                    'role' => Locales::pick($trainer->getTranslations('role'), Locales::default()),
                    'photo' => Trainer::mediaUrl($trainer->photo_path),
                    'status' => $trainer->status,
                    'sort_order' => $trainer->sort_order,
                    'work_entries_count' => $trainer->work_entries_count,
                    'updated_at' => $trainer->updated_at?->diffForHumans(),
                ])->all(),
                'meta' => $this->paginationMeta($trainers),
            ],
            'filters' => $request->only('search', 'status'),
            'statuses' => Trainer::STATUSES,
        ]);
    }

    public function create(): Response
    {
        return Inertia::render('Admin/Trainers/Form', [
            'trainer' => $this->blank(),
            'options' => ['statuses' => Trainer::STATUSES],
        ]);
    }

    public function store(TrainerRequest $request): RedirectResponse
    {
        $trainer = DB::transaction(fn () => $this->persist(new Trainer, $request));

        ActivityLog::record('created', $trainer, "Created trainer \"{$trainer->fullName(Locales::default())}\"");

        return redirect()->route('admin.trainers.edit', $trainer)->with('success', 'Trainer created.');
    }

    public function edit(Trainer $trainer): Response
    {
        $trainer->load('workEntries');

        return Inertia::render('Admin/Trainers/Form', [
            'trainer' => $this->payload($trainer),
            'options' => ['statuses' => Trainer::STATUSES],
        ]);
    }

    public function update(TrainerRequest $request, Trainer $trainer): RedirectResponse
    {
        DB::transaction(fn () => $this->persist($trainer, $request));

        ActivityLog::record('updated', $trainer, "Updated trainer \"{$trainer->fullName(Locales::default())}\"");

        return back()->with('success', 'Trainer saved.');
    }

    public function destroy(Trainer $trainer): RedirectResponse
    {
        $name = $trainer->fullName(Locales::default());
        $trainer->delete();

        ActivityLog::record('deleted', $trainer, "Deleted trainer \"{$name}\"");

        return redirect()->route('admin.trainers.index')->with('success', 'Trainer deleted.');
    }

    public function reorder(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'ids' => ['required', 'array'],
            'ids.*' => ['integer', 'exists:trainers,id'],
        ]);

        DB::transaction(function () use ($data) {
            foreach ($data['ids'] as $index => $id) {
                Trainer::whereKey($id)->update(['sort_order' => $index]);
            }
        });

        return back()->with('success', 'Order updated.');
    }

    protected function persist(Trainer $trainer, TrainerRequest $request): Trainer
    {
        $data = $request->validated();

        $trainer->fill([
            'slug' => ($data['slug'] ?? null) ?: $trainer->slug,
            'first_name' => $data['first_name'],
            'last_name' => $data['last_name'],
            'role' => $data['role'] ?? Locales::blankMap(),
            'bio' => RichText::cleanMap($data['bio'] ?? []),
            'nationality' => $data['nationality'] ?? null,
            'date_of_birth' => $data['date_of_birth'] ?? null,
            'email' => $data['email'] ?? null,
            'phone' => $data['phone'] ?? null,
            'instagram' => $data['instagram'] ?? null,
            'linkedin' => $data['linkedin'] ?? null,
            'status' => $data['status'],
            'sort_order' => $data['sort_order'] ?? 0,
            'seo_title' => $data['seo_title'] ?? Locales::blankMap(),
            'seo_description' => $data['seo_description'] ?? Locales::blankMap(),
            'photo_path' => MediaUploader::store($data['photo_path'] ?? null, 'trainers/photos', $trainer->photo_path),
            'cover_path' => MediaUploader::store($data['cover_path'] ?? null, 'trainers/covers', $trainer->cover_path),
        ])->save();

        RepeaterSync::sync(
            $trainer->workEntries(),
            $data['work'] ?? [],
            fn (array $row, int $index) => [
                'organization' => $row['organization'],
                'title' => Locales::normalizeMap($row['title'] ?? []),
                'started_on' => $row['started_on'] ?? null,
                'ended_on' => $row['ended_on'] ?? null,
                'notes' => Locales::normalizeMap($row['notes'] ?? []),
                'sort_order' => $index,
            ],
            mediaFields: ['logo_path' => 'trainers/logos'],
        );

        return $trainer;
    }

    protected function payload(Trainer $trainer): array
    {
        return [
            'id' => $trainer->id,
            'slug' => $trainer->slug,
            'first_name' => $this->map($trainer, 'first_name'),
            'last_name' => $this->map($trainer, 'last_name'),
            'role' => $this->map($trainer, 'role'),
            'bio' => $this->map($trainer, 'bio'),
            'photo_path' => $trainer->photo_path,
            'photo_url' => Trainer::mediaUrl($trainer->photo_path),
            'cover_path' => $trainer->cover_path,
            'cover_url' => Trainer::mediaUrl($trainer->cover_path),
            'nationality' => $trainer->nationality,
            'date_of_birth' => $trainer->date_of_birth?->format('Y-m-d'),
            'email' => $trainer->email,
            'phone' => $trainer->phone,
            'instagram' => $trainer->instagram,
            'linkedin' => $trainer->linkedin,
            'status' => $trainer->status,
            'sort_order' => $trainer->sort_order,
            'seo_title' => $this->map($trainer, 'seo_title'),
            'seo_description' => $this->map($trainer, 'seo_description'),
            'work' => $trainer->workEntries->map(fn ($entry) => [
                'id' => $entry->id,
                'organization' => $entry->organization,
                'logo_path' => $entry->logo_path,
                'logo_url' => $entry::mediaUrl($entry->logo_path),
                'title' => $this->map($entry, 'title'),
                'started_on' => $entry->started_on?->format('Y-m-d'),
                'ended_on' => $entry->ended_on?->format('Y-m-d'),
                'notes' => $this->map($entry, 'notes'),
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
            'role' => Locales::blankMap(''),
            'bio' => Locales::blankMap(''),
            'photo_path' => null, 'photo_url' => null,
            'cover_path' => null, 'cover_url' => null,
            'nationality' => null,
            'date_of_birth' => null,
            'email' => null, 'phone' => null, 'instagram' => null, 'linkedin' => null,
            'status' => 'draft',
            'sort_order' => 0,
            'seo_title' => Locales::blankMap(''),
            'seo_description' => Locales::blankMap(''),
            'work' => [],
        ];
    }
}
