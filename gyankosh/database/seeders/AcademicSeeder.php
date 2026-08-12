<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Faculty;
use App\Models\Department;
use App\Models\Program;
use App\Models\AcademicYear;
use App\Models\Semester;

class AcademicSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create Faculties
        $faculties = [
            ['name' => 'Science', 'code' => 'SCI'],
            ['name' => 'Engineering', 'code' => 'ENG'],
            ['name' => 'Arts & Humanities', 'code' => 'ARTS'],
            ['name' => 'Commerce', 'code' => 'COM'],
            ['name' => 'Management', 'code' => 'MGT'],
        ];

        foreach ($faculties as $facultyData) {
            Faculty::firstOrCreate($facultyData);
        }

        // Create Departments
        $departments = [
            ['name' => 'Computer Science', 'code' => 'CS', 'faculty_name' => 'Science'],
            ['name' => 'Physics', 'code' => 'PHY', 'faculty_name' => 'Science'],
            ['name' => 'Chemistry', 'code' => 'CHEM', 'faculty_name' => 'Science'],
            ['name' => 'Mathematics', 'code' => 'MATH', 'faculty_name' => 'Science'],
            ['name' => 'Information Technology', 'code' => 'IT', 'faculty_name' => 'Engineering'],
            ['name' => 'Electronics', 'code' => 'ECE', 'faculty_name' => 'Engineering'],
            ['name' => 'Mechanical Engineering', 'code' => 'MECH', 'faculty_name' => 'Engineering'],
            ['name' => 'Civil Engineering', 'code' => 'CE', 'faculty_name' => 'Engineering'],
            ['name' => 'English', 'code' => 'ENG-LIT', 'faculty_name' => 'Arts & Humanities'],
            ['name' => 'History', 'code' => 'HIST', 'faculty_name' => 'Arts & Humanities'],
            ['name' => 'Economics', 'code' => 'ECON', 'faculty_name' => 'Commerce'],
            ['name' => 'Business Administration', 'code' => 'MBA', 'faculty_name' => 'Management'],
        ];

        foreach ($departments as $deptData) {
            $faculty = Faculty::where('name', $deptData['faculty_name'])->first();
            Department::firstOrCreate([
                'name' => $deptData['name'],
                'code' => $deptData['code'],
                'faculty_id' => $faculty->id,
            ]);
        }

        // Create Programs
        $programs = [
            ['name' => 'Bachelor of Computer Applications', 'code' => 'BCA', 'duration_years' => 3, 'degree_type' => 'Bachelors', 'dept_name' => 'Computer Science'],
            ['name' => 'Master of Computer Applications', 'code' => 'MCA', 'duration_years' => 2, 'degree_type' => 'Masters', 'dept_name' => 'Computer Science'],
            ['name' => 'B.Sc. Physics', 'code' => 'BSC-PHY', 'duration_years' => 3, 'degree_type' => 'Bachelors', 'dept_name' => 'Physics'],
            ['name' => 'M.Sc. Physics', 'code' => 'MSC-PHY', 'duration_years' => 2, 'degree_type' => 'Masters', 'dept_name' => 'Physics'],
            ['name' => 'B.Sc. Chemistry', 'code' => 'BSC-CHEM', 'duration_years' => 3, 'degree_type' => 'Bachelors', 'dept_name' => 'Chemistry'],
            ['name' => 'B.Tech. Information Technology', 'code' => 'BTech-IT', 'duration_years' => 4, 'degree_type' => 'Bachelors', 'dept_name' => 'Information Technology'],
            ['name' => 'M.Tech. Information Technology', 'code' => 'MTech-IT', 'duration_years' => 2, 'degree_type' => 'Masters', 'dept_name' => 'Information Technology'],
            ['name' => 'B.A. English', 'code' => 'BA-ENG', 'duration_years' => 3, 'degree_type' => 'Bachelors', 'dept_name' => 'English'],
            ['name' => 'M.A. English', 'code' => 'MA-ENG', 'duration_years' => 2, 'degree_type' => 'Masters', 'dept_name' => 'English'],
            ['name' => 'B.Com', 'code' => 'BCOM', 'duration_years' => 3, 'degree_type' => 'Bachelors', 'dept_name' => 'Economics'],
            ['name' => 'MBA', 'code' => 'MBA', 'duration_years' => 2, 'degree_type' => 'Masters', 'dept_name' => 'Business Administration'],
        ];

        foreach ($programs as $progData) {
            $department = Department::where('name', $progData['dept_name'])->first();
            Program::firstOrCreate([
                'name' => $progData['name'],
                'code' => $progData['code'],
            ], [
                'duration_years' => $progData['duration_years'],
                'degree_type' => $progData['degree_type'],
                'department_id' => $department->id,
            ]);
        }

        // Create Academic Years
        $currentYear = date('Y');
        for ($i = -2; $i <= 3; $i++) {
            $startYear = $currentYear + $i;
            $endYear = $startYear + 1;
            AcademicYear::firstOrCreate(
                ['name' => "{$startYear}-{$endYear}"],
                [
                    'start_date' => "$startYear-07-01",
                    'end_date' => "$endYear-06-30",
                    'is_current' => $i === 0,
                ]
            );
        }

        // Create Semesters for each program
        $programs = Program::all();
        foreach ($programs as $program) {
            $totalSemesters = $program->duration_years * 2;
            for ($i = 1; $i <= $totalSemesters; $i++) {
                Semester::firstOrCreate([
                    'program_id' => $program->id,
                    'semester_number' => $i,
                ], [
                    'name' => "Semester {$i}",
                    'start_date' => now(),
                    'end_date' => now()->addMonths(6),
                ]);
            }
        }

        $this->command->info('Academic structure seeded successfully!');
    }
}
