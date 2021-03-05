<?php

namespace Tests\Feature;

use App\Models\Anime;
use App\Models\Favorite;
use App\Models\Manga;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Str;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class FavoritesTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    protected $url = '/api/v1/favorites';

    protected function user()
    {
        return Sanctum::actingAs(User::factory()->create());
    }

    public function testFetchFavorites()
    {
        $this->user();

        $response = $this->getJson($this->url);

        $response->assertOk();
    }

    public function testCreateFavoriteAnime()
    {
        $response = $this->createFavorable(Anime::class);

        $response->assertCreated();
    }

    public function testCreateFavoriteManga()
    {
        $response = $this->createFavorable(Manga::class);

        $response->assertCreated();
    }

    public function testShowFavorite()
    {
        $user = $this->user();
        $favorite = Favorite::factory()->create(['user_id' => $user->id]);

        $response = $this->getJson("{$this->url}/{$favorite->id}");

        $response->assertOk();
    }

    public function testDeleteFavorite()
    {
        $user = $this->user();
        $favorite = Favorite::factory()->create(['user_id' => $user->id]);

        $response = $this->deleteJson("{$this->url}/{$favorite->id}");

        $response->assertNoContent();
    }

    protected function createFavorable($favorable)
    {
        $this->user();

        $favorable = $favorable::factory()->create();

        $fragments = explode('\\', get_class($favorable));

        $type = Str::lower($fragments[count($fragments) - 1]);

        $data = [
            'favorable_type' => $type,
            'favorable_id' => $favorable->id,
        ];

        return $this->postJson($this->url, $data);
    }
}
