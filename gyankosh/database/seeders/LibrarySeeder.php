<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Author;
use App\Models\Publisher;
use App\Models\Category;
use App\Models\Subject;
use App\Models\Book;
use App\Models\BookCopy;
use App\Models\DigitalResource;
use App\Models\ResourceCategory;
use App\Models\User;
use App\Models\Borrowing;
use App\Models\Reservation;
use App\Models\Fine;
use Carbon\Carbon;

class LibrarySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create Authors
        $authors = [
            ['name' => 'Cormen, Thomas H.', 'bio' => 'Computer Scientist, MIT'],
            ['name' => 'Tanenbaum, Andrew S.', 'bio' => 'Computer Scientist'],
            ['name' => 'Silberschatz, Abraham', 'bio' => 'Computer Scientist'],
            ['name' => 'Kurose, James F.', 'bio' => 'Network Researcher'],
            ['name' => 'Gamma, Erich', 'bio' => 'Software Engineer'],
            ['name' => 'Martin, Robert C.', 'bio' => 'Software Engineer'],
            ['name' => 'Fowler, Martin', 'bio' => 'Software Engineer'],
            ['name' => 'Knuth, Donald E.', 'bio' => 'Computer Scientist, Stanford'],
            ['name' => 'Stallings, William', 'bio' => 'Computer Scientist'],
            ['name' => 'Forouzan, Behrouz A.', 'bio' => 'Computer Scientist'],
            ['name' => 'Navathe, Shamkant B.', 'bio' => 'Database Researcher'],
            ['name' => 'Date, C.J.', 'bio' => 'Database Expert'],
            ['name' => 'Stroustrup, Bjarne', 'bio' => 'Creator of C++'],
            ['name' => 'Bloch, Joshua', 'bio' => 'Java Expert'],
            ['name' => 'McConnell, Steve', 'bio' => 'Software Engineer'],
            ['name' => 'Hunt, Andrew', 'bio' => 'Software Engineer'],
            ['name' => 'Thomas, Dave', 'bio' => 'Software Engineer'],
            ['name' => 'Beck, Kent', 'bio' => 'XP Pioneer'],
            ['name' => 'Nygaard, Kristen', 'bio' => 'OOP Pioneer'],
            ['name' => 'Booch, Grady', 'bio' => 'Software Engineer'],
        ];

        foreach ($authors as $authorData) {
            Author::firstOrCreate(['name' => $authorData['name']], ['bio' => $authorData['bio']]);
        }

        // Create Publishers
        $publishers = [
            ['name' => 'Pearson Education', 'address' => 'New Delhi, India'],
            ['name' => 'McGraw Hill', 'address' => 'New York, USA'],
            ['name' => 'Wiley', 'address' => 'New Jersey, USA'],
            ['name' => 'Springer', 'address' => 'Berlin, Germany'],
            ['name' => 'Elsevier', 'address' => 'Amsterdam, Netherlands'],
            ['name' => 'Addison-Wesley', 'address' => 'Boston, USA'],
            ['name' => 'O\'Reilly Media', 'address' => 'Sebastopol, USA'],
            ['name' => 'Cambridge University Press', 'address' => 'Cambridge, UK'],
            ['name' => 'Oxford University Press', 'address' => 'Oxford, UK'],
            ['name' => 'Prentice Hall', 'address' => 'New Jersey, USA'],
        ];

        foreach ($publishers as $pubData) {
            Publisher::firstOrCreate($pubData);
        }

        // Create Categories
        $categories = [
            ['name' => 'Computer Science', 'description' => 'Computing and Programming'],
            ['name' => 'Physics', 'description' => 'Physical Sciences'],
            ['name' => 'Mathematics', 'description' => 'Mathematical Sciences'],
            ['name' => 'Engineering', 'description' => 'Engineering Disciplines'],
            ['name' => 'Management', 'description' => 'Business and Management'],
            ['name' => 'Literature', 'description' => 'Language and Literature'],
            ['name' => 'Social Sciences', 'description' => 'Social Studies'],
            ['name' => 'Research Papers', 'description' => 'Academic Research'],
            ['name' => 'Thesis', 'description' => 'Dissertations and Thesis'],
            ['name' => 'Journals', 'description' => 'Academic Journals'],
        ];

        foreach ($categories as $catData) {
            Category::firstOrCreate($catData);
        }

        // Create Subjects
        $subjects = [
            ['name' => 'Data Structures', 'category_name' => 'Computer Science'],
            ['name' => 'Algorithms', 'category_name' => 'Computer Science'],
            ['name' => 'Operating Systems', 'category_name' => 'Computer Science'],
            ['name' => 'Database Management', 'category_name' => 'Computer Science'],
            ['name' => 'Computer Networks', 'category_name' => 'Computer Science'],
            ['name' => 'Software Engineering', 'category_name' => 'Computer Science'],
            ['name' => 'Artificial Intelligence', 'category_name' => 'Computer Science'],
            ['name' => 'Machine Learning', 'category_name' => 'Computer Science'],
            ['name' => 'Web Development', 'category_name' => 'Computer Science'],
            ['name' => 'Object Oriented Programming', 'category_name' => 'Computer Science'],
            ['name' => 'Classical Mechanics', 'category_name' => 'Physics'],
            ['name' => 'Quantum Physics', 'category_name' => 'Physics'],
            ['name' => 'Electromagnetism', 'category_name' => 'Physics'],
            ['name' => 'Calculus', 'category_name' => 'Mathematics'],
            ['name' => 'Linear Algebra', 'category_name' => 'Mathematics'],
            ['name' => 'Statistics', 'category_name' => 'Mathematics'],
        ];

        foreach ($subjects as $subjData) {
            $category = Category::where('name', $subjData['category_name'])->first();
            Subject::firstOrCreate([
                'name' => $subjData['name'],
                'category_id' => $category->id,
            ]);
        }

        // Create Books
        $books = [
            [
                'title' => 'Introduction to Algorithms',
                'isbn' => '978-0262033848',
                'author_name' => 'Cormen, Thomas H.',
                'publisher_name' => 'MIT Press',
                'category_name' => 'Computer Science',
                'subject_name' => 'Algorithms',
                'description' => 'Comprehensive introduction to algorithms',
                'edition' => '3rd',
                'publication_year' => 2009,
                'language' => 'English',
                'total_copies' => 5,
                'shelf_number' => 'A1',
                'rack_number' => 'R1',
            ],
            [
                'title' => 'Computer Networks',
                'isbn' => '978-0132126953',
                'author_name' => 'Tanenbaum, Andrew S.',
                'publisher_name' => 'Pearson Education',
                'category_name' => 'Computer Science',
                'subject_name' => 'Computer Networks',
                'description' => 'Comprehensive guide to computer networks',
                'edition' => '5th',
                'publication_year' => 2010,
                'language' => 'English',
                'total_copies' => 4,
                'shelf_number' => 'A2',
                'rack_number' => 'R1',
            ],
            [
                'title' => 'Operating System Concepts',
                'isbn' => '978-1118063330',
                'author_name' => 'Silberschatz, Abraham',
                'publisher_name' => 'Wiley',
                'category_name' => 'Computer Science',
                'subject_name' => 'Operating Systems',
                'description' => 'Essential concepts of operating systems',
                'edition' => '9th',
                'publication_year' => 2012,
                'language' => 'English',
                'total_copies' => 6,
                'shelf_number' => 'A3',
                'rack_number' => 'R2',
            ],
            [
                'title' => 'Database System Concepts',
                'isbn' => '978-0073523323',
                'author_name' => 'Navathe, Shamkant B.',
                'publisher_name' => 'McGraw Hill',
                'category_name' => 'Computer Science',
                'subject_name' => 'Database Management',
                'description' => 'Fundamental concepts of database systems',
                'edition' => '6th',
                'publication_year' => 2015,
                'language' => 'English',
                'total_copies' => 5,
                'shelf_number' => 'A4',
                'rack_number' => 'R2',
            ],
            [
                'title' => 'Design Patterns',
                'isbn' => '978-0201633610',
                'author_name' => 'Gamma, Erich',
                'publisher_name' => 'Addison-Wesley',
                'category_name' => 'Computer Science',
                'subject_name' => 'Software Engineering',
                'description' => 'Elements of Reusable Object-Oriented Software',
                'edition' => '1st',
                'publication_year' => 1994,
                'language' => 'English',
                'total_copies' => 4,
                'shelf_number' => 'A5',
                'rack_number' => 'R3',
            ],
            [
                'title' => 'Clean Code',
                'isbn' => '978-0132350884',
                'author_name' => 'Martin, Robert C.',
                'publisher_name' => 'Prentice Hall',
                'category_name' => 'Computer Science',
                'subject_name' => 'Software Engineering',
                'description' => 'A Handbook of Agile Software Craftsmanship',
                'edition' => '1st',
                'publication_year' => 2008,
                'language' => 'English',
                'total_copies' => 6,
                'shelf_number' => 'A5',
                'rack_number' => 'R3',
            ],
            [
                'title' => 'The Pragmatic Programmer',
                'isbn' => '978-0135957059',
                'author_name' => 'Hunt, Andrew',
                'publisher_name' => 'Addison-Wesley',
                'category_name' => 'Computer Science',
                'subject_name' => 'Software Engineering',
                'description' => 'Your Journey To Mastery',
                'edition' => '2nd',
                'publication_year' => 2019,
                'language' => 'English',
                'total_copies' => 5,
                'shelf_number' => 'A5',
                'rack_number' => 'R3',
            ],
            [
                'title' => 'Effective Java',
                'isbn' => '978-0134685991',
                'author_name' => 'Bloch, Joshua',
                'publisher_name' => 'Addison-Wesley',
                'category_name' => 'Computer Science',
                'subject_name' => 'Object Oriented Programming',
                'description' => 'Best practices for Java programming',
                'edition' => '3rd',
                'publication_year' => 2017,
                'language' => 'English',
                'total_copies' => 4,
                'shelf_number' => 'A6',
                'rack_number' => 'R4',
            ],
            [
                'title' => 'Computer Networking: A Top-Down Approach',
                'isbn' => '978-0133594140',
                'author_name' => 'Kurose, James F.',
                'publisher_name' => 'Pearson Education',
                'category_name' => 'Computer Science',
                'subject_name' => 'Computer Networks',
                'description' => 'Top-down approach to networking',
                'edition' => '7th',
                'publication_year' => 2016,
                'language' => 'English',
                'total_copies' => 5,
                'shelf_number' => 'A2',
                'rack_number' => 'R1',
            ],
            [
                'title' => 'Data Structures and Algorithms in C++',
                'isbn' => '978-0470383278',
                'author_name' => 'Forouzan, Behrouz A.',
                'publisher_name' => 'McGraw Hill',
                'category_name' => 'Computer Science',
                'subject_name' => 'Data Structures',
                'description' => 'Comprehensive guide to data structures',
                'edition' => '2nd',
                'publication_year' => 2013,
                'language' => 'English',
                'total_copies' => 6,
                'shelf_number' => 'A1',
                'rack_number' => 'R1',
            ],
        ];

        foreach ($books as $bookData) {
            $author = Author::where('name', $bookData['author_name'])->first();
            $publisher = Publisher::where('name', $bookData['publisher_name'])->first() ?? Publisher::first();
            $category = Category::where('name', $bookData['category_name'])->first();
            $subject = Subject::where('name', $bookData['subject_name'])->first();

            $book = Book::create([
                'title' => $bookData['title'],
                'isbn' => $bookData['isbn'],
                'author_id' => $author->id,
                'publisher_id' => $publisher->id,
                'category_id' => $category->id,
                'subject_id' => $subject->id,
                'description' => $bookData['description'],
                'edition' => $bookData['edition'],
                'publication_year' => $bookData['publication_year'],
                'language' => $bookData['language'],
                'total_copies' => $bookData['total_copies'],
                'available_copies' => $bookData['total_copies'],
                'shelf_number' => $bookData['shelf_number'],
                'rack_number' => $bookData['rack_number'],
                'location' => "Shelf {$bookData['shelf_number']}, Rack {$bookData['rack_number']}",
                'status' => 'available',
            ]);

            // Create book copies
            for ($i = 1; $i <= $bookData['total_copies']; $i++) {
                BookCopy::create([
                    'book_id' => $book->id,
                    'barcode' => "BK{$book->id}-CPY$i",
                    'copy_number' => $i,
                    'shelf' => $bookData['shelf_number'],
                    'rack' => $bookData['rack_number'],
                    'status' => 'available',
                ]);
            }
        }

        // Create Digital Resources
        $digitalResources = [
            [
                'title' => 'Introduction to Machine Learning',
                'description' => 'Comprehensive lecture notes on ML fundamentals',
                'resource_type' => 'lecture_notes',
                'category_name' => 'Computer Science',
                'department_name' => 'Computer Science',
            ],
            [
                'title' => 'Database Normalization Techniques',
                'description' => 'Research paper on advanced normalization',
                'resource_type' => 'research_paper',
                'category_name' => 'Computer Science',
                'department_name' => 'Computer Science',
            ],
            [
                'title' => 'Quantum Computing Basics',
                'description' => 'E-book on quantum computing fundamentals',
                'resource_type' => 'e_book',
                'category_name' => 'Physics',
                'department_name' => 'Physics',
            ],
            [
                'title' => 'Previous Year Question Papers - BCA',
                'description' => 'Collection of past exam papers',
                'resource_type' => 'question_paper',
                'category_name' => 'Computer Science',
                'department_name' => 'Computer Science',
            ],
            [
                'title' => 'Software Engineering Best Practices',
                'description' => 'Presentation on modern SE practices',
                'resource_type' => 'presentation',
                'category_name' => 'Computer Science',
                'department_name' => 'Computer Science',
            ],
        ];

        $users = User::whereHas('role', fn($q) => $q->where('name', 'staff'))->get();
        
        foreach ($digitalResources as $resData) {
            $category = Category::where('name', $resData['category_name'])->first();
            $department = \App\Models\Department::where('name', $resData['department_name'])->first();
            $contributor = $users->random();

            DigitalResource::create([
                'title' => $resData['title'],
                'description' => $resData['description'],
                'author' => $contributor->name,
                'contributor_id' => $contributor->id,
                'category_id' => $category->id,
                'department_id' => $department->id,
                'resource_type' => $resData['resource_type'],
                'access_level' => 'public',
                'download_permission' => true,
                'status' => 'approved',
                'approved_by' => User::whereHas('role', fn($q) => $q->where('name', 'admin'))->first()->id,
                'approved_at' => now(),
            ]);
        }

        // Create some borrowings
        $students = User::whereHas('role', fn($q) => $q->where('name', 'student'))->take(10)->get();
        $books_list = Book::all();
        
        foreach ($students->take(5) as $student) {
            $book = $books_list->random();
            if ($book->available_copies > 0) {
                $dueDate = Carbon::now()->addDays(14);
                
                $borrowing = Borrowing::create([
                    'user_id' => $student->id,
                    'book_copy_id' => $book->copies()->where('status', 'available')->first()->id,
                    'issue_date' => Carbon::now(),
                    'due_date' => $dueDate,
                    'status' => 'borrowed',
                    'issued_by' => User::whereHas('role', fn($q) => $q->where('name', 'librarian'))->first()->id,
                ]);

                // Update book copy and book availability
                $borrowing->bookCopy->update(['status' => 'borrowed']);
                $book->decrement('available_copies');
            }
        }

        // Create a reservation
        $student = $students->first();
        $book = Book::where('available_copies', 0)->first() ?? Book::first();
        if ($book && $student) {
            Reservation::create([
                'user_id' => $student->id,
                'book_id' => $book->id,
                'reservation_date' => now(),
                'status' => 'pending',
                'queue_position' => 1,
            ]);
        }

        $this->command->info('Library data seeded successfully!');
        $this->command->info("Created: " . Book::count() . " books");
        $this->command->info("Created: " . BookCopy::count() . " book copies");
        $this->command->info("Created: " . DigitalResource::count() . " digital resources");
    }
}
