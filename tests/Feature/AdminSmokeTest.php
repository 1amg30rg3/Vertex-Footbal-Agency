<?php

namespace Tests\Feature;

use App\Models\ContactMessage;
use App\Models\News;
use App\Models\Player;
use App\Models\TeamMember;
use App\Models\Trainer;
use App\Models\User;
use App\Support\Locales;
use Database\Seeders\DemoContentSeeder;
use Database\Seeders\SettingSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminSmokeTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::factory()->create(['role' => 'admin']);

        $this->seed(SettingSeeder::class);
        $this->seed(DemoContentSeeder::class);
    }

    public function test_admin_area_requires_authentication(): void
    {
        $this->get('/admin')->assertRedirect(route('admin.login'));
        $this->get('/admin/players')->assertRedirect(route('admin.login'));
    }

    public function test_admin_pages_render(): void
    {
        $player = Player::first();
        $trainer = Trainer::first();
        $member = TeamMember::first();
        $article = News::first();

        $routes = [
            route('admin.dashboard'),
            route('admin.players.index'),
            route('admin.players.create'),
            route('admin.players.edit', $player),
            route('admin.trainers.index'),
            route('admin.trainers.create'),
            route('admin.trainers.edit', $trainer),
            route('admin.team.members.index'),
            route('admin.team.members.create'),
            route('admin.team.members.edit', $member),
            route('admin.news.index'),
            route('admin.news.create'),
            route('admin.news.edit', $article),
            route('admin.news.categories.index'),
            route('admin.messages.index'),
            route('admin.settings.edit'),
        ];

        foreach ($routes as $url) {
            $this->actingAs($this->admin)->get($url)->assertOk();
        }
    }

    public function test_a_player_can_be_created_with_nested_blocks(): void
    {
        $payload = [
            'first_name' => $this->map('Test'),
            'last_name' => $this->map('Player'),
            'status' => 'published',
            'position' => 'forward',
            'preferred_foot' => 'left',
            'height_cm' => 180,
            'weight_kg' => 75,
            'pitch_x' => 40.5,
            'pitch_y' => 20.0,
            'skills' => [
                ['label' => $this->map('Finishing'), 'value' => 90],
                ['label' => $this->map('Pace'), 'value' => 84],
            ],
            'career' => [
                ['club_name' => 'Test FC', 'started_on' => '2022-07-01', 'ended_on' => null, 'category' => 'Senior'],
            ],
            'achievements' => [
                ['text' => $this->map('Top scorer'), 'year' => '2024'],
            ],
            'seasons' => [
                [
                    'label' => '2024/2025',
                    'matches_played' => 20,
                    'goals' => 11,
                    'assists' => 4,
                    'minutes_played' => 1500,
                    'starting_pct' => 70,
                    'substitute_pct' => 20,
                    'not_in_squad_pct' => 10,
                    'is_current' => true,
                    'months' => [
                        ['month' => 8, 'goals' => 2, 'assists' => 1],
                        ['month' => 9, 'goals' => 1, 'assists' => 0],
                    ],
                ],
            ],
        ];

        $this->actingAs($this->admin)
            ->post(route('admin.players.store'), $payload)
            ->assertRedirect();

        $player = Player::where('slug', 'test-player')->firstOrFail();

        $this->assertSame(2, $player->skills()->count());
        $this->assertSame(1, $player->careerEntries()->count());
        $this->assertSame(1, $player->achievements()->count());
        $this->assertSame(1, $player->seasons()->count());
        $this->assertSame(2, $player->seasons()->first()->months()->count());
        $this->assertSame('Finishing', $player->skills()->first()->getTranslation('label', 'en'));
    }

    public function test_updating_a_player_reconciles_repeaters(): void
    {
        $player = Player::where('slug', 'maximilian-becker')->firstOrFail();
        $keptSkill = $player->skills()->first();

        $this->actingAs($this->admin)
            ->put(route('admin.players.update', $player), [
                'first_name' => $this->map('Maximilian'),
                'last_name' => $this->map('Becker'),
                'status' => 'published',
                'skills' => [
                    ['id' => $keptSkill->id, 'label' => $this->map('Renamed'), 'value' => 99],
                ],
                'career' => [],
                'achievements' => [],
                'seasons' => [],
                'photos' => [],
            ])
            ->assertRedirect();

        $player->refresh();

        $this->assertSame(1, $player->skills()->count());
        $this->assertSame(99, $player->skills()->first()->value);
        $this->assertSame('Renamed', $player->skills()->first()->getTranslation('label', 'en'));
        $this->assertSame(0, $player->careerEntries()->count());
        $this->assertSame(0, $player->seasons()->count());
    }

    public function test_rich_text_is_sanitised_on_save(): void
    {
        $this->actingAs($this->admin)
            ->post(route('admin.news.store'), [
                'title' => $this->map('XSS check'),
                'body' => $this->map('<p>Safe</p><script>alert(1)</script><a href="javascript:alert(2)">bad</a>'),
                'status' => 'draft',
            ])
            ->assertRedirect();

        $body = News::where('slug', 'xss-check')->firstOrFail()->getTranslation('body', 'en');

        $this->assertStringNotContainsString('<script', $body);
        $this->assertStringNotContainsString('javascript:', $body);
        $this->assertStringContainsString('Safe', $body);
    }

    public function test_featured_toggle_and_reorder(): void
    {
        $article = News::first();
        $wasFeatured = $article->is_featured;

        $this->actingAs($this->admin)
            ->patch(route('admin.news.featured', $article))
            ->assertRedirect();

        $this->assertSame(! $wasFeatured, $article->fresh()->is_featured);

        $ids = Player::orderBy('id')->pluck('id')->reverse()->values()->all();

        $this->actingAs($this->admin)
            ->post(route('admin.players.reorder'), ['ids' => $ids])
            ->assertRedirect();

        $this->assertSame(0, Player::whereKey($ids[0])->value('sort_order'));
    }

    public function test_editors_cannot_manage_accounts(): void
    {
        $editor = User::factory()->create(['role' => 'editor']);

        $this->actingAs($editor)->get(route('admin.settings.edit'))->assertOk();

        $this->actingAs($editor)
            ->post(route('admin.settings.users.store'), [
                'name' => 'Nope',
                'email' => 'nope@example.com',
                'password' => 'Password123!',
                'password_confirmation' => 'Password123!',
                'role' => 'admin',
            ])
            ->assertForbidden();
    }

    public function test_theme_preference_is_persisted(): void
    {
        $this->actingAs($this->admin)
            ->patch(route('admin.preferences.theme'), ['theme' => 'light'])
            ->assertRedirect();

        $this->assertSame('light', $this->admin->fresh()->theme);
    }

    public function test_contact_form_stores_a_message_and_blocks_the_honeypot(): void
    {
        $this->post(route('public.contacts.store', ['locale' => 'ka']), [
            'name' => 'Visitor',
            'email' => 'visitor@example.com',
            'subject' => 'Hello',
            'message' => 'I would like to talk about representation.',
        ])->assertRedirect();

        $this->assertSame(1, ContactMessage::count());
        $this->assertSame('ka', ContactMessage::first()->locale);

        $this->post(route('public.contacts.store', ['locale' => 'ka']), [
            'name' => 'Bot',
            'email' => 'bot@example.com',
            'message' => 'Buy cheap things right now please.',
            'website' => 'http://spam.example',
        ])->assertSessionHasErrors('website');

        $this->assertSame(1, ContactMessage::count());
    }

    /** @return array<string, string> */
    protected function map(string $value): array
    {
        return array_fill_keys(Locales::codes(), $value);
    }
}
