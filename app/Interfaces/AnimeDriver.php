<?php

namespace App\Interfaces;

interface AnimeDriver
{
    /**
     * Syncs the latest anime on the site and saves it on cache or the database.
     * 
     * @param int $pages
     * @return void
     */
    public function refreshCache($pages);

    /**
     * Fetch a list of anime on the site.
     * 
     * @param int|null $limit
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function fetchAll($limit = 1);

    /**
     * Search anime on the site that matches the keyword.
     * 
     * @param string $keyword
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function search($keyword);

    /**
     * Returns the image links of the chapters of that anime.
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
     * Get the video url for a specific episode of the anime.
     * 
     * @param int $id
     * @param int $episode
     * @return string
     */
    public function getEpisodeUrl($id, $episode);
}
