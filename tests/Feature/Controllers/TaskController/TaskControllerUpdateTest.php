<?php

namespace Tests\Feature\Controllers\TaskController;

use App\Models\Project;
use App\Models\Task;
use App\Models\User;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class TaskControllerUpdateTest extends TestCase
{
    public function test_can_update_title(): void
    {
        $user = User::factory()->create();
        $task = Task::factory()->for($user, 'creator')->create();

        Sanctum::actingAs($user);
        $route = route('tasks.update', $task);

        $response = $this->putJson($route, [
            'title' => 'test title'
        ]);

        $response->assertOk();
        $this->assertEquals('test title', $task->refresh()->title);

    }

    public function test_cannot_update_as_project_member(): void
    {
        $user = User::factory()->create();
        // The creator of this project is not the user created above
        $project = Project::factory()->create();
        $project->members()->attach([$user->id]);
        $task = Task::factory()->for($project->creator, 'creator')->for($project)->create();

        Sanctum::actingAs($user);
        $route = route('tasks.update', $task);

        $response = $this->putJson($route, [
            'title' => 'test title'
        ]);
        $response->assertForbidden();
    }

    public function test_unauthenticated_response(): void
    {
        $task = Task::factory()->create();

        $route = route('tasks.update', $task);

        $response = $this->putJson($route, [
            'title' => 'test title'
        ]);
        $response->assertUnauthorized();
    }

    public function test_no_access_response(): void
    {
        $user = User::factory()->create();
        $task = Task::factory()->create();

        Sanctum::actingAs($user);
        $route = route('tasks.update', $task);

        $response = $this->putJson($route, [
            'title' => 'test title'
        ]);
        $response->assertNotFound();
    }
}