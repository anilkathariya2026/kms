<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Role;
use App\Models\Permission;

class RoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create roles
        $adminRole = Role::firstOrCreate(
            ['name' => 'admin'],
            [
                'display_name' => 'Administrator',
                'description' => 'System Administrator with full access',
            ]
        );

        $librarianRole = Role::firstOrCreate(
            ['name' => 'librarian'],
            [
                'display_name' => 'Librarian',
                'description' => 'Library Manager with library management privileges',
            ]
        );

        $staffRole = Role::firstOrCreate(
            ['name' => 'staff'],
            [
                'display_name' => 'Staff',
                'description' => 'Faculty/Staff member with contribution privileges',
            ]
        );

        $studentRole = Role::firstOrCreate(
            ['name' => 'student'],
            [
                'display_name' => 'Student',
                'description' => 'Student with basic access privileges',
            ]
        );

        // Get all permissions
        $permissions = Permission::all()->keyBy('name');

        // Admin permissions - all permissions
        $adminPermissions = Permission::pluck('id')->toArray();
        $adminRole->permissions()->sync($adminPermissions);

        // Librarian permissions
        $librarianPermissionNames = [
            'view', 'create', 'edit', 'delete', 'approve', 'upload', 'download',
            'borrow', 'return', 'renew', 'reserve',
            'manage-books', 'manage-copies', 'issue-books', 'process-returns',
            'manage-reservations', 'manage-fines', 'manage-inventory', 'manage-resources',
            'search', 'bookmark', 'favorite', 'view-history', 'receive-notifications',
            'generate-reports', 'view-analytics'
        ];
        $librarianPermissions = collect($librarianPermissionNames)
            ->map(fn($name) => $permissions[$name]->id ?? null)
            ->filter()
            ->toArray();
        $librarianRole->permissions()->sync($librarianPermissions);

        // Staff permissions
        $staffPermissionNames = [
            'view', 'download', 'borrow', 'return', 'renew', 'reserve',
            'contribute-resources', 'track-contributions',
            'search', 'bookmark', 'favorite', 'view-history', 'receive-notifications'
        ];
        $staffPermissions = collect($staffPermissionNames)
            ->map(fn($name) => $permissions[$name]->id ?? null)
            ->filter()
            ->toArray();
        $staffRole->permissions()->sync($staffPermissions);

        // Student permissions
        $studentPermissionNames = [
            'view', 'download', 'borrow', 'return', 'renew', 'reserve',
            'view-due-dates', 'view-fines',
            'search', 'bookmark', 'favorite', 'view-history', 'receive-notifications'
        ];
        $studentPermissions = collect($studentPermissionNames)
            ->map(fn($name) => $permissions[$name]->id ?? null)
            ->filter()
            ->toArray();
        $studentRole->permissions()->sync($studentPermissions);

        $this->command->info('Roles and permissions seeded successfully!');
    }
}
