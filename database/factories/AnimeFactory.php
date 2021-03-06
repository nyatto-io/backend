<?php

namespace Database\Factories;

use App\Models\Anime;
use Illuminate\Database\Eloquent\Factories\Factory;

class AnimeFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Anime::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        $faker = $this->faker;
        return [
            'group' => $faker->text,
            'genres' => $faker->sentence,
            'title' => $faker->title,
            'status' => $faker->text,
            'url' => $faker->url,
            'image_url' => $faker->imageUrl(),
        ];
    }
}
