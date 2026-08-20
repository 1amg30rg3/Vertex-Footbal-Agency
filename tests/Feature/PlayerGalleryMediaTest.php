<?php

namespace Tests\Feature;

use App\Models\Player;
use App\Models\User;
use App\Support\MediaUploader;
use App\Support\PlayerMedia;
use App\Support\Presenters\PlayerPresenter;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class PlayerGalleryMediaTest extends TestCase
{
    use RefreshDatabase;

    private function admin(): User
    {
        return User::factory()->create(['role' => 'admin']);
    }

    /** A tiny but structurally valid MP4: a 'ftyp' box is what the guard checks. */
    private function fakeMp4(string $name = 'clip.mp4', int $kilobytes = 8): UploadedFile
    {
        $body = "\x00\x00\x00\x18ftypmp42".str_repeat("\x00", $kilobytes * 1024);

        $path = tempnam(sys_get_temp_dir(), 'mp4');
        file_put_contents($path, $body);

        return new UploadedFile($path, $name, 'video/mp4', null, true);
    }

    public function test_it_classifies_the_three_kinds_of_gallery_media(): void
    {
        $this->assertSame('image', PlayerMedia::kind('players/gallery/x.jpg'));
        $this->assertSame('video', PlayerMedia::kind('players/gallery/x.mp4'));
        $this->assertSame('embed', PlayerMedia::kind('https://youtu.be/dQw4w9WgXcQ'));

        $this->assertSame('image', PlayerMedia::kind('players/gallery/legacy'));
    }

    public function test_it_refuses_to_embed_an_arbitrary_host(): void
    {
        $this->assertNull(PlayerMedia::kind('https://evil.example/x?v=dQw4w9WgXcQ'));
        $this->assertNull(PlayerMedia::embedUrl('https://evil.example/x?v=dQw4w9WgXcQ'));
    }

    public function test_it_uploads_a_video_and_returns_its_path(): void
    {
        Storage::fake('public');

        $response = $this->actingAs($this->admin())
            ->post(route('admin.media.video'), ['file' => $this->fakeMp4()], ['Accept' => 'application/json']);

        $response->assertOk()->assertJsonStructure(['path', 'url']);

        $path = $response->json('path');

        $this->assertStringStartsWith('players/gallery/', $path);
        $this->assertStringEndsWith('.mp4', $path);
        Storage::disk('public')->assertExists($path);
    }

    public function test_it_rejects_a_non_video_upload(): void
    {
        Storage::fake('public');

        $this->actingAs($this->admin())
            ->post(route('admin.media.video'), [
                'file' => UploadedFile::fake()->create('notes.pdf', 10, 'application/pdf'),
            ], ['Accept' => 'application/json'])
            ->assertJsonValidationErrors('file');
    }

    public function test_video_upload_requires_an_admin(): void
    {
        $this->post(route('admin.media.video'), ['file' => $this->fakeMp4()], ['Accept' => 'application/json'])
            ->assertUnauthorized();
    }

    public function test_a_pasted_link_is_stored_untouched_and_presented_as_an_embed(): void
    {
        Storage::fake('public');

        $player = Player::factory()->create();

        $this->actingAs($this->admin())
            ->put(route('admin.players.update', $player), $this->payload($player, [
                ['path' => 'https://www.youtube.com/watch?v=dQw4w9WgXcQ'],
            ]))
            ->assertRedirect();

        $photo = $player->fresh()->photos->first();

        $this->assertSame('https://www.youtube.com/watch?v=dQw4w9WgXcQ', $photo->path);

        $presented = (new PlayerPresenter)->detail($player->fresh())['photos'][0];

        $this->assertSame('embed', $presented['kind']);
        $this->assertSame('https://www.youtube-nocookie.com/embed/dQw4w9WgXcQ', $presented['embed_url']);
        $this->assertSame('https://img.youtube.com/vi/dQw4w9WgXcQ/hqdefault.jpg', $presented['poster']);
    }

    public function test_it_rejects_a_link_that_cannot_be_played(): void
    {
        $player = Player::factory()->create();

        $this->actingAs($this->admin())
            ->put(route('admin.players.update', $player), $this->payload($player, [
                ['path' => 'https://evil.example/whatever'],
            ]))
            ->assertSessionHasErrors('photos.0.path');
    }

    public function test_switching_a_row_from_an_upload_to_a_link_deletes_the_old_file(): void
    {
        Storage::fake('public');

        $player = Player::factory()->create();
        $stored = MediaUploader::store($this->fakeMp4(), 'players/gallery', video: true);

        Storage::disk('public')->assertExists($stored);

        $photo = $player->photos()->create(['path' => $stored, 'sort_order' => 0]);

        $this->actingAs($this->admin())
            ->put(route('admin.players.update', $player), $this->payload($player, [
                ['id' => $photo->id, 'path' => 'https://youtu.be/dQw4w9WgXcQ'],
            ]))
            ->assertRedirect();

        Storage::disk('public')->assertMissing($stored);
    }

    /** @param list<array<string, mixed>> $photos */
    private function payload(Player $player, array $photos): array
    {
        return [
            'first_name' => $player->getTranslations('first_name'),
            'last_name' => $player->getTranslations('last_name'),
            'status' => $player->status,
            'photos' => array_map(fn (array $row) => $row + ['caption' => []], $photos),
        ];
    }
}
