<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Borrowing;
use App\Models\Book;
use App\Models\User;
use App\Models\Fine;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class BorrowingController extends Controller
{
    public function index(Request $request)
    {
        $query = Borrowing::with(['user', 'bookCopy.book', 'return']);

        // Search
        if ($request->has('search') && $request->search) {
            $query->whereHas('user', function($q) use ($request) {
                $q->where('name', 'like', '%' . $request->search . '%');
            });
        }

        // Filter by status
        if ($request->has('status') && $request->status) {
            if ($request->status === 'active') {
                $query->whereNull('returned_at');
            } elseif ($request->status === 'returned') {
                $query->whereNotNull('returned_at');
            } elseif ($request->status === 'overdue') {
                $query->whereNull('returned_at')
                      ->where('due_date', '<', now());
            }
        }

        $borrowings = $query->orderBy('created_at', 'desc')->paginate(15);

        return view('admin.borrowings.index', compact('borrowings'));
    }

    public function show(Borrowing $borrowing)
    {
        $borrowing->load(['user', 'bookCopy.book', 'return', 'fine']);
        return view('admin.borrowings.show', compact('borrowing'));
    }

    public function renew(Borrowing $borrowing)
    {
        // Check if renewal is allowed
        if ($borrowing->renewal_count >= config('library.max_renewals', 2)) {
            return redirect()->back()
                ->with('error', 'Maximum renewal limit reached.');
        }

        if ($borrowing->bookCopy->reservations()->where('status', 'pending')->exists()) {
            return redirect()->back()
                ->with('error', 'Cannot renew - book has pending reservations.');
        }

        $borrowing->update([
            'due_date' => $borrowing->due_date->addDays(config('library.borrowing_period', 14)),
            'renewal_count' => $borrowing->renewal_count + 1,
        ]);

        return redirect()->back()
            ->with('success', 'Book renewed successfully.');
    }
}
