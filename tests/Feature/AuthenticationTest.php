<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class AuthenticationTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    public function testLogin()
    {
        $email = $this->faker->email;
        $password = $this->faker->password;
        $name = $this->faker->name;

        $user = User::create([
            'name' => $name,
            'email' => $email,
            'password' => $password,
        ]);

        $user->markEmailAsVerified();

        $response = $this->postJson(
            '/api/v1/auth/login',
            [
                'email' => $email,
                'password' => $password,
            ]
        );

        $response->assertOk();
    }

    public function testRegister()
    {
        $email = $this->faker->email;
        $password = $this->faker->password;
        $name = $this->faker->name;

        $response = $this->postJson(
            '/api/v1/auth/register',
            [
                'name' => $name,
                'email' => $email,
                'password' => $password,
            ],
        );

        $response->assertOk();
    }
}
