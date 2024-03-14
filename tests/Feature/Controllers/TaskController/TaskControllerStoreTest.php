<?php

namespace Tests\Feature\Controllers\TaskController;

use App\Models\Project;
use App\Models\Task;
use App\Models\User;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class TaskControllerStoreTest extends TestCase
{
    public function test_can_create_task(): void
    {
        $user = User::factory()->create();

        Sanctum::actingAs($user);
        $route = route('tasks.store');

        $response = $this->postJson($route, [
            'title' => 'test title'
        ]);

        $response->assertCreated();
        $this->assertDatabaseHas('tasks', [
            'title' => 'test title',
            'creator_id' => $user->id
        ]);
    }

    public function test_title_field_is_required(): void
    {
        $user = User::factory()->create();

        Sanctum::actingAs($user);
        $route = route('tasks.store');

        // Send an empty array without title
        $response = $this->postJson($route, []);
        $response->assertJsonValidationErrors([
            'title' => 'required'
        ]);
    }

    public function test_task_project_id_is_valid(): void
    {
        $project = Project::factory()->create();
        $user = User::factory()->create();
        
        Sanctum::actingAs($user);

        $route = route('tasks.store');

        $response = $this->postJson($route, [
            'title' => 'test title',
            'project_id' => $project->id
        ]);
        $response->assertJsonValidationErrors([
            'project_id' => 'in'
        ]);
    }
}