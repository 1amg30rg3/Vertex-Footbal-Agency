<?php

namespace Tests\Feature;

use App\Support\Locales;
use Tests\TestCase;

class ExampleTest extends TestCase
{
    public function test_the_application_redirects_the_root_url_to_a_locale(): void
    {
        $response = $this->withHeader('Accept-Language', 'zz')->get('/');

        $response->assertRedirect('/'.Locales::default());
    }
}
