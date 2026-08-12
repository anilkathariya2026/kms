<?php

namespace App\Http\Controllers\Staff;

use App\Http\Controllers\Controller;
use App\Models\Book;
use App\Models\Borrowing;
use App\Models\Reservation;
use App\Models\BookCopy;
use Illuminate\Http\Request;

class BookController extends Controller
{
    public function index(Request $request)
    {
        $query = Book::with(['author', 'publisher', 'category'])
            ->where('status', 'active');

        // Search
        if ($request->has('search') && $request->search) {
            $query->where(function($q) use ($request) {
                $q->where('title', 'like', '%' . $request->search . '%')
                  ->orWhere('isbn', 'like', '%' . $request->search . '%')
                  ->orWhere('subject', 'like', '%' . $request->search . '%');
            });
        }

        // Filter by category
        if ($request->has('category') && $request->category) {
            $query->where('category_id', $request->category);
        }

        $books = $query->orderBy('created_at', 'desc')->paginate(12);

        return view('staff.books.index', compact('books'));
    }

    public function show(Book $book)
    {
        $book->load(['author', 'publisher', 'category', 'copies' => function($q) {
            $q->where('status', 'available')->limit(5);
        }]);

        $availableCopies = $book->copies()->where('status', 'available')->count();

        return view('staff.books.show', compact('book', 'availableCopies'));
    }

    public function borrow(Request $request, Book $book)
    {
        $validated = $request->validate([
            'book_copy_id' => 'required|exists:book_copies,id',
        ]);

        $bookCopy = BookCopy::findOrFail($validated['book_copy_id']);

        if ($bookCopy->status !== 'available') {
            return redirect()->back()
                ->with('error', 'Selected book copy is not available.');
        }

        // Check borrowing limits
        $activeBorrowings = auth()->user()->borrowings()->whereNull('returned_at')->count();
        $maxBooks = config('library.max_books', 5);

        if ($activeBorrowings >= $maxBooks) {
            return redirect()->back()
                ->with('error', "You have reached maximum borrowing limit of {$maxBooks} books.");
        }

        Borrowing::create([
            'user_id' => auth()->id(),
            'book_copy_id' => $bookCopy->id,
            'issue_date' => now(),
            'due_date' => now()->addDays(config('library.borrowing_period', 14)),
            'status' => 'issued',
        ]);

        $bookCopy->update(['status' => 'issued']);
        $book->decrement('available_copies');

        return redirect()->route('staff.borrowings.index')
            ->with('success', 'Book borrowed successfully.');
    }

    public function reserve(Request $request, Book $book)
    {
        // Check if already has active reservation for this book
        $existingReservation = Reservation::where('user_id', auth()->id())
            ->where('book_id', $book->id)
            ->where('status', 'pending')
            ->first();

        if ($existingReservation) {
            return redirect()->back()
                ->with('error', 'You already have a pending reservation for this book.');
        }

        Reservation::create([
            'user_id' => auth()->id(),
            'book_id' => $book->id,
            'status' => 'pending',
            'reservation_date' => now(),
        ]);

        return redirect()->back()
            ->with('success', 'Reservation submitted successfully.');
    }
}
