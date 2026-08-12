<?php

namespace App\Http\Controllers\Staff;

use App\Http\Controllers\Controller;
use App\Models\Borrowing;
use App\Models\Reservation;
use Illuminate\Http\Request;

class BorrowingController extends Controller
{
    public function index()
    {
        $borrowings = Borrowing::with(['bookCopy.book.author'])
            ->where('user_id', auth()->id())
            ->orderBy('created_at', 'desc')
            ->paginate(15);

        return view('staff.borrowings.index', compact('borrowings'));
    }

    public function active()
    {
        $borrowings = Borrowing::with(['bookCopy.book'])
            ->where('user_id', auth()->id())
            ->whereNull('returned_at')
            ->orderBy('due_date')
            ->get();

        return view('staff.borrowings.active', compact('borrowings'));
    }

    public function history()
    {
        $borrowings = Borrowing::with(['bookCopy.book'])
            ->where('user_id', auth()->id())
            ->whereNotNull('returned_at')
            ->orderBy('returned_at', 'desc')
            ->paginate(15);

        return view('staff.borrowings.history', compact('borrowings'));
    }

    public function renew(Request $request, Borrowing $borrowing)
    {
        // Only allow renew own borrowings
        if ($borrowing->user_id !== auth()->id()) {
            abort(403, 'You can only renew your own borrowings.');
        }

        if ($borrowing->returned_at) {
            return redirect()->back()
                ->with('error', 'Cannot renew returned books.');
        }

        if ($borrowing->renewal_count >= config('library.max_renewals', 2)) {
            return redirect()->back()
                ->with('error', 'Maximum renewal limit reached.');
        }

        // Check if book has pending reservations
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
