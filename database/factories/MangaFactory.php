<?php

namespace Database\Factories;

use App\Models\Manga;
use Illuminate\Database\Eloquent\Factories\Factory;

class MangaFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Manga::class;

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
            'rating' => $faker->numberBetween(0, 5),
            'title' => $faker->title,
            'status' => $faker->text,
            'url' => $faker->url,
            'image_url' => $faker->imageUrl(),
        ];
    }
}
