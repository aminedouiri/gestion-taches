<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\Models\Role;
use Illuminate\Support\Facades\Hash;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;

class AuthenticationFeatureTest extends TestCase
{
    use RefreshDatabase;

    public function test_register()
    {
        Role::create(['name' => 'Utilisateur']);
        $response = $this->postJson('/api/register', [
            'name' => 'Mohamed Amine',
            'email' => 'mohamed@example.com',
            'password' => 'password',
            'password_confirmation' => 'password',
        ]);

        $response->assertStatus(201);
    }

    public function test_login()
    {
        $user = User::factory()->create([
            'email' => 'mohamed@example.com',
            'password' => Hash::make('password'),
        ]);

        $response = $this->postJson('/api/login', [
            'email' => 'mohamed@example.com',
            'password' => 'password',
        ]);

        $response->assertStatus(200);
    }

    public function test_logout()
    {
        $user = User::factory()->create();
        $token = $user->createToken('personal-token')->plainTextToken;

        $response = $this->withHeaders(['Authorization' => 'Bearer ' . $token])
            ->postJson('/api/logout');

        $response->assertStatus(200);
    }

    public function test_user()
    {
        $user = User::factory()->create();
        $token = $user->createToken('personal-token')->plainTextToken;

        $response = $this->withHeaders(['Authorization' => 'Bearer ' . $token])
            ->getJson('/api/user');

        $response->assertStatus(200);
    }
}
