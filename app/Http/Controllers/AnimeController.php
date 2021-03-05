<?php

namespace App\Http\Controllers;

use App\Drivers\Anime\Gogoanime;
use App\Interfaces\AnimeDriver;
use App\Jobs\CacheAnime;
use App\Models\Anime;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class AnimeController extends Controller
{
    public function commandGogoanime(Request $request, Gogoanime $driver)
    {
        return $this->handleAnimeCommand($request, $driver);
    }

    protected function handleAnimeCommand(Request $request, AnimeDriver $driver)
    {
        $type = $request->input('type');

        switch ($type) {
            case 'refreshCache':
                $pages = (int)$request->input('pages', 5);
                dispatch(new CacheAnime($driver, $pages));
                return response('', 204);
            case 'fetchAll':
                return $driver->fetchAll(20);
            case 'search':
                return $driver->search($request->input('keyword', ''));
            case 'genres':
                return $driver->genres();
            case 'view':
                try {
                    return $driver->view((int)$request->input('id', 1));
                } catch (ModelNotFoundException $e) {
                    return response(['error' => $e, 'message' => 'Anime does not exist.'], 404);
                }
            case 'getEpisodeUrl':
                $data = $request->validate([
                    'id' => ['required', 'numeric', Rule::exists(Anime::class, 'id')],
                    'episode' => ['required', 'numeric'],
                ]);
                return $driver->getEpisodeUrl($data['id'], $data['episode']);
            default:
                return response(['message' => 'Command does not exist.'], 400);
        }
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return Anime::with('image')->paginate(20);
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Anime  $anime
     * @return \Illuminate\Http\Response
     */
    public function show(Anime $anime)
    {
        return $anime->load(['image']);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Anime  $anime
     * @return \Illuminate\Http\Response
     */
    public function destroy(Anime $anime)
    {
        $anime->delete();

        return response('', 204);
    }
}
