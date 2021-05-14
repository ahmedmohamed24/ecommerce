<?php

namespace Tests\Feature\User\Auth;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

/**
 * @internal
 * @coversNothing
 */
class SocialiteTest extends TestCase
{
    use WithFaker;
    use RefreshDatabase;

    // @test
    public function testCanLoginWithGithub()
    {
        $this->withoutExceptionHandling();
        $this->getJson('/auth/github/login')
            ->assertRedirect()
        ;
    }

    // @test
    public function testCanLoginWithFacebook()
    {
        $this->withoutExceptionHandling();
        $this->getJson('/auth/facebook/login')->assertRedirect();
    }
}