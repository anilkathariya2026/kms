<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Role;
use App\Models\Permission;

class PermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Define all permissions
        $permissions = [
            // Core permissions
            ['name' => 'view', 'description' => 'View resources'],
            ['name' => 'create', 'description' => 'Create resources'],
            ['name' => 'edit', 'description' => 'Edit resources'],
            ['name' => 'delete', 'description' => 'Delete resources'],
            ['name' => 'approve', 'description' => 'Approve resources'],
            ['name' => 'upload', 'description' => 'Upload files'],
            ['name' => 'download', 'description' => 'Download files'],
            
            // Library permissions
            ['name' => 'borrow', 'description' => 'Borrow books'],
            ['name' => 'return', 'description' => 'Return books'],
            ['name' => 'renew', 'description' => 'Renew books'],
            ['name' => 'reserve', 'description' => 'Reserve books'],
            ['name' => 'manage', 'description' => 'Manage system'],
            
            // Admin specific
            ['name' => 'manage-users', 'description' => 'Manage users'],
            ['name' => 'manage-roles', 'description' => 'Manage roles and permissions'],
            ['name' => 'manage-settings', 'description' => 'Manage system settings'],
            ['name' => 'view-analytics', 'description' => 'View analytics'],
            ['name' => 'generate-reports', 'description' => 'Generate reports'],
            ['name' => 'view-logs', 'description' => 'View activity logs'],
            
            // Librarian specific
            ['name' => 'manage-books', 'description' => 'Manage books'],
            ['name' => 'manage-copies', 'description' => 'Manage book copies'],
            ['name' => 'issue-books', 'description' => 'Issue books'],
            ['name' => 'process-returns', 'description' => 'Process returns'],
            ['name' => 'manage-reservations', 'description' => 'Manage reservations'],
            ['name' => 'manage-fines', 'description' => 'Manage fines'],
            ['name' => 'manage-inventory', 'description' => 'Manage inventory'],
            ['name' => 'manage-resources', 'description' => 'Manage digital resources'],
            
            // Staff specific
            ['name' => 'contribute-resources', 'description' => 'Contribute academic resources'],
            ['name' => 'track-contributions', 'description' => 'Track own contributions'],
            
            // Student specific
            ['name' => 'view-due-dates', 'description' => 'View due dates'],
            ['name' => 'view-fines', 'description' => 'View fines'],
            
            // Common
            ['name' => 'search', 'description' => 'Search resources'],
            ['name' => 'bookmark', 'description' => 'Bookmark resources'],
            ['name' => 'favorite', 'description' => 'Add to favorites'],
            ['name' => 'view-history', 'description' => 'View reading history'],
            ['name' => 'receive-notifications', 'description' => 'Receive notifications'],
        ];

        foreach ($permissions as $permissionData) {
            Permission::firstOrCreate(
                ['name' => $permissionData['name']],
                [
                    'display_name' => ucfirst(str_replace('-', ' ', $permissionData['name'])),
                    'description' => $permissionData['description'],
                    'group' => 'system',
                ]
            );
        }

        $this->command->info('Permissions seeded successfully!');
    }
}
