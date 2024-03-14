<?php

namespace Tests\Feature\Controllers\ProjectController;

use App\Models\Project;
use App\Models\User;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class ProjectControllerShowTest extends TestCase
{
    public function test_authenticated_users_can_see_created_projects(): void
    {
        $user = User::factory()->create();
        $project = Project::factory()->for($user, 'creator')->create();

        Sanctum::actingAs($user);
        $route = route('projects.show', $project);

        $response = $this->getJson($route);
        $response->assertOk()
                ->assertJsonStructure([
                    'id',
                    'title',
                    'creator_id',
                    'created_at',
                    'updated_at',
                    'tasks',
                    'members'
                ]);
    }

    public function test_members_can_see_projects(): void
    {
        $user = User::factory()->create();
        $project = Project::factory()->create();
        $project->members()->attach([$user->id]);

        Sanctum::actingAs($user);
        $route = route('projects.show', $project);

        $response = $this->getJson($route);
        $response->assertOk()
                ->assertJsonStructure([
                    'id',
                    'title',
                    'creator_id',
                    'created_at',
                    'updated_at',
                    'tasks',
                    'members'
                ]);
    }

    public function test_unauthenticated_response(): void
    {
        $project = Project::factory()->create();

        $route = route('projects.show', $project);

        $response = $this->getJson($route);
        $response->assertUnauthorized();
    }

    public function test_no_access_response(): void
    {
        $user = User::factory()->create();
        $project = Project::factory()->create();

        Sanctum::actingAs($user);
        $route = route('projects.show', $project);

        $response = $this->getJson($route);
        $response->assertNotFound();
    }
}