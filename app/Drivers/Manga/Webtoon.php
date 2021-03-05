<?php

namespace App\Drivers\Manga;

use App\Interfaces\MangaDriver;
use App\Models\ChapterImage;
use App\Models\File;
use App\Models\Genre;
use App\Models\Manga;
use DiDom\Document;
use DiDom\Element;
use Exception;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class Webtoon implements MangaDriver
{
    /**
     * @var string
     */
    protected $url;

    /**
     * @param string $url
     * @return void
     */
    public function __construct($url)
    {
        $this->url = $url;
    }

    public function refreshCache($pages = 5)
    {
        $genresDone = false;
        for ($page = 1; $page <= $pages; $page++) {
            $site = new Document("{$this->url}/webtoons/page/{$page}", true);
            foreach ($site->find('.manga') as $item) {
                $image = $item->first('img')->src;
                $link = $item->first('.font-title')->first('a');
                $title = $link->text();
                $url = $link->href;
                $rating = $item->first('.total_votes')->text();
                $chapters = (int)Str::replaceFirst('Chapter', '', $item->first('.chapter')->first('a')->text());

                $manga = Manga::webtoon()
                    ->where('title', $title)
                    ->first();


                if ($manga) {
                    $manga->update([
                        'rating' => $rating,
                        'url' => $url,
                        'chapters' => $chapters,
                    ]);
                } else {
                    $file = File::process($image);

                    $file->save();

                    Manga::create([
                        'title' => $title,
                        'image_id' => $file->getKey(),
                        'group' => 'webtoon',
                        'rating' => $rating,
                        'url' => $url,
                        'chapters' => $chapters,
                    ]);
                }
            }

            if (!$genresDone) {
                foreach ($this->getGenres($site) as $genre) {
                    $title = $genre['title'];
                    $url = $genre['url'];
                    if (Genre::manga()->title($title)->count() === 0) {
                        Genre::create(['group' => 'webtoon', 'title' => $title, 'url' => $url]);
                    }
                }
            }
        }
    }

    public function fetchAll($limit = null)
    {
        $builder = Manga::webtoon()->with('image');
        return $limit !== null ? $builder->paginate($limit) : $builder->get();
    }

    public function search($keyword)
    {
        if ($genre = Genre::webtoon()->search($keyword)->first()) {
            return Manga::webtoon()
                ->like('genres', $genre->title)
                ->with('image')
                ->paginate(10);
        }

        /**
         * @var Paginator
         */
        $pagination = Manga::search($keyword)
            ->where('group', 'mangakakalot')
            ->paginate(20);

        $items = $pagination->getCollection();

        $data = $pagination->toArray();

        $data['data'] = $items->load('image')->toArray();

        return $data;
    }

    public function view($id)
    {
        $manga = Manga::webtoon()->findOrFail($id);

        $data = [];

        for ($start = 1; $start <= $manga->chapters; $start++) {
            $title = "Chapter {$start}";
            $data[] = [
                'title' => $title,
                'url' => $manga->url . Str::slug(Str::lower($title)),
                'chapter' => $start,
            ];
        }

        return $data;
    }

    public function genres()
    {
        return Genre::webtoon()->get();
    }

    public function getGenres(Document $site)
    {
        $data = [];

        foreach ($site->find('.menu-item-object-wp-manga-genre') as $item) {
            $link = $item->first('a');
            $data[] = [
                'title' => $link->text(),
                'url' => $link->href,
            ];
        }

        return $data;
    }

    public function getChapter($id, $chapter)
    {
        /**
         * @var Manga
         */
        $manga = Manga::webtoon()->findOrFail($id);

        if ($chapter > $manga->chapters) {
            return response(['message' => 'Chapter does not exist.'], 404);
        }

        $title = sprintf("Chapter %s", $chapter);

        try {

            if ($manga->chapters()->title($chapter)->count() === 0) {

                $url = $manga->url . Str::slug(Str::lower($title));
                $site = new Document($url, true);

                $images = collect($site->find('.wp-manga-chapter-img'))
                    ->filter(function (Element $image) {
                        return $image->src ? true : false;
                    })
                    ->map(function (Element $image) {
                        return trim($image->src);
                    })
                    ->map(function (string $url) {
                        $file = File::process($url);

                        $file->save();

                        return new ChapterImage(['file_id' => $file->id]);
                    })
                    ->all();

                $manga->chapters()
                    ->create(['title' => $chapter])
                    ->images()
                    ->saveMany($images);
            }

            return $manga->chapters()
                ->title($chapter)
                ->with('images.file')
                ->firstOrFail();
        } catch (Exception $exception) {
            return response(['message' => 'Chapter does not exist.', 'exception' => [
                'message' => $exception->getMessage(),
                'trace' => $exception->getTrace(),
            ]], 404);
        }
    }
}
