<?php

namespace Database\Seeders;

use App\Enums\UserRole;
use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {

        User::create([
            'name' => 'Reader',
            'email' => 'reader@example.com',
            'password' => Hash::make('password'),
            'role' => UserRole::READER,
        ]);

        User::create([
            'name' => 'Librarian',
            'email' => 'librarian@example.com',
            'password' => Hash::make('password'),
            'role' => UserRole::LIBRARIAN,
        ]);

        $this->call([
            AuthorSeeder::class,
            PublisherSeeder::class,
            BookSeeder::class,
            BookCopySeeder::class,
        ]);
    }
}
