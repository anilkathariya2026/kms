<?php

namespace App\Http\Controllers\Staff;

use App\Http\Controllers\Controller;
use App\Models\Book;
use App\Models\BookCopy;
use App\Models\Borrowing;
use App\Models\DigitalResource;
use App\Models\Reservation;
use App\Models\Favorite;
use App\Models\Bookmark;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        
        // Statistics
        $myBorrowings = Borrowing::where('user_id', $user->id)
            ->where('status', 'issued')
            ->count();
        
        $myReservations = Reservation::where('user_id', $user->id)
            ->where('status', 'pending')
            ->count();
        
        $myContributions = DigitalResource::where('contributor_id', $user->id)->count();
        $pendingContributions = DigitalResource::where('contributor_id', $user->id)
            ->where('status', 'pending')
            ->count();
        $approvedContributions = DigitalResource::where('contributor_id', $user->id)
            ->where('status', 'approved')
            ->count();
        
        $myFavorites = Favorite::where('user_id', $user->id)->count();
        $myBookmarks = Bookmark::where('user_id', $user->id)->count();
        
        // Due dates
        $dueSoon = Borrowing::where('user_id', $user->id)
            ->where('status', 'issued')
            ->whereBetween('due_date', [now(), now()->addDays(3)])
            ->count();
        
        $overdue = Borrowing::where('user_id', $user->id)
            ->where('status', 'issued')
            ->where('due_date', '<', now())
            ->count();

        // Recent borrowings
        $recentBorrowings = DB::table('borrowings')
            ->join('book_copies', 'borrowings.book_copy_id', '=', 'book_copies.id')
            ->join('books', 'book_copies.book_id', '=', 'books.id')
            ->where('borrowings.user_id', $user->id)
            ->select('borrowings.*', 'books.title as book_title', 'book_copies.barcode')
            ->orderBy('borrowings.created_at', 'desc')
            ->limit(5)
            ->get();

        return view('staff.dashboard', compact(
            'myBorrowings',
            'myReservations',
            'myContributions',
            'pendingContributions',
            'approvedContributions',
            'myFavorites',
            'myBookmarks',
            'dueSoon',
            'overdue',
            'recentBorrowings'
        ));
    }
}
