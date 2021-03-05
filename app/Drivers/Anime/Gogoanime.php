<?php

namespace App\Drivers\Anime;

use App\API\RapidAPI;
use App\Interfaces\AnimeDriver;
use App\Models\Anime;
use App\Models\File;
use App\Models\Genre;
use DiDom\Document;
use DiDom\Element;
use Exception;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class Gogoanime implements AnimeDriver
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

    /**
     * The RapidAPI Client
     *
     * @return \App\API\RapidAPI
     */
    public function client()
    {
        return app(RapidAPI::class);
    }

    public function refreshCache($pages = 5)
    {
        $genresDone = false;
        for ($page = 1; $page <= $pages; $page++) {
            $site = new Document("{$this->url}?page={$page}", true);
            $items = $site->first('.last_episodes')->first('.items')->find('li');
            foreach ($items as $item) {
                $episodes = (int)Str::replaceFirst('Episode', '', $item->first('.episode')->text());
                $img = $item->first('img');
                $image = $img->src;
                $title = $img->alt;
                $url = "{$this->url}/category/" . Str::slug(Str::lower($img->alt));
                $rating = '0';
                $description = '';
                $status = '';
                $genres = '';
                $englishTitle = '';
                $synonyms = '';
                $japaneseTitle = '';

                $anime = Anime::gogoanime()
                    ->where('title', $title)
                    ->first();

                $matches = $this->client()->search($title, 'anime');

                if (count($matches) > 0) {
                    $match = (array)$matches[0];

                    $rating = $match['score'];

                    $malSite = new Document($match['url'], true);

                    $description = $malSite->first('p[itemprop=description]')->text();
                    $title = $malSite->first('.title-name')->text();
                    if ($malSite->first('.title-english')) {
                        $englishTitle = $malSite->first('.title-english')->text();
                    }

                    $metas = collect($malSite->find('.dark_text'))->map(function (Element $span) {
                        return $span->parent();
                    });

                    $status = trim($metas->filter(function (Element $element) {
                        return strpos($element->text(), 'Status:') !== false;
                    })->map(function (Element $element) {
                        return trim(explode(':', $element->text())[1]);
                    })->implode(' '));

                    $genres = collect($malSite->find('span[itemprop=genre]'))->map(function (Element $element) {
                        return $element->text();
                    })->implode(', ');

                    $synonyms = trim($metas->filter(function (Element $element) {
                        return strpos($element->text(), 'Synonyms:') !== false;
                    })->map(function (Element $element) {
                        return trim(explode(':', $element->text())[1]);
                    })->implode(' '));

                    $japaneseTitle = trim($metas->filter(function (Element $element) {
                        return strpos($element->text(), 'Japanese:') !== false;
                    })->map(function (Element $element) {
                        return trim(explode(':', $element->text())[1]);
                    })->implode(' '));
                }

                if ($anime) {
                    $anime->update([
                        'episodes' => $episodes,
                        'status' => $status,
                        'rating' => $rating,
                        'episodes' => $episodes,
                    ]);
                } else {
                    $file = File::process($image);

                    $file->save();

                    Anime::create([
                        'title' => $title,
                        'image_id' => $file->getKey(),
                        'group' => 'gogoanime',
                        'url' => $url,
                        'episodes' => $episodes,
                        'description' => $description,
                        'rating' => $rating,
                        'status' => $status,
                        'genres' => $genres,
                        'english_title' => $englishTitle,
                        'synonyms' => $synonyms,
                        'japanese_title' => $japaneseTitle,
                    ]);
                }
            }
            if (!$genresDone) {
                foreach ($this->getGenres($site) as $genre) {
                    $title = $genre['title'];
                    $url = $genre['url'];
                    if (Genre::anime()->title($title)->count() === 0) {
                        Genre::create(['group' => 'gogoanime', 'title' => $title, 'url' => $url]);
                    }
                }
                $genresDone = true;
            }
        }
    }

    public function fetchAll($limit = null)
    {
        $builder = Anime::gogoanime()->with('image');
        return $limit !== null ? $builder->paginate($limit) : $builder->get();
    }

    public function search($keyword)
    {
        if ($genre = Genre::gogoanime()->search($keyword)->first()) {
            return Anime::gogoanime()
                ->like('genres', $genre->title)
                ->with('image')
                ->paginate(10);
        }

        /**
         * @var Paginator
         */
        $pagination = Anime::search($keyword)
            ->where('group', 'gogoanime')
            ->paginate(20);

        $items = $pagination->getCollection();

        $data = $pagination->toArray();

        $data['data'] = $items->load('image')->toArray();

        return $data;
    }

    public function view($id)
    {
        $anime = Anime::gogoanime()->findOrFail($id);

        $data = [];

        for ($start = 1; $start <= $anime->episodes; $start++) {
            $data[] = [
                'title' => "Episode {$start}",
                'url' => "{$this->url}/" . Str::slug(Str::lower("{$anime->title} episode {$start}")),
                'episode' => $start,
            ];
        }

        return $data;
    }

    public function genres()
    {
        return Genre::gogoanime()->get();
    }

    public function getGenres(Document $site)
    {
        $data = [];

        $genres = $site->first('.genre')->first('ul');
        foreach ($genres->find('li') as $item) {
            $link = $item->first('a');
            $title = $link->title;
            $data[] = [
                'title' => $title,
                'url' => "{$this->url}/genre/" . Str::slug(Str::lower($title)),
            ];
        }

        return $data;
    }

    public function getEpisodeUrl($id, $episode)
    {
        $anime = Anime::gogoanime()->findOrFail($id);

        if ($episode > $anime->episodes) {
            return response(['message' => 'Episode does not exist.'], 404);
        }

        if ($existing = $anime->episodes()->title($episode)->first()) {
            return ['url' => $existing->url];
        }

        $url = "{$this->url}/" . Str::slug(Str::lower($anime->title . ' episode ' . $episode));

        try {
            $site = new Document($url, true);
            $url = $site->first('.play-video')->first('iframe')->src;
            $anime->episodes()->create(['title' => $episode, 'url' => $url]);
            return ['url' => $url];
        } catch (Exception $exception) {
            Log::error(sprintf("Unable to fetch episode:\n\n{$exception}\n"));
            return response(['message' => 'Unable to fetch episode.', 'exception' => [
                'message' => $exception->getMessage(),
                'trace' => $exception->getTrace(),
            ]], 404);
        }
    }
}
