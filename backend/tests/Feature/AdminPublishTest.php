<?php

namespace Tests\Feature;

use App\Models\Movie;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class AdminPublishTest extends TestCase
{
    use RefreshDatabase;

    private function makeMovie(array $attrs = []): Movie
    {
        return Movie::create(array_merge([
            'title' => '后台测试影片 ' . uniqid(),
            'year' => 2024,
            'is_published' => true,
        ], $attrs));
    }

    public function test_guest_cannot_toggle_publish(): void
    {
        $movie = $this->makeMovie();

        $this->patchJson("/api/admin/movies/{$movie->id}/publish", ['is_published' => false])
            ->assertStatus(401);
    }

    public function test_admin_can_unpublish_and_publish(): void
    {
        Sanctum::actingAs(User::factory()->create());
        $movie = $this->makeMovie();

        $this->patchJson("/api/admin/movies/{$movie->id}/publish", ['is_published' => false])
            ->assertStatus(200)
            ->assertJsonPath('is_published', false);

        $this->assertDatabaseHas('movies', ['id' => $movie->id, 'is_published' => false]);
        $this->getJson("/api/movies/{$movie->id}")->assertStatus(404);

        $this->patchJson("/api/admin/movies/{$movie->id}/publish", ['is_published' => true])
            ->assertStatus(200)
            ->assertJsonPath('is_published', true);

        $this->getJson("/api/movies/{$movie->id}")->assertStatus(200);
    }

    public function test_admin_can_see_unpublished_in_listing(): void
    {
        Sanctum::actingAs(User::factory()->make());
        $offline = $this->makeMovie(['is_published' => false]);

        $data = $this->getJson('/api/admin/movies')->assertStatus(200)->json('data');
        $this->assertContains($offline->id, array_column($data, 'id'));
    }
}
