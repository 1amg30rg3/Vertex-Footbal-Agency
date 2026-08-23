<?php

namespace Tests\Feature;

use App\Models\ContactMessage;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\RateLimiter;
use Tests\TestCase;

class ContactPhoneTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        RateLimiter::clear('contact:127.0.0.1');
    }

    private function payload(array $overrides = []): array
    {
        return array_merge([
            'name' => 'Visitor',
            'email' => 'visitor@example.com',
            'message' => 'I would like to discuss representation for a young player.',
        ], $overrides);
    }

    public function test_a_phone_number_with_a_country_code_is_stored(): void
    {
        $this->post(route('public.contacts.store', ['locale' => 'ka']), $this->payload([
            'phone' => '+995 555 12 34 56',
        ]))->assertRedirect();

        $this->assertSame('+995 555 12 34 56', ContactMessage::latest('id')->first()->phone);
    }

    public function test_the_phone_number_is_optional(): void
    {
        $this->post(route('public.contacts.store', ['locale' => 'ka']), $this->payload())
            ->assertRedirect()
            ->assertSessionHasNoErrors();

        $this->assertNull(ContactMessage::latest('id')->first()->phone);
    }

    public function test_it_rejects_a_phone_number_that_is_not_a_number(): void
    {
        $this->post(route('public.contacts.store', ['locale' => 'ka']), $this->payload([
            'phone' => 'call me maybe',
        ]))->assertSessionHasErrors('phone');

        $this->assertSame(0, ContactMessage::count());
    }
}
