<?php

namespace Database\Seeders;

use App\Models\Contact;
use App\Models\User;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // User::factory(10)->create();

        User::factory()->create([
            'name' => 'Test User',
            'email' => 'test@example.com',
        ]);

        $blanchar = User::factory()
            ->has(Contact::factory()->count(10))
                ->create([
                    'name' => 'BLANCHAR',
                    'email' => 'anchar2107@gmail.com',
                ]);

    }
}
