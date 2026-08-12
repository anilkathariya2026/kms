<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([
            PermissionSeeder::class,
            RoleSeeder::class,
            AcademicSeeder::class,
            UserSeeder::class,
            LibrarySeeder::class,
        ]);
        
        $this->command->info('🎉 Gyankosh database seeded successfully!');
    }
}
