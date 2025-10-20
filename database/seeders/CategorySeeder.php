<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class CategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    // database/seeders/CategorySeeder.php
    public function run(): void
    {
        \App\Models\Category::insert([
            ['name' => 'Technology'],
            ['name' => 'Lifestyle'],
            ['name' => 'Business'],
        ]);
    }
}
