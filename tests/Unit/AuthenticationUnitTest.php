<?php

namespace Tests\Unit;

use App\Models\User;
use Tests\TestCase;
use Illuminate\Http\JsonResponse;
use Spatie\Permission\Models\Role;
use App\Http\Requests\LoginRequest;
use Illuminate\Support\Facades\Hash;
use App\Http\Requests\RegisterRequest;
use App\Http\Controllers\AuthController;
use Illuminate\Foundation\Testing\RefreshDatabase;

class AuthenticationUnitTest extends TestCase
{
    use RefreshDatabase;

    public function test_register()
    {
        Role::create(['name' => 'Utilisateur']);
        $request = new RegisterRequest([
            'name' => 'Mohamed Amine',
            'email' => 'mohamed@example.com',
            'password' => 'password',
            'password_confirmation' => 'password',
        ]);

        $controller = new AuthController();
        $response = $controller->register($request);

        $this->assertInstanceOf(JsonResponse::class, $response);
        $this->assertEquals(201, $response->getStatusCode());
    }

    public function test_login()
    {
        $user = User::factory()->create([
            'email' => 'mohamed@example.com',
            'password' => Hash::make('password'),
        ]);

        $request = new LoginRequest([
            'email' => 'mohamed@example.com',
            'password' => 'password',
        ]);

        $controller = new AuthController();
        $response = $controller->login($request);

        $this->assertInstanceOf(JsonResponse::class, $response);
    }

    public function test_logout()
    {
        $user = User::factory()->create();
        $user->createToken('personal-token');
        $this->actingAs($user);

        $controller = new AuthController();
        $response = $controller->logout();

        $this->assertInstanceOf(JsonResponse::class, $response);
    }

    public function test_user()
    {
        $user = User::factory()->create();
        $user->createToken('personal-token');
        $this->actingAs($user);

        $controller = new AuthController();
        $response = $controller->user();

        $this->assertInstanceOf(JsonResponse::class, $response);
    }
}
