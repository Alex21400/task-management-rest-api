<?php

namespace Tests\Feature\Controllers\CommentController;

use App\Models\Project;
use App\Models\Task;
use App\Models\User;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class CommentControllerIndexTest extends TestCase
{
    public function test_authenticated_users_can_fetch_tasks_comments(): void
    {
        $user = User::factory()->create();
        $task = Task::factory()->for($user, 'creator')->create();
        $comment = $task->comments()->make([
            'content' => 'test content'
        ]);
        $comment->user()->associate($task->creator);
        $comment->save();

        Sanctum::actingAs($user);
        $route = route('tasks.comments.index', $task);
        
        $response = $this->getJson($route);
        $response->assertOk()
                ->assertJsonCount(1, 'data')
                ->assertJsonStructure([
                    'data' => [
                        '*' => [
                            'id',
                            'content',
                            'user',
                            'created_at'
                        ]
                    ]
                ]);
    }

    public function test_unauthenticated_users_can_not_fetch_tasks_comments(): void
    {
        $task = Task::factory()->create();
        $route = route('tasks.comments.index', $task);
        
        $response = $this->getJson($route);
        $response->assertUnauthorized();
    }

    public function test_authenticated_users_can_fetch_projects_comments(): void
    {
        $user = User::factory()->create();
        $project = Project::factory()->for($user, 'creator')->create();
        $comment = $project->comments()->make([
            'content' => 'test content'
        ]);
        $comment->user()->associate($project->creator);
        $comment->save();

        Sanctum::actingAs($user);
        $route = route('projects.comments.index', $project);

        $response = $this->getJson($route);
        $response->assertOk()
                ->assertJsonCount(1, 'data')
                ->assertJsonStructure([
                    'data' => [
                        '*' => [
                            'id',
                            'content',
                            'user',
                            'created_at'
                        ]
                    ]
                ]);
    }
}