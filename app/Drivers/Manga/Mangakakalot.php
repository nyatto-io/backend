<?php

namespace App\Drivers\Manga;

use App\Interfaces\MangaDriver;
use App\Models\Chapter;
use App\Models\ChapterImage;
use App\Models\File;
use App\Models\Genre;
use App\Models\Manga;
use DiDom\Document;
use DiDom\Element;
use Exception;
use GuzzleHttp\Client;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Str;

class Mangakakalot implements MangaDriver
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
            $site = new Document("{$this->url}/manga_list?page={$page}", true);
            foreach ($site->find('.list-truyen-item-wrap') as $item) {
                $link = $item->first('a');
                $image = $item->first('img')->src;
                $title = $link->title;
                $url = $link->href;
                $rating = '';
                $chapterElement = explode(':', $item->first('.list-story-item-wrap-chapter')->text());
                $chapterFragments = explode('Chapter', $chapterElement[0]);
                $chapters = (int)trim($chapterFragments[count($chapterFragments) - 1]);

                $manga = Manga::mangakakalot()
                    ->title($title)
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

                    $manga = Manga::create([
                        'title' => $title,
                        'image_id' => $file->getKey(),
                        'group' => 'mangakakalot',
                        'rating' => $rating,
                        'url' => $url,
                        'chapters' => $chapters,
                    ]);
                }

                $this->insertMangaMeta($manga);
            }

            if (!$genresDone) {
                foreach ($this->getGenres($site) as $genre) {
                    $title = $genre['title'];
                    $url = $genre['url'];
                    if (Genre::manga()->title($title)->count() === 0) {
                        Genre::create(['group' => 'mangakakalot', 'title' => $title, 'url' => $url]);
                    }
                }
            }
        }
    }

    public function fetchAll($limit = null)
    {
        $builder = Manga::mangakakalot()->with('image');
        return $limit !== null ? $builder->paginate($limit) : $builder->get();
    }

    public function search($keyword)
    {
        if ($genre = Genre::mangakakalot()->search($keyword)->first()) {
            return Manga::mangakakalot()
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
        $manga = Manga::mangakakalot()->findOrFail($id);

        $data = [];

        for ($start = 1; $start <= $manga->chapters; $start++) {
            $title = "Chapter {$start}";
            $data[] = [
                'title' => $title,
                'url' => $manga->url . '/' . Str::slug(Str::lower($title), '_'),
                'chapter' => $start,
            ];
        }

        return $data;
    }

    public function genres()
    {
        return Genre::mangakakalot()->get();
    }

    public function getGenres(Document $site)
    {
        $data = [];

        foreach ($site->first('.tag')->find('li') as $item) {
            $link = $item->first('a');
            $title = trim($link->text());

            $data[] = [
                'title' => $title,
                'url' => $link->href,
            ];
        }

        return collect($data)->filter(function ($entry) {
            return $entry['title'] !== 'ALL';
        });
    }

    public function getChapter($id, $chapter)
    {
        /**
         * @var Manga
         */
        $manga = Manga::mangakakalot()->findOrFail($id);

        if ($chapter > $manga->chapters) {
            return response(['message' => 'Chapter does not exist.'], 404);
        }

        try {
            $chapter = $manga->chapters()
                ->title($chapter)
                ->firstOrFail();

            if ($chapter->images()->count() === 0) {
                /**
                 * @var Client
                 */
                $client = app(Client::class);

                $proxy = config('proxy.url');

                $response = $client->post(sprintf('%s%s', $proxy, '/manga/mangakakalot/chapters'), [
                    'json' => [
                        'url' => $chapter->url,
                    ]
                ]);

                $uris = (array)json_decode($response->getBody());

                $images = collect($uris)->map((function (string $uri) use ($proxy) {
                    $file = File::process(sprintf('%s%s%s', $proxy, '/storage/', $uri));

                    $file->save();

                    return new ChapterImage(['file_id' => $file->id]);
                }));

                $chapter->images()->saveMany($images);
            }

            $chapter->load('images.file');

            return $chapter;
        } catch (Exception $exception) {
            return response(['message' => 'Chapter does not exist.', 'exception' => [
                'message' => $exception->getMessage(),
                'trace' => $exception->getTrace(),
            ]], 404);
        }
    }

    /**
     * @param Manga $manga
     * @return void
     */
    protected function insertMangaMeta($manga)
    {
        $site = new Document($manga->url, true);

        $parsed = parse_url($manga->url, PHP_URL_HOST);

        if (strpos($parsed, 'mangakakalot') !== false) {
            $infoItems = collect($site->first('.manga-info-text')->find('li'));

            $genres = collect($infoItems->filter(function (Element $item) {
                return strpos($item->text(), 'Genres') !== false;
            })->first()->find('a'))
                ->map(function (Element $a) {
                    return $a->text();
                })
                ->join(', ');

            $status = trim(explode(':', $infoItems->filter(function (Element $item) {
                return strpos($item->text(), 'Status') !== false;
            })->first()->text())[1]);

            $manga->update([
                'rating' => explode(':', explode('/', $site->first('#rate_row_cmd')->text())[0])[1],
                'status' => $status,
                'genres' => $genres,
                'description' => trim(explode('summary:', $site->first('#noidungm')->text())[1])
            ]);

            $links = $site->first('.chapter-list')->find('a');
        } else {
            $table = $site->first('.variations-tableInfo');

            $manga->update([
                'rating' => $site->first('em[property="v:average"]')->text(),
                'status' => $table->first('.info-status')->parent()->parent()->first('.table-value')->text(),
                'genres' => collect($table->first('.info-genres')->parent()->parent()->first('.table-value')->find('a'))
                    ->map(function (Element $link) {
                        return $link->text();
                    })
                    ->join(', '),
                'description' => Str::replaceFirst('Description :', '', $site->first('#panel-story-info-description')->text()),
            ]);

            $links = collect($site->first('.row-content-chapter')->find('li'))
                ->map(function (Element $item) {
                    return $item->first('a');
                });
        }

        $metas = collect([]);

        foreach ($links as $link) {
            $url = $link->href;
            if (strpos($parsed, 'mangakakalot') !== false) {
                $chapter = (float)trim(Str::replaceFirst('Chapter', '', $link->text()));
            } else {
                $chapter = (float)trim(Str::replaceFirst('Chapter', '', explode(':', $link->text())[0]));
            }

            $metas->add([
                'url' => $url,
                'chapter' => $chapter,
            ]);
        }

        $chapters = $manga->chapters()->get()->map(function (Chapter $chapter) {
            return $chapter->title;
        })->toArray();

        $nonExisting = $metas->filter(function ($meta) use ($chapters) {
            return !in_array($meta['chapter'], $chapters);
        });

        foreach ($nonExisting as $meta) {
            $manga->chapters()
                ->create(['title' => $meta['chapter'], 'url' => $meta['url']]);
        }
    }
}
