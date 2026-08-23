<?php

namespace Tests\Feature;

use App\Models\Player;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class HomeClubsTest extends TestCase
{
    use RefreshDatabase;

    private function clubs(): array
    {
        return $this->get('/en')->viewData('page')['props']['clubs'];
    }

    private function stats(): array
    {
        return $this->get('/en')->viewData('page')['props']['stats'];
    }

    public function test_the_same_club_typed_in_different_cases_appears_once(): void
    {
        foreach (['FC Dinamo Tbilisi', 'fc dinamo tbilisi', 'FC DINAMO TBILISI'] as $club) {
            Player::factory()->create(['status' => 'published', 'current_club' => $club]);
        }

        $this->assertSame(['FC DINAMO TBILISI'], $this->clubs());
    }

    public function test_stray_spacing_does_not_create_a_second_entry(): void
    {
        Player::factory()->create(['status' => 'published', 'current_club' => 'FC  Bayern']);
        Player::factory()->create(['status' => 'published', 'current_club' => '  FC Bayern ']);

        $this->assertSame(['FC BAYERN'], $this->clubs());
    }

    public function test_the_clubs_figure_matches_the_list_that_is_shown(): void
    {
        foreach (['Ajax', 'ajax', 'AJAX', 'Porto'] as $club) {
            Player::factory()->create(['status' => 'published', 'current_club' => $club]);
        }

        $clubs = $this->clubs();

        $this->assertSame(['AJAX', 'PORTO'], $clubs);
        $this->assertSame(count($clubs), $this->stats()['clubs']);
    }

    public function test_distinct_clubs_are_all_kept(): void
    {
        foreach (['Ajax', 'Porto', 'Benfica'] as $club) {
            Player::factory()->create(['status' => 'published', 'current_club' => $club]);
        }

        $this->assertSame(['AJAX', 'BENFICA', 'PORTO'], $this->clubs());
    }
}
