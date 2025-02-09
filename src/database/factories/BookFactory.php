<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\Publisher;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Book>
 */
class BookFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'title' => fake()->sentence(3),
            'published' => fake()->date(),
            'isbn' => $this->generateIsbn10(),
            'isbn13' => $this->generateIsbn13(),
            'publisher_id' => fake()->optional(0.8)->randomElement(Publisher::pluck('id')->toArray()),
        ];
    }

    private function generateIsbn10(): string
    {
        $digits = $this->faker->numerify('#########'); // 9 случайных цифр
        $checksum = $this->calculateIsbn10Checksum($digits);
        return $digits . $checksum;
    }

    private function calculateIsbn10Checksum(string $digits): string
    {
        $sum = 0;
        for ($i = 0; $i < 9; $i++) {
            $sum += ((int) $digits[$i]) * ($i + 1);
        }
        $remainder = $sum % 11;
        return ($remainder === 10) ? 'X' : (string) $remainder;
    }

    /**
     * Генерация корректного ISBN-13
     */
    private function generateIsbn13(): string
    {
        $prefix = '978'; // Стандартный префикс для ISBN-13
        $body = $this->faker->numerify('#########'); // 9 случайных цифр
        $partialIsbn = $prefix . $body;
        $checksum = $this->calculateIsbn13Checksum($partialIsbn);
        return $partialIsbn . $checksum;
    }

    private function calculateIsbn13Checksum(string $digits): string
    {
        $sum = 0;
        for ($i = 0; $i < 12; $i++) {
            $multiplier = ($i % 2 === 0) ? 1 : 3;
            $sum += (int) $digits[$i] * $multiplier;
        }
        $remainder = $sum % 10;
        $checksum = (10 - $remainder) % 10;
        return (string) $checksum;
    }
}
