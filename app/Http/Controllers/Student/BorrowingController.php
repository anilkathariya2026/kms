<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\Borrowing;
use App\Models\Reservation;
use App\Models\Fine;
use Illuminate\Http\Request;

class BorrowingController extends Controller
{
    public function index()
    {
        $borrowings = Borrowing::with(['bookCopy.book.author'])
            ->where('user_id', auth()->id())
            ->orderBy('created_at', 'desc')
            ->paginate(15);

        return view('student.borrowings.index', compact('borrowings'));
    }

    public function active()
    {
        $borrowings = Borrowing::with(['bookCopy.book'])
            ->where('user_id', auth()->id())
            ->whereNull('returned_at')
            ->orderBy('due_date')
            ->get();

        $overdue = $borrowings->filter(function($b) {
            return now()->gt($b->due_date);
        });

        return view('student.borrowings.active', compact('borrowings', 'overdue'));
    }

    public function history()
    {
        $borrowings = Borrowing::with(['bookCopy.book'])
            ->where('user_id', auth()->id())
            ->whereNotNull('returned_at')
            ->orderBy('returned_at', 'desc')
            ->paginate(15);

        return view('student.borrowings.history', compact('borrowings'));
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

    public function fines()
    {
        $fines = Fine::with(['borrowing.bookCopy.book'])
            ->whereHas('borrowing', function($q) {
                $q->where('user_id', auth()->id());
            })
            ->orderBy('created_at', 'desc')
            ->paginate(15);

        $totalPending = Fine::whereHas('borrowing', function($q) {
                $q->where('user_id', auth()->id());
            })
            ->where('status', 'pending')
            ->sum('amount');

        $totalPaid = Fine::whereHas('borrowing', function($q) {
                $q->where('user_id', auth()->id());
            })
            ->where('status', 'paid')
            ->sum('amount');

        return view('student.borrowings.fines', compact('fines', 'totalPending', 'totalPaid'));
    }

    public function reservations()
    {
        $reservations = Reservation::with(['book.author'])
            ->where('user_id', auth()->id())
            ->orderBy('created_at', 'desc')
            ->paginate(15);

        return view('student.borrowings.reservations', compact('reservations'));
    }

    public function cancelReservation(Reservation $reservation)
    {
        if ($reservation->user_id !== auth()->id()) {
            abort(403, 'You can only cancel your own reservations.');
        }

        if ($reservation->status !== 'pending') {
            return redirect()->back()
                ->with('error', 'Can only cancel pending reservations.');
        }

        $reservation->update([
            'status' => 'cancelled',
            'cancelled_at' => now(),
        ]);

        return redirect()->back()
            ->with('success', 'Reservation cancelled successfully.');
    }
}
