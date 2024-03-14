<?php

namespace Test\Feature\Controllers\ProjectController;

use App\Models\Project;
use App\Models\User;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class ProjectControllerDestroyTest extends TestCase
{
    public function test_can_destroy_created_project(): void
    {
        $user = User::factory()->create();
        $project = Project::factory()->for($user, 'creator')->create();

        Sanctum::actingAs($user);
        $route = route('projects.destroy', $project);

        $response = $this->deleteJson($route);
        $response->assertNoContent();
        $this->assertDatabaseMissing('projects', $project->toArray());
    }

    public function test_can_not_destroy_as_project_member(): void
    {
        $user = User::factory()->create();
        $project = Project::factory()->create();
        $project->members()->attach([$user->id]);

        Sanctum::actingAs($user);
        $route = route('projects.destroy', $project);

        $response = $this->deleteJson($route);
        $response->assertForbidden();
    }
}