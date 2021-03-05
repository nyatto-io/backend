<?php

namespace App\Http\Controllers;

use App\Drivers\Manga\Mangakakalot;
use App\Drivers\Manga\Webtoon;
use App\Interfaces\MangaDriver;
use App\Jobs\CacheManga;
use App\Models\Manga;
use App\Rules\MangaChapterExists;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class MangaController extends Controller
{
    public function commandWebtoon(Request $request, Webtoon $driver)
    {
        return $this->handleMangaCommand($request, $driver);
    }

    public function commandMangakakalot(Request $request, Mangakakalot $driver)
    {
        return $this->handleMangaCommand($request, $driver);
    }

    protected function handleMangaCommand(Request $request, MangaDriver $driver)
    {
        $type = $request->input('type');

        switch ($type) {
            case 'refreshCache':
                $pages = (int)$request->input('pages', 5);
                dispatch(new CacheManga($driver, $pages));
                return response('', 204);
                break;
            case 'fetchAll':
                return $driver->fetchAll(20);
                break;
            case 'search':
                return $driver->search($request->input('keyword', ''));
                break;
            case 'genres':
                return $driver->genres();
                break;
            case 'view':
                try {
                    return $driver->view((int)$request->input('id', 0));
                } catch (ModelNotFoundException $e) {
                    return response(['error' => $e, 'message' => 'Manga does not exist.'], 404);
                }
                break;
            case 'getChapter':
                $data = $request->validate([
                    'id' => ['required', 'numeric', Rule::exists(Manga::class, 'id')],
                    'chapter' => ['required', 'numeric'],
                ]);
                return $driver->getChapter($data['id'], $data['chapter']);
            default:
                return response(['message' => 'Command does not exist.'], 400);
                break;
        }
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return Manga::with('image')->paginate(20);
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Manga  $manga
     * @return \Illuminate\Http\Response
     */
    public function show(Manga $manga)
    {
        return $manga->load(['image']);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Manga  $manga
     * @return \Illuminate\Http\Response
     */
    public function destroy(Manga $manga)
    {
        $manga->delete();

        return response('', 204);
    }
}
