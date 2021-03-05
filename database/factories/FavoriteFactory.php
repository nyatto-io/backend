<?php

namespace Database\Factories;

use App\Models\Anime;
use App\Models\Favorite;
use App\Models\Manga;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class FavoriteFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Favorite::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        $classes = [
            'anime' => Anime::class,
            'manga' => Manga::class,
        ];

        $map = array_keys($classes);

        $index = $this->faker->numberBetween(0, count($classes) - 1);

        $class = $classes[$map[$index]];

        return [
            'type' => $map[$index],
            'group' => $this->faker->text(20),
            'user_id' => User::factory()->create()->id,
            'favorable_type' => $class,
            'favorable_id' => $class::factory()->create()->id,
        ];
    }
}
