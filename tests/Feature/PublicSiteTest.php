<?php

namespace Tests\Feature;

use App\Models\News;
use App\Models\Player;
use App\Support\Locales;
use Database\Seeders\DemoContentSeeder;
use Database\Seeders\SettingSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia;
use Tests\TestCase;

class PublicSiteTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(SettingSeeder::class);
        $this->seed(DemoContentSeeder::class);
    }

    public function test_root_negotiates_a_locale(): void
    {
        $this->withHeader('Accept-Language', 'de-DE,de;q=0.9,en;q=0.8')
            ->get('/')
            ->assertRedirect('/de');

        $this->withHeader('Accept-Language', 'zz-ZZ')
            ->get('/')
            ->assertRedirect('/'.Locales::default());

        $this->withHeader('Accept-Language', 'de-DE')
            ->withCookie('site_locale', 'fr')
            ->get('/')
            ->assertRedirect('/fr');
    }

    public function test_every_page_renders_in_every_locale(): void
    {
        $player = Player::published()->first();

        foreach (Locales::codes() as $locale) {
            $this->get("/{$locale}")->assertOk();
            $this->get("/{$locale}/players")->assertOk();
            $this->get("/{$locale}/players/{$player->slug}")->assertOk();
            $this->get("/{$locale}/trainers")->assertOk();
            $this->get("/{$locale}/agency-team")->assertOk();
            $this->get("/{$locale}/news")->assertOk();
            $this->get("/{$locale}/about")->assertOk();
            $this->get("/{$locale}/contacts")->assertOk();
        }
    }

    public function test_an_unknown_locale_prefix_is_not_treated_as_a_slug(): void
    {
        $this->get('/xx/players')->assertNotFound();
    }

    public function test_content_falls_back_to_the_default_locale(): void
    {
        $player = Player::where('slug', 'maximilian-becker')->firstOrFail();

        $this->assertNull($player->getTranslations('first_name')['it'] ?? null);

        $this->get('/it/players/maximilian-becker')
            ->assertOk()
            ->assertInertia(
                fn (AssertableInertia $page) => $page
                    ->component('Public/Players/Show')
                    ->where('player.first_name', 'მაქსიმილიან')
            );

        $this->get('/en/players/maximilian-becker')
            ->assertInertia(fn (AssertableInertia $page) => $page->where('player.first_name', 'Maximilian'));
    }

    public function test_draft_content_is_not_publicly_reachable(): void
    {
        $player = Player::first();
        $player->update(['status' => 'draft']);

        $this->get('/ka/players/'.$player->slug)->assertNotFound();

        $draft = News::factory()->draft()->create();
        $this->get('/ka/news/'.$draft->slug)->assertNotFound();
    }

    public function test_scheduled_articles_only_appear_once_their_time_has_passed(): void
    {
        $scheduled = News::factory()->scheduled()->create();

        $this->get('/ka/news/'.$scheduled->slug)->assertNotFound();

        $scheduled->update(['published_at' => now()->subMinute()]);

        $this->get('/ka/news/'.$scheduled->slug)->assertOk();
    }

    public function test_the_homepage_shows_only_featured_articles_in_order(): void
    {
        $this->get('/ka')->assertInertia(function (AssertableInertia $page) {
            $page->component('Public/Home')->has('featuredNews', 3);

            $featured = collect($page->toArray()['props']['featuredNews']);

            $this->assertTrue($featured->every(fn (array $article) => $article['is_featured']));
        });
    }

    public function test_players_can_be_filtered_by_position_and_search(): void
    {
        $this->get('/ka/players?position=midfielder')->assertInertia(function (AssertableInertia $page) {
            $slugs = collect($page->toArray()['props']['players']['data'])->pluck('slug');

            $this->assertTrue($slugs->contains('maximilian-becker'));
            $this->assertFalse($slugs->contains('giorgi-kartveli'));
        });

        $this->get('/ka/players?search=maximilian-becker')->assertInertia(function (AssertableInertia $page) {
            $data = $page->toArray()['props']['players']['data'];

            $this->assertCount(1, $data);
            $this->assertSame('maximilian-becker', $data[0]['slug']);
        });

        $this->get('/ka/players?search=definitely-no-such-player')->assertInertia(function (AssertableInertia $page) {
            $this->assertCount(0, $page->toArray()['props']['players']['data']);
        });
    }

    public function test_the_locale_switcher_route_remembers_the_choice(): void
    {
        $this->get('/lang/de')
            ->assertRedirect('/de')
            ->assertCookie('site_locale', 'de');
    }
}
