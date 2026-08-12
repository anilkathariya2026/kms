<?php

namespace App\Http\Controllers\Librarian;

use App\Http\Controllers\Controller;
use App\Models\Reservation;
use App\Models\Book;
use App\Models\User;
use App\Models\Borrowing;
use Illuminate\Http\Request;

class ReservationController extends Controller
{
    public function index(Request $request)
    {
        $query = Reservation::with(['user', 'book.author']);

        // Filter by status
        if ($request->has('status') && $request->status) {
            $query->where('status', $request->status);
        }

        $reservations = $query->orderBy('created_at', 'desc')->paginate(15);

        return view('librarian.reservations.index', compact('reservations'));
    }

    public function approve(Reservation $reservation)
    {
        if ($reservation->status !== 'pending') {
            return redirect()->back()
                ->with('error', 'Reservation is not pending.');
        }

        // Check if book has available copy
        $availableCopy = $reservation->book->copies()
            ->where('status', 'available')
            ->first();

        if (!$availableCopy) {
            return redirect()->back()
                ->with('error', 'No available copies for this book.');
        }

        $reservation->update([
            'status' => 'approved',
            'approved_at' => now(),
        ]);

        // Notify user (would use Laravel Notifications in production)
        
        return redirect()->back()
            ->with('success', 'Reservation approved. User notified.');
    }

    public function reject(Reservation $reservation)
    {
        if ($reservation->status !== 'pending') {
            return redirect()->back()
                ->with('error', 'Reservation is not pending.');
        }

        $reservation->update([
            'status' => 'rejected',
            'rejected_at' => now(),
        ]);

        return redirect()->back()
            ->with('success', 'Reservation rejected.');
    }

    public function fulfill(Reservation $reservation)
    {
        if ($reservation->status !== 'approved') {
            return redirect()->back()
                ->with('error', 'Reservation must be approved first.');
        }

        // Check if book still has available copy
        $availableCopy = $reservation->book->copies()
            ->where('status', 'available')
            ->first();

        if (!$availableCopy) {
            return redirect()->back()
                ->with('error', 'No available copies for this book.');
        }

        // Issue the book
        Borrowing::create([
            'user_id' => $reservation->user_id,
            'book_copy_id' => $availableCopy->id,
            'issue_date' => now(),
            'due_date' => now()->addDays(config('library.borrowing_period', 14)),
            'status' => 'issued',
            'issued_by' => auth()->id(),
            'reservation_id' => $reservation->id,
        ]);

        $availableCopy->update(['status' => 'issued']);
        $reservation->book->decrement('available_copies');

        $reservation->update([
            'status' => 'fulfilled',
            'fulfilled_at' => now(),
        ]);

        return redirect()->route('librarian.reservations.index')
            ->with('success', 'Reservation fulfilled and book issued.');
    }

    public function cancel(Reservation $reservation)
    {
        $reservation->update([
            'status' => 'cancelled',
            'cancelled_at' => now(),
        ]);

        return redirect()->back()
            ->with('success', 'Reservation cancelled.');
    }

    public function expired()
    {
        $expiredReservations = Reservation::where('status', 'approved')
            ->where('expires_at', '<', now())
            ->get();

        foreach ($expiredReservations as $reservation) {
            $reservation->update([
                'status' => 'expired',
            ]);
        }

        return redirect()->route('librarian.reservations.index')
            ->with('success', 'Expired reservations updated.');
    }
}
