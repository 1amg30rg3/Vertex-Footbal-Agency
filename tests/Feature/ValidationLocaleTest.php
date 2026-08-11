<?php

namespace Tests\Feature;

use App\Models\ContactMessage;
use App\Support\Locales;
use Database\Seeders\SettingSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ValidationLocaleTest extends TestCase
{
    use RefreshDatabase;

    public function test_contact_form_errors_are_localised(): void
    {
        $this->seed(SettingSeeder::class);

        foreach (Locales::codes() as $locale) {
            $response = $this->from("/{$locale}/contacts")
                ->post("/{$locale}/contacts", ['name' => '', 'email' => 'not-an-email', 'message' => 'short']);

            $response->assertSessionHasErrors(['name', 'email', 'message']);

            $errors = $response->baseResponse->getSession()->get('errors')->getBag('default')->all();

            $this->assertNotEmpty($errors, "no errors returned for [{$locale}]");

            foreach ($errors as $message) {
                $this->assertStringNotContainsString(
                    'validation.',
                    $message,
                    "untranslated validation key leaked in [{$locale}]: {$message}",
                );
            }
        }
    }

    public function test_the_honeypot_rejects_a_filled_submission(): void
    {
        $this->seed(SettingSeeder::class);

        $this->post('/ka/contacts', [
            'name' => 'Bot',
            'email' => 'bot@example.com',
            'message' => 'A perfectly plausible looking message body.',
            'website' => 'http://spam.example',
        ])->assertSessionHasErrors('website');

        $this->assertSame(0, ContactMessage::count());
    }
}
