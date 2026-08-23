<?php

namespace Tests\Feature;

use App\Models\News;
use App\Models\Player;
use App\Models\User;
use App\Support\Locales;
use Database\Seeders\SettingSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SeoTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(SettingSeeder::class);
    }

    public function test_a_public_page_ships_its_metadata_without_javascript(): void
    {
        $response = $this->get('/en/players');

        $response->assertOk();
        $response->assertSee('<link rel="canonical" href="'.url('/en/players').'"', false);
        $response->assertSee('name="robots" content="index, follow', false);
        $response->assertSee('property="og:title"', false);
        $response->assertSee('name="twitter:card"', false);
        $response->assertSee('"@type":"SportsOrganization"', false);
    }

    public function test_every_language_is_declared_with_hreflang(): void
    {
        $response = $this->get('/en/players');

        foreach (Locales::codes() as $code) {
            $response->assertSee('hreflang="'.$code.'" href="'.url("/$code/players").'"', false);
        }

        $response->assertSee('hreflang="x-default"', false);
    }

    public function test_a_player_page_carries_person_structured_data(): void
    {
        $player = Player::factory()->create(['status' => 'published']);

        $this->get("/en/players/{$player->slug}")
            ->assertOk()
            ->assertSee('"@type":"Person"', false)
            ->assertSee('"jobTitle":"Footballer"', false);
    }

    public function test_an_article_page_carries_news_article_structured_data(): void
    {
        $article = News::factory()->create(['status' => 'published', 'published_at' => now()->subDay()]);

        $this->get("/en/news/{$article->slug}")
            ->assertOk()
            ->assertSee('"@type":"NewsArticle"', false)
            ->assertSee('"datePublished"', false);
    }

    public function test_the_admin_area_is_kept_out_of_the_index(): void
    {
        $this->get('/admin/login')
            ->assertOk()
            ->assertSee('name="robots" content="noindex, nofollow"', false);

        $this->actingAs(User::factory()->create(['role' => 'admin']))
            ->get('/admin')
            ->assertOk()
            ->assertSee('name="robots" content="noindex, nofollow"', false);
    }

    public function test_the_sitemap_lists_content_with_alternates(): void
    {
        $player = Player::factory()->create(['status' => 'published']);

        $response = $this->get('/sitemap.xml');

        $response->assertOk();
        $response->assertHeader('Content-Type', 'application/xml');
        $response->assertSee(url('/ka'), false);
        $response->assertSee(url("/ka/players/{$player->slug}"), false);
        $response->assertSee('hreflang="x-default"', false);
    }

    public function test_robots_blocks_private_paths_and_points_at_the_sitemap(): void
    {
        $response = $this->get('/robots.txt');

        $response->assertOk();
        $response->assertSee('Disallow: /admin', false);
        $response->assertSee('Sitemap: '.url('/sitemap.xml'), false);
    }

    public function test_the_share_image_is_preferred_over_the_logo_for_previews(): void
    {
        \App\Models\Setting::put('share_image_path', 'site/share-card.png', 'branding');
        $this->get('/en/about')
            ->assertOk()
            ->assertSee('property="og:image" content="'.url('/storage/site/share-card.png').'"', false);
    }

    public function test_canonicals_ignore_filter_and_paging_query_strings(): void
    {
        // Otherwise every filter combination would register as its own page.
        $this->get('/en/players?page=2&position=defender')
            ->assertOk()
            ->assertSee('<link rel="canonical" href="'.url('/en/players').'"', false);
    }
}
