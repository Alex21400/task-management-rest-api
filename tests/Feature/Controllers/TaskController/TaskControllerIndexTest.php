<?php

namespace Tests\Feature\Controllers\TaskController;

use App\Models\Task;
use App\Models\User;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class TaskControllerIndexTest extends TestCase
{
    public function test_authenticated_users_can_fetch_the_tasks_list(): void
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        Task::factory()->for($user, 'creator')->create();

        $route = route('tasks.index');

        $response = $this->getJson($route);
        $response->assertOk()
                ->assertJsonCount(1, 'data')
                ->assertJsonStructure([
                    'data' => [
                        '*' => [
                            'id',
                            'title',
                            'creator_id',
                            'is_done',
                            'created_at',
                            'status'
                        ]
                    ]
                ]);
    }

    /**
     * @dataProvider sortableFields
     */
    public function test_sortable_fields($field, $statusCode): void
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        $route = route('tasks.index', [
            'sort' => $field
        ]);

        $response = $this->getJson($route);
        $response->assertStatus($statusCode);
    }

    public function sortableFields(): array
    {
        return [
            ['title', 200],
            ['is_done', 200],
            ['created_at', 200],
            ['updated_at', 400]
        ];
    }

    /**
     * @dataProvider filterFields
     */
    public function test_filterable_fields($field, $value, $statusCode): void
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        $route = route('tasks.index', [
            "filter[{$field}]" => $value
        ]);

        $response = $this->getJson($route);
        $response->assertStatus($statusCode);
    }

    public function filterFields(): array
    {
        // Only is_done filter is allowed
        return [
            ['id', 1, 400],
            ['title', 'foo', 400],
            ['is_done', 1, 200]
        ];
    }

    public function test_unauthenticated_users_can_not_fetch_the_tasks_list(): void
    {
        $route = route('tasks.index');
        $response = $this->getJson($route);

        $response->assertUnauthorized();
    }
}