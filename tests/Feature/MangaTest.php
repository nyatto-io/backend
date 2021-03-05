<?php

namespace Tests\Feature;

use App\Models\Manga;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class MangaTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    protected $url = '/api/v1/manga';

    protected function user()
    {
        return Sanctum::actingAs(User::factory()->create());
    }

    public function testFetchAllMangas()
    {
        $this->user();
        $response = $this->getJson($this->url);

        $response->assertOk();
    }

    public function testFetchOneManga()
    {
        $this->user();
        $manga = Manga::factory()->create();

        $response = $this->getJson("{$this->url}/{$manga->id}");

        $response->assertJson($manga->toArray());
    }

    public function testDeleteManga()
    {
        $this->user();
        $manga = Manga::factory()->create();

        $response = $this->deleteJson("{$this->url}/{$manga->id}");

        $response->assertNoContent();
    }

    public function testRefreshWebtoonsCache()
    {
        $response = $this->makeCommand('webtoons', 'refreshCache&pages=1');
        $response->assertNoContent();
    }

    public function testFetchAllWebtoons()
    {
        $response = $this->makeCommand('webtoons', 'fetchAll');

        $response->assertOk();
    }

    public function testSearchWebtoons()
    {
        $response = $this->makeCommand('webtoons', 'search&keyword=' . urlencode($this->faker->text(50)));
        $response->assertOk();
    }

    public function testViewWebtoon()
    {
        $manga = Manga::factory()->create([
            'group' => 'webtoon',
            'url' => 'https://www.webtoon.xyz/read/the-beginning-after-the-end/',
        ]);

        $response = $this->makeCommand('webtoons', 'view&id=' . $manga->id);

        $response->assertOk();
    }

    public function testFetchWebtoonGenres()
    {
        $response = $this->makeCommand('webtoons', 'genres');

        $response->assertOk();
    }

    protected function makeCommand($driver, $command)
    {
        $this->user();
        $url = "/api/v1/manga/{$driver}?type={$command}";

        return $this->getJson($url);
    }
}
