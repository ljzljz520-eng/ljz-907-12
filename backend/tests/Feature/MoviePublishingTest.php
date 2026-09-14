<?php

namespace Tests\Feature;

use App\Models\Movie;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MoviePublishingTest extends TestCase
{
    use RefreshDatabase;

    private function makeMovie(array $attrs = []): Movie
    {
        return Movie::create(array_merge([
            'title' => '测试影片 ' . uniqid(),
            'year' => 2024,
            'is_published' => true,
        ], $attrs));
    }

    public function test_published_movie_is_publicly_accessible(): void
    {
        $movie = $this->makeMovie();

        $this->getJson("/api/movies/{$movie->id}")
            ->assertStatus(200)
            ->assertJsonPath('movie.id', $movie->id)
            ->assertJsonStructure(['movie', 'related']);
    }

    public function test_unpublished_movie_returns_404_for_public(): void
    {
        $movie = $this->makeMovie(['is_published' => false]);

        // 下架影片与不存在的影片表现一致，不暴露存在性
        $this->getJson("/api/movies/{$movie->id}")->assertStatus(404);
    }

    public function test_unpublished_movie_not_in_public_listing(): void
    {
        $online = $this->makeMovie(['title' => '上架片AAAA']);
        $offline = $this->makeMovie(['title' => '下架片BBBB', 'is_published' => false]);

        $data = $this->getJson('/api/movies')->assertStatus(200)->json('data');
        $ids = array_column($data, 'id');

        $this->assertContains($online->id, $ids);
        $this->assertNotContains($offline->id, $ids);
    }

    public function test_related_movies_never_include_unpublished(): void
    {
        $movie = $this->makeMovie(['genre' => '剧情']);
        $offline = $this->makeMovie(['genre' => '剧情', 'is_published' => false]);

        $related = $this->getJson("/api/movies/{$movie->id}")->json('related');
        $ids = array_column($related, 'id');

        $this->assertNotContains($offline->id, $ids);
    }
}
