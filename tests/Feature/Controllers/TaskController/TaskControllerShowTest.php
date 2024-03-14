<?php

namespace Tests\Feature\Controllers\TaskController;

use App\Models\Task;
use App\Models\User;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class TaskControllerShowTest extends TestCase
{
    public function test_authenticated_user_can_see_created_tasks(): void
    {
        $user = User::factory()->create();
        $task = Task::factory()->for($user, 'creator')->create();

        Sanctum::actingAs($user);
        $route = route('tasks.show', $task);

        $response = $this->getJson($route);
        $response->assertOk()
                ->assertJson([
                    'data' => [
                        'id' => $task->id,
                        'title' => $task->title,
                        'creator_id' => $user->id,
                        'is_done' => $task->is_done,
                        'created_at' => $task->created_at->jsonSerialize(),
                        'project_id' => null,
                        'status' => 'in_progress'
                    ]
                ]);
    }

    public function test_unauthenticated_response(): void
    {
        $task = Task::factory()->create();

        $route = route('tasks.show', $task);

        $response = $this->getJson($route);
        $response->assertUnauthorized();
    }

    public function test_no_access_response(): void
    {
        $user = User::factory()->create();
        $task = Task::factory()->create();

        Sanctum::actingAs($user);
        $route = route('tasks.show', $task);

        $response = $this->getJson($route);
        $response->assertNotFound();
    }
}