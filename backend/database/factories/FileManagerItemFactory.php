<?php

namespace Database\Factories;

use App\Models\FileManagerItem;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<FileManagerItem>
 */
class FileManagerItemFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $isFolder = fake()->boolean(40);

        return [
            'name' => $isFolder ? fake()->words(2, true).'-folder' : fake()->fileName(),
            'type' => $isFolder ? 'folder' : 'file',
            'size' => $isFolder ? null : fake()->randomElement(['2.5 MB', '512 KB', '1.2 MB', '340 KB']),
            'date_label' => fake()->dateTimeBetween('-6 months', 'now')->format('d M, Y'),
            'icon' => $isFolder ? 'bx bx-folder' : fake()->randomElement(['bx bx-file', 'bx bx-image', 'bx bx-video', 'bx bx-music']),
            'icon_class' => fake()->randomElement(['text-warning', 'text-primary', 'text-success', 'text-danger']),
            'parent_id' => null,
            'user_id' => null,
        ];
    }
}
