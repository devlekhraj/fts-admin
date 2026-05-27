<?php

namespace Database\Seeders;



use Database\Seeders\FilesTableSeeder;
use Database\Seeders\FileUsagesTableSeeder;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call(FilesTableSeeder::class);
        $this->call(FileUsagesTableSeeder::class);
    }
}
