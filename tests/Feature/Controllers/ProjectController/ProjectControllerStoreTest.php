<?php

namespace Tests\Feature\Controllers\ProjectController;

use App\Models\Project;
use App\Models\User;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class ProjectControllerStoreTest extends TestCase
{
    public function test_can_create_project(): void
    {
        $user = User::factory()->create();

        Sanctum::actingAs($user);
        $route = route('projects.store');

        $response = $this->postJson($route, [
            'title' => 'test title'
        ]);
        $response->assertCreated();
        $this->assertDatabaseHas('projects', [
            'title' => 'test title',
            'creator_id' => $user->id
        ]);
    }

    public function test_unauthenticated_users_can_not_create_projects(): void 
    {
        $route = route('projects.store');

        $response = $this->postJson($route, [
            'title' => 'test title'
        ]);
        $response->assertUnauthorized();
    }

    public function test_title_field_is_required(): void
    {
        $user = User::factory()->create();

        Sanctum::actingAs($user);
        $route = route('projects.store');

        $response = $this->postJson($route, []);
        $response->assertJsonValidationErrors([
            'title' => 'required'
        ]);
    }
}