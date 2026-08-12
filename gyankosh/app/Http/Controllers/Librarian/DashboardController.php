<?php

namespace App\Http\Controllers\Librarian;

use App\Http\Controllers\Controller;
use App\Models\Book;
use App\Models\BookCopy;
use App\Models\Borrowing;
use App\Models\Reservation;
use App\Models\DigitalResource;
use App\Models\Fine;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index()
    {
        // Statistics
        $totalBooks = Book::count();
        $availableBooks = BookCopy::where('status', 'Available')->count();
        $borrowedBooks = BookCopy::where('status', 'Borrowed')->count();
        $reservedBooks = BookCopy::where('status', 'Reserved')->count();
        $overdueBooks = Borrowing::where('status', 'issued')
            ->where('due_date', '<', now())
            ->count();
        
        $returnedToday = Borrowing::whereDate('return_date', today())->count();
        $pendingRequests = Borrowing::where('status', 'pending')->count();
        
        $totalFines = Fine::sum('amount');
        $unpaidFines = Fine::whereNull('paid_at')->sum('amount');

        // Recent transactions
        $recentTransactions = DB::table('borrowings')
            ->join('users', 'borrowings.user_id', '=', 'users.id')
            ->join('book_copies', 'borrowings.book_copy_id', '=', 'book_copies.id')
            ->join('books', 'book_copies.book_id', '=', 'books.id')
            ->select('borrowings.*', 'users.name as user_name', 'books.title as book_title')
            ->orderBy('borrowings.created_at', 'desc')
            ->limit(10)
            ->get();

        return view('librarian.dashboard', compact(
            'totalBooks',
            'availableBooks',
            'borrowedBooks',
            'reservedBooks',
            'overdueBooks',
            'returnedToday',
            'pendingRequests',
            'totalFines',
            'unpaidFines',
            'recentTransactions'
        ));
    }
}
