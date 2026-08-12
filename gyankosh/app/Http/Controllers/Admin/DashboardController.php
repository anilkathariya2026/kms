<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Book;
use App\Models\BookCopy;
use App\Models\Borrowing;
use App\Models\DigitalResource;
use App\Models\Department;
use App\Models\Faculty;
use App\Models\Role;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index()
    {
        // Statistics
        $totalUsers = User::count();
        $totalStudents = User::whereHas('role', function($q) { $q->where('name', 'Student'); })->count();
        $totalStaff = User::whereHas('role', function($q) { $q->where('name', 'Staff'); })->count();
        $totalLibrarians = User::whereHas('role', function($q) { $q->where('name', 'Librarian'); })->count();
        
        $totalBooks = Book::count();
        $availableBooks = BookCopy::where('status', 'Available')->count();
        $borrowedBooks = BookCopy::where('status', 'Borrowed')->count();
        $overdueBooks = Borrowing::where('status', 'issued')
            ->where('due_date', '<', now())
            ->count();
        
        $digitalResources = DigitalResource::where('status', 'approved')->count();
        $pendingApprovals = DigitalResource::where('status', 'pending')->count();
        
        $activeUsers = User::where('status', true)->count();
        
        $totalFines = DB::table('fines')
            ->whereNull('paid_at')
            ->sum('amount');

        // Recent activities
        $recentActivities = DB::table('activity_logs')
            ->orderBy('created_at', 'desc')
            ->limit(10)
            ->get();

        return view('admin.dashboard', compact(
            'totalUsers',
            'totalStudents',
            'totalStaff',
            'totalLibrarians',
            'totalBooks',
            'availableBooks',
            'borrowedBooks',
            'overdueBooks',
            'digitalResources',
            'pendingApprovals',
            'activeUsers',
            'totalFines',
            'recentActivities'
        ));
    }
}
