<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\Task;
use App\Models\User;
use Spatie\Permission\Models\Role;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;

class TaskFeatureTest extends TestCase
{
    use RefreshDatabase;
    public function test_store_method_creates_task()
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $taskData = Task::factory()->create()->toArray();

        $response = $this->postJson(route('tasks.store'), $taskData);

        $response->assertStatus(201);
    }

    public function test_index_method_returns_task_list()
    {
        $user = User::factory()->create();
        $this->actingAs($user);
        $token = $user->createToken('personal-token')->plainTextToken;

        $response = $this->withHeaders(['Authorization' => 'Bearer ' . $token])
            ->getJson('/api/tasks');

        $response->assertStatus(200)
        ->assertJsonStructure([
            'data' => [
                'tasks' => [
                    '*' => [
                        'id',
                        'titre',
                        'description',
                        'statut',
                        'date d\'échéance',
                        'user' => [
                            'id',
                            'name',
                            'email',
                            'email_verified_at',
                            'created_at',
                            'updated_at',
                        ],
                    ],
                ],
                'current_page',
                'total_pages',
            ],
            'message',
        ]);
    }
    public function test_show_method_returns_task_by_id()
    {
        $user = User::factory()->create();
        $this->actingAs($user);
        $token = $user->createToken('personal-token')->plainTextToken;
        $task = Task::factory()->create();

        $response = $this->withHeaders(['Authorization' => 'Bearer ' . $token])
                ->getJson(route('tasks.show', $task->id));

        $response->assertStatus(200)
        ->assertJsonStructure([
            'message',
            'data' => [
                'id',
                'titre',
                'description',
                'statut',
                'date d\'échéance',
                'user',
                'created_at',
                'updated_at',
                'deleted_at',
            ],
        ]);
    }

    public function test_update_method_updates_task()
    {
        $user = User::factory()->create();
        $this->actingAs($user);
        $token = $user->createToken('personal-token')->plainTextToken;
        $task = Task::factory()->create();

        $response = $this->withHeaders(['Authorization' => 'Bearer ' . $token])
                ->getJson(route('tasks.update', $task->id));

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    'id',
                    'titre',
                    'description',
                    'statut',
                    'date d\'échéance',
                    'user',
                ]
            ]);
    }

    public function test_destroy_method_deletes_task()
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $token = $user->createToken('personal-token')->plainTextToken;
        $task = Task::factory()->create();

        $response = $this->withHeaders(['Authorization' => 'Bearer ' . $token])
                        ->deleteJson(route('tasks.destroy', $task->id));

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    'id',
                    'titre',
                    'description',
                    'date d\'échéance',
                    'statut',
                ]
            ]);
    }

    public function test_deleted_method_returns_deleted_task_list()
    {
        $user = User::factory()->create();
        $this->actingAs($user);
        $token = $user->createToken('personal-token')->plainTextToken;

        $tasks = Task::factory()->count(3)->create();
        Task::find($tasks->first()->id)->delete();
        Role::create(['name' => 'Administrateur']);
        $user->assignRole('Administrateur');

        $response = $this->withHeaders(['Authorization' => 'Bearer ' . $token])
                        ->getJson('/api/tasks/deleted');

        $response->assertStatus(200)
        ->assertJsonStructure([
            'data' => [
                'tasks' => [
                    '*' => [
                        'id',
                        'titre',
                        'description',
                        'statut',
                        'date d\'échéance',
                        'user' => [
                            'id',
                            'name',
                            'email',
                            'email_verified_at',
                            'created_at',
                            'updated_at',
                        ],
                    ],
                ],
                'current_page',
                'total_pages',
            ],
            'message',
        ]);
    }
}
