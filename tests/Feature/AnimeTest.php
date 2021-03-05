<?php

namespace Tests\Feature;

use App\Models\Anime;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class AnimeTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    protected $url = '/api/v1/anime';

    protected function user()
    {
        return Sanctum::actingAs(User::factory()->create());
    }

    public function testFetchAllAnimes()
    {
        $this->user();
        $response = $this->getJson($this->url);

        $response->assertOk();
    }

    public function testFetchOneAnime()
    {
        $this->user();
        $anime = Anime::factory()->create();

        $response = $this->getJson("{$this->url}/{$anime->id}");

        $response->assertJson($anime->toArray());
    }

    public function testDeleteAnime()
    {
        $this->user();
        $anime = Anime::factory()->create();

        $response = $this->deleteJson("{$this->url}/{$anime->id}");

        $response->assertNoContent();
    }

    public function testRefreshGogoanimesCache()
    {
        $response = $this->makeCommand('gogoanime', 'refreshCache&pages=1');

        $response->assertNoContent();
    }

    public function testFetchAllGogoanimes()
    {
        $response = $this->makeCommand('gogoanime', 'fetchAll');

        $response->assertOk();
    }

    public function testSearchGogoanimes()
    {
        $response = $this->makeCommand('gogoanime', 'search&keyword=' . urlencode($this->faker->text(50)));
        $response->assertOk();
    }

    public function testViewGogoanime()
    {
        $anime = Anime::factory()->create([
            'group' => 'gogoanime',
            'url' => 'https://www2.gogoanime.sh/category/horimiya',
            'title' => 'Horimiya',
        ]);

        $response = $this->makeCommand('gogoanime', 'view&id=' . $anime->id);

        $response->assertOk();
    }

    public function testFetchGogoanimeGenres()
    {
        $response = $this->makeCommand('gogoanime', 'genres');

        $response->assertOk();
    }

    protected function makeCommand($driver, $command)
    {
        $this->user();
        $url = "/api/v1/anime/{$driver}?type={$command}";

        return $this->getJson($url);
    }
}
