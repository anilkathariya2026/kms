<?php

namespace App\Http\Controllers\Librarian;

use App\Http\Controllers\Controller;
use App\Models\Fine;
use App\Models\Borrowing;
use Illuminate\Http\Request;

class FineController extends Controller
{
    public function index(Request $request)
    {
        $query = Fine::with(['borrowing.user', 'borrowing.bookCopy.book']);

        // Filter by status
        if ($request->has('status') && $request->status) {
            $query->where('status', $request->status);
        }

        $fines = $query->orderBy('created_at', 'desc')->paginate(15);

        return view('librarian.fines.index', compact('fines'));
    }

    public function show(Fine $fine)
    {
        $fine->load(['borrowing.user', 'borrowing.bookCopy.book']);
        return view('librarian.fines.show', compact('fine'));
    }

    public function recordPayment(Request $request, Fine $fine)
    {
        if ($fine->status === 'paid') {
            return redirect()->back()
                ->with('error', 'Fine already paid.');
        }

        $request->validate([
            'amount_paid' => 'required|numeric|min:' . $fine->amount . '|max:' . $fine->amount,
            'payment_method' => 'required|in:cash,card,online,other',
            'notes' => 'nullable|string|max:500',
        ]);

        $fine->update([
            'status' => 'paid',
            'amount_paid' => $request->amount_paid,
            'payment_method' => $request->payment_method,
            'payment_notes' => $request->notes,
            'paid_at' => now(),
            'paid_to' => auth()->id(),
        ]);

        return redirect()->route('librarian.fines.index')
            ->with('success', 'Payment recorded successfully.');
    }

    public function waive(Request $request, Fine $fine)
    {
        $request->validate([
            'waiver_reason' => 'required|string|max:500',
        ]);

        $fine->update([
            'status' => 'waived',
            'waiver_reason' => $request->waiver_reason,
            'waived_by' => auth()->id(),
            'waived_at' => now(),
        ]);

        return redirect()->back()
            ->with('success', 'Fine waived successfully.');
    }

    public function overdueBooks()
    {
        $overdue = Borrowing::with(['user', 'bookCopy.book'])
            ->whereNull('returned_at')
            ->where('due_date', '<', now())
            ->orderBy('due_date')
            ->get();

        return view('librarian.fines.overdue', compact('overdue'));
    }
}
