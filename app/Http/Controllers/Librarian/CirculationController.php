<?php

namespace App\Http\Controllers\Librarian;

use App\Http\Controllers\Controller;
use App\Models\Borrowing;
use App\Models\BookCopy;
use App\Models\User;
use App\Models\ReturnBook;
use App\Models\Fine;
use App\Models\Reservation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class CirculationController extends Controller
{
    public function issueForm()
    {
        return view('librarian.circulation.issue');
    }

    public function searchUser(Request $request)
    {
        $query = User::with(['role', 'department']);

        if ($request->has('search') && $request->search) {
            $query->where(function($q) use ($request) {
                $q->where('name', 'like', '%' . $request->search . '%')
                  ->orWhere('email', 'like', '%' . $request->search . '%')
                  ->orWhereHas('role', function($qr) use ($request) {
                      $qr->where('name', 'like', '%' . $request->search . '%');
                  });
            });
        }

        $users = $query->limit(10)->get();
        return response()->json($users);
    }

    public function searchBook(Request $request)
    {
        $query = BookCopy::with(['book.author', 'book.publisher', 'book.category'])
            ->where('status', 'available');

        if ($request->has('barcode') && $request->barcode) {
            $query->where('barcode', $request->barcode);
        }

        if ($request->has('search') && $request->search) {
            $query->whereHas('book', function($q) use ($request) {
                $q->where('title', 'like', '%' . $request->search . '%')
                  ->orWhere('isbn', 'like', '%' . $request->search . '%');
            });
        }

        $copies = $query->limit(10)->get();
        return response()->json($copies);
    }

    public function issue(Request $request)
    {
        $validated = $request->validate([
            'user_id' => 'required|exists:users,id',
            'book_copy_id' => 'required|exists:book_copies,id',
            'due_date' => 'required|date|after:today',
        ]);

        $user = User::findOrFail($validated['user_id']);
        $bookCopy = BookCopy::findOrFail($validated['book_copy_id']);

        // Check if user is active
        if ($user->status !== 'active') {
            return redirect()->back()
                ->with('error', 'User account is not active.');
        }

        // Check if book copy is available
        if ($bookCopy->status !== 'available') {
            return redirect()->back()
                ->with('error', 'Book copy is not available.');
        }

        // Check borrowing limits
        $activeBorrowings = $user->borrowings()->whereNull('returned_at')->count();
        $maxBooks = config('library.max_books', 5);
        
        if ($activeBorrowings >= $maxBooks) {
            return redirect()->back()
                ->with('error', "User has reached maximum borrowing limit of {$maxBooks} books.");
        }

        DB::transaction(function () use ($user, $bookCopy, $validated) {
            // Create borrowing record
            $borrowing = Borrowing::create([
                'user_id' => $user->id,
                'book_copy_id' => $bookCopy->id,
                'issue_date' => now(),
                'due_date' => $validated['due_date'],
                'status' => 'issued',
                'issued_by' => auth()->id(),
            ]);

            // Update book copy status
            $bookCopy->update([
                'status' => 'issued',
            ]);

            // Update book available copies
            $bookCopy->book->decrement('available_copies');

            // Cancel any reservation for this user and book
            Reservation::where('user_id', $user->id)
                ->where('book_id', $bookCopy->book_id)
                ->where('status', 'pending')
                ->update(['status' => 'fulfilled']);
        });

        return redirect()->route('librarian.circulation.issue')
            ->with('success', 'Book issued successfully.');
    }

    public function returnForm()
    {
        return view('librarian.circulation.return');
    }

    public function processReturn(Request $request)
    {
        $validated = $request->validate([
            'book_copy_id' => 'required|exists:book_copies,id',
        ]);

        $bookCopy = BookCopy::with(['book', 'borrowing' => function($q) {
            $q->whereNull('returned_at')->latest();
        }])->findOrFail($validated['book_copy_id']);

        if (!$bookCopy->borrowing) {
            return redirect()->back()
                ->with('error', 'No active borrowing found for this book copy.');
        }

        $borrowing = $bookCopy->borrowing;
        $returnDate = now();
        $fineAmount = 0;

        // Calculate fine if overdue
        if ($returnDate->gt($borrowing->due_date)) {
            $overdueDays = $returnDate->diffInDays($borrowing->due_date);
            $fineRate = config('library.fine_per_day', 1.00);
            $fineAmount = $overdueDays * $fineRate;
        }

        DB::transaction(function () use ($borrowing, $bookCopy, $returnDate, $fineAmount) {
            // Create return record
            ReturnBook::create([
                'borrowing_id' => $borrowing->id,
                'return_date' => $returnDate,
                'condition' => 'good',
                'returned_to' => auth()->id(),
            ]);

            // Update borrowing record
            $borrowing->update([
                'returned_at' => $returnDate,
                'status' => 'returned',
            ]);

            // Update book copy status
            $bookCopy->update([
                'status' => 'available',
            ]);

            // Update book available copies
            $bookCopy->book->increment('available_copies');

            // Create fine if applicable
            if ($fineAmount > 0) {
                Fine::create([
                    'borrowing_id' => $borrowing->id,
                    'amount' => $fineAmount,
                    'reason' => 'Overdue return',
                    'status' => 'pending',
                ]);
            }
        });

        return redirect()->route('librarian.circulation.return')
            ->with('success', 'Book returned successfully.' . ($fineAmount > 0 ? " Fine: \${$fineAmount}" : ''));
    }

    public function renewals()
    {
        $borrowings = Borrowing::with(['user', 'bookCopy.book'])
            ->whereNull('returned_at')
            ->where('renewal_count', '<', config('library.max_renewals', 2))
            ->orderBy('due_date')
            ->paginate(15);

        return view('librarian.circulation.renewals', compact('borrowings'));
    }

    public function processRenewal(Request $request, Borrowing $borrowing)
    {
        if ($borrowing->renewal_count >= config('library.max_renewals', 2)) {
            return redirect()->back()
                ->with('error', 'Maximum renewal limit reached.');
        }

        if ($borrowing->bookCopy->reservations()->where('status', 'pending')->exists()) {
            return redirect()->back()
                ->with('error', 'Cannot renew - book has pending reservations.');
        }

        $extensionDays = config('library.borrowing_period', 14);
        
        $borrowing->update([
            'due_date' => $borrowing->due_date->addDays($extensionDays),
            'renewal_count' => $borrowing->renewal_count + 1,
        ]);

        return redirect()->back()
            ->with('success', 'Book renewed successfully. New due date: ' . $borrowing->due_date->format('Y-m-d'));
    }
}
