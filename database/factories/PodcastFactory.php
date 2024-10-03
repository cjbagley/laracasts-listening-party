<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Podcast>
 */
class PodcastFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'title' => $this->faker->sentence(nbWords: 4),
            'description' => $this->faker->paragraph(),
            'hosts' => $this->faker->name(),
            'artwork_url' => $this->faker->url(),
            'rss_url' => $this->faker->url(),
        ];
    }
}
