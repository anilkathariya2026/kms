<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Report;
use App\Models\Borrowing;
use App\Models\User;
use App\Models\Book;
use App\Models\DigitalResource;
use App\Models\Fine;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ReportController extends Controller
{
    public function index()
    {
        return view('admin.reports.index');
    }

    public function borrowingReport(Request $request)
    {
        $query = Borrowing::with(['user', 'bookCopy.book']);

        if ($request->has('start_date') && $request->start_date) {
            $query->where('issue_date', '>=', $request->start_date);
        }

        if ($request->has('end_date') && $request->end_date) {
            $query->where('issue_date', '<=', $request->end_date);
        }

        if ($request->has('status')) {
            if ($request->status === 'active') {
                $query->whereNull('returned_at');
            } elseif ($request->status === 'returned') {
                $query->whereNotNull('returned_at');
            }
        }

        $borrowings = $query->orderBy('issue_date', 'desc')->get();

        return view('admin.reports.borrowing', compact('borrowings'));
    }

    public function userReport(Request $request)
    {
        $query = User::with(['role', 'department']);

        if ($request->has('role') && $request->role) {
            $query->where('role_id', $request->role);
        }

        if ($request->has('status') && $request->status !== '') {
            $query->where('status', $request->status);
        }

        $users = $query->orderBy('created_at', 'desc')->get();

        return view('admin.reports.user', compact('users'));
    }

    public function bookInventoryReport()
    {
        $books = Book::with(['author', 'publisher', 'category', 'copies'])
            ->orderBy('title')
            ->get();

        return view('admin.reports.book-inventory', compact('books'));
    }

    public function fineReport(Request $request)
    {
        $query = Fine::with(['borrowing.user', 'borrowing.bookCopy.book']);

        if ($request->has('start_date') && $request->start_date) {
            $query->where('created_at', '>=', $request->start_date);
        }

        if ($request->has('end_date') && $request->end_date) {
            $query->where('created_at', '<=', $request->end_date);
        }

        if ($request->has('status') && $request->status) {
            $query->where('status', $request->status);
        }

        $fines = $query->orderBy('created_at', 'desc')->get();
        $totalCollected = $fines->where('status', 'paid')->sum('amount');
        $totalPending = $fines->where('status', 'pending')->sum('amount');

        return view('admin.reports.fine', compact('fines', 'totalCollected', 'totalPending'));
    }

    public function resourceUsageReport(Request $request)
    {
        $query = DigitalResource::with(['category', 'department']);

        if ($request->has('category') && $request->category) {
            $query->where('category_id', $request->category);
        }

        $resources = $query->orderBy('download_count', 'desc')->get();

        return view('admin.reports.resource-usage', compact('resources'));
    }

    public function export(Request $request, $type)
    {
        // Implementation for exporting to CSV/PDF
        // This would use a package like dompdf or maatwebsite/excel
        
        return redirect()->back()
            ->with('info', 'Export functionality would be implemented here.');
    }
}
