<?php

namespace Tests\Feature\Controllers\TaskController;

use App\Models\Project;
use App\Models\Task;
use App\Models\User;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class TaskControllerDestroyTest extends TestCase
{
    public function test_can_destroy_created_task(): void
    {
        $user = User::factory()->create();
        $task = Task::factory()->for($user, 'creator')->create();

        Sanctum::actingAs($user);
        $route = route('tasks.destroy', $task);
        
        $response = $this->deleteJson($route);
        $response->assertNoContent();
        $this->assertDatabaseMissing('tasks', $task->toArray());
    }

    public function test_can_not_destroy_as_project_member(): void
    {
        $user = User::factory()->create();
        // The creator of this project is not the user above
        $project = Project::factory()->create();
        $project->members()->attach([$user->id]);
        $task = Task::factory()->for($project->creator, 'creator')->for($project)->create();

        Sanctum::actingAs($user);
        $route = route('tasks.destroy', $task);

        $response = $this->deleteJson($route);
        $response->assertForbidden();
    }
}