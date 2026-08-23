<?php

namespace Tests\Feature;

use App\Models\Player;
use App\Models\User;
use App\Support\Presenters\PlayerPresenter;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PitchPositionsTest extends TestCase
{
    use RefreshDatabase;

    private function save(Player $player, array $positions): void
    {
        $this->actingAs(User::factory()->create(['role' => 'admin']))
            ->put(route('admin.players.update', $player), [
                'first_name' => $player->getTranslations('first_name'),
                'last_name' => $player->getTranslations('last_name'),
                'status' => $player->status,
                'pitch_positions' => $positions,
            ])
            ->assertRedirect();
    }

    public function test_several_positions_are_stored_in_order(): void
    {
        $player = Player::factory()->create();

        $this->save($player, [
            ['x' => 20, 'y' => 80],
            ['x' => 50, 'y' => 40],
            ['x' => 78.5, 'y' => 15.2],
        ]);

        // Whole numbers come back from JSON as ints, so compare loosely.
        $this->assertEquals([
            ['x' => 20, 'y' => 80],
            ['x' => 50, 'y' => 40],
            ['x' => 78.5, 'y' => 15.2],
        ], $player->fresh()->pitch_positions);
    }

    public function test_positions_can_be_cleared(): void
    {
        $player = Player::factory()->create(['pitch_positions' => [['x' => 10, 'y' => 10]]]);

        $this->save($player, []);

        $this->assertSame([], $player->fresh()->pitch_positions);
    }

    public function test_a_position_outside_the_pitch_is_rejected(): void
    {
        $player = Player::factory()->create();

        $this->actingAs(User::factory()->create(['role' => 'admin']))
            ->put(route('admin.players.update', $player), [
                'first_name' => $player->getTranslations('first_name'),
                'last_name' => $player->getTranslations('last_name'),
                'status' => $player->status,
                'pitch_positions' => [['x' => 140, 'y' => 10]],
            ])
            ->assertSessionHasErrors('pitch_positions.0.x');
    }

    public function test_the_public_payload_exposes_every_position(): void
    {
        $player = Player::factory()->create([
            'status' => 'published',
            'pitch_positions' => [['x' => 30, 'y' => 60], ['x' => 70, 'y' => 25]],
        ]);

        $pitch = (new PlayerPresenter)->detail($player->fresh())['pitch'];

        $this->assertCount(2, $pitch);
        $this->assertSame(['x' => 70.0, 'y' => 25.0], $pitch[1]);
    }
}
