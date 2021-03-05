<?php

namespace App\Interfaces;

interface MangaDriver
{
    /**
     * Syncs the latest mangas on the site and saves it on cache or the database.
     * 
     * @param int $pages
     * @return void
     */
    public function refreshCache($pages);

    /**
     * Fetch a list of mangas on the site.
     * 
     * @param int|null $limit
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function fetchAll($limit = 1);

    /**
     * Search mangas on the site that matches the keyword.
     * 
     * @param string $keyword
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function search($keyword);

    /**
     * Returns the image links of the chapters of that manga.
     * 
     * @param int $id
     * @return mixed
     */
    public function view($id);

    /**
     * Returns a list of genres provided by the site.
     * 
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function genres();

    /**
     * Get a specific chapter of a manga.
     * 
     * @param int $id
     * @param int $chapter
     * @return \App\Models\Chapter
     */
    public function getChapter($id, $chapter);
}
