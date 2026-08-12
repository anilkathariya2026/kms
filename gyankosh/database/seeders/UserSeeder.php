<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Role;
use App\Models\Faculty;
use App\Models\Department;
use App\Models\Program;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Get roles
        $adminRole = Role::where('name', 'admin')->first();
        $librarianRole = Role::where('name', 'librarian')->first();
        $staffRole = Role::where('name', 'staff')->first();
        $studentRole = Role::where('name', 'student')->first();

        // Get or create faculty and departments
        $faculty = Faculty::firstOrCreate(['name' => 'Science']);
        $engineeringFaculty = Faculty::firstOrCreate(['name' => 'Engineering']);

        $csDept = Department::firstOrCreate([
            'name' => 'Computer Science',
            'faculty_id' => $faculty->id
        ]);
        
        $physicsDept = Department::firstOrCreate([
            'name' => 'Physics',
            'faculty_id' => $faculty->id
        ]);

        $itDept = Department::firstOrCreate([
            'name' => 'Information Technology',
            'faculty_id' => $engineeringFaculty->id
        ]);

        // Get or create programs
        $bcaProgram = Program::firstOrCreate([
            'name' => 'Bachelor of Computer Applications',
            'code' => 'BCA',
            'department_id' => $csDept->id
        ]);

        $bscProgram = Program::firstOrCreate([
            'name' => 'B.Sc. Physics',
            'code' => 'BSC-PHY',
            'department_id' => $physicsDept->id
        ]);

        $mcaProgram = Program::firstOrCreate([
            'name' => 'Master of Computer Applications',
            'code' => 'MCA',
            'department_id' => $csDept->id
        ]);

        // Create Admin
        User::firstOrCreate(
            ['email' => 'admin@gyankosh.edu'],
            [
                'name' => 'System Administrator',
                'password' => Hash::make('password123'),
                'phone' => '+91-9876543210',
                'role_id' => $adminRole->id,
                'faculty_id' => null,
                'department_id' => null,
                'program_id' => null,
                'status' => 'active',
                'email_verified_at' => now(),
            ]
        );

        // Create Librarians (2)
        User::firstOrCreate(
            ['email' => 'librarian@gyankosh.edu'],
            [
                'name' => 'Head Librarian',
                'password' => Hash::make('password123'),
                'phone' => '+91-9876543211',
                'role_id' => $librarianRole->id,
                'faculty_id' => null,
                'department_id' => null,
                'program_id' => null,
                'status' => 'active',
                'email_verified_at' => now(),
            ]
        );

        User::firstOrCreate(
            ['email' => 'librarian2@gyankosh.edu'],
            [
                'name' => 'Assistant Librarian',
                'password' => Hash::make('password123'),
                'phone' => '+91-9876543212',
                'role_id' => $librarianRole->id,
                'faculty_id' => null,
                'department_id' => null,
                'program_id' => null,
                'status' => 'active',
                'email_verified_at' => now(),
            ]
        );

        // Create Staff (5)
        $staffEmails = [
            ['email' => 'staff1@gyankosh.edu', 'name' => 'Dr. Rajesh Kumar', 'dept' => $csDept->id, 'prog' => null],
            ['email' => 'staff2@gyankosh.edu', 'name' => 'Dr. Priya Sharma', 'dept' => $physicsDept->id, 'prog' => null],
            ['email' => 'staff3@gyankosh.edu', 'name' => 'Prof. Amit Patel', 'dept' => $csDept->id, 'prog' => null],
            ['email' => 'staff4@gyankosh.edu', 'name' => 'Dr. Sunita Singh', 'dept' => $itDept->id, 'prog' => null],
            ['email' => 'staff5@gyankosh.edu', 'name' => 'Prof. Vikram Mehta', 'dept' => $csDept->id, 'prog' => null],
        ];

        foreach ($staffEmails as $staffData) {
            User::firstOrCreate(
                ['email' => $staffData['email']],
                [
                    'name' => $staffData['name'],
                    'password' => Hash::make('password123'),
                    'phone' => '+91-' . rand(9000000000, 9999999999),
                    'role_id' => $staffRole->id,
                    'faculty_id' => $faculty->id,
                    'department_id' => $staffData['dept'],
                    'program_id' => $staffData['prog'],
                    'status' => 'active',
                    'email_verified_at' => now(),
                ]
            );
        }

        // Create Students (20)
        $studentPrograms = [$bcaProgram->id, $bscProgram->id, $mcaProgram->id];
        
        for ($i = 1; $i <= 20; $i++) {
            $progId = $studentPrograms[array_rand($studentPrograms)];
            $program = Program::find($progId);
            $department = $program ? $program->department : null;
            
            User::firstOrCreate(
                ['email' => "student{$i}@gyankosh.edu"],
                [
                    'name' => "Student {$i}",
                    'password' => Hash::make('password123'),
                    'phone' => '+91-' . rand(9000000000, 9999999999),
                    'role_id' => $studentRole->id,
                    'faculty_id' => $department ? $department->faculty_id : null,
                    'department_id' => $department ? $department->id : null,
                    'program_id' => $progId,
                    'status' => 'active',
                    'email_verified_at' => now(),
                ]
            );
        }

        $this->command->info('Users seeded successfully!');
        $this->command->info('Demo Accounts:');
        $this->command->info('Admin: admin@gyankosh.edu / password123');
        $this->command->info('Librarian: librarian@gyankosh.edu / password123');
        $this->command->info('Staff: staff1@gyankosh.edu / password123');
        $this->command->info('Student: student1@gyankosh.edu / password123');
    }
}
