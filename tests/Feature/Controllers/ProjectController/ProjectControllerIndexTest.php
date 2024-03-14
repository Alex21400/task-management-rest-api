<?php

namespace Tests\Feature\Controllers\ProjectController;

use App\Models\Project;
use App\Models\User;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class ProjectControllerIndexTest extends TestCase
{
    public function test_authenticated_users_can_fetch_the_projects_list(): void
    {
        $user = User::factory()->create();
        $project = Project::factory()->for($user, 'creator')->create();

        Sanctum::actingAs($user);
        $route = route('projects.index');

        $response = $this->getJson($route);
        $response->assertOk()
                ->assertJsonCount(1, 'data')
                ->assertJsonStructure([
                    'data' => [
                        '*' => [
                            'id',
                            'title',
                            'created_at',
                            'updated_at'
                        ]
                    ]
                ]);
    }

    public function test_unauthenticated_users_can_not_fetch_the_projects_list(): void
    {
        $route = route('projects.index');

        $response = $this->getJson($route);
        $response->assertUnauthorized();
    }
}