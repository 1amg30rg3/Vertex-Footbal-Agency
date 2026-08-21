<?php

namespace Tests\Feature;

use App\Models\News;
use App\Models\User;
use App\Support\Presenters\NewsPresenter;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class NewsExternalLinkTest extends TestCase
{
    use RefreshDatabase;

    private function payload(array $overrides = []): array
    {
        return array_merge([
            'title' => ['ka' => 'სათაური', 'en' => 'Title'],
            'status' => 'published',
        ], $overrides);
    }

    public function test_an_article_can_store_an_external_link(): void
    {
        $this->actingAs(User::factory()->create(['role' => 'admin']))
            ->post(route('admin.news.store'), $this->payload([
                'external_url' => 'https://www.bbc.com/sport/football/12345',
            ]))
            ->assertRedirect();

        $this->assertSame(
            'https://www.bbc.com/sport/football/12345',
            News::latest('id')->first()->external_url,
        );
    }

    public function test_an_article_without_a_link_stores_null_rather_than_an_empty_string(): void
    {
        $this->actingAs(User::factory()->create(['role' => 'admin']))
            ->post(route('admin.news.store'), $this->payload(['external_url' => '']))
            ->assertRedirect();

        $this->assertNull(News::latest('id')->first()->external_url);
    }

    public function test_it_rejects_a_link_that_is_not_a_web_url(): void
    {
        $this->actingAs(User::factory()->create(['role' => 'admin']))
            ->post(route('admin.news.store'), $this->payload(['external_url' => 'javascript:alert(1)']))
            ->assertSessionHasErrors('external_url');
    }

    public function test_the_public_payload_exposes_the_link(): void
    {
        $article = News::factory()->create(['external_url' => 'https://example.com/story']);

        $presented = (new NewsPresenter)->card($article->fresh());

        $this->assertSame('https://example.com/story', $presented['external_url']);
    }

    public function test_the_public_payload_reports_null_when_there_is_no_link(): void
    {
        $article = News::factory()->create(['external_url' => null]);

        $this->assertNull((new NewsPresenter)->card($article->fresh())['external_url']);
    }
}
