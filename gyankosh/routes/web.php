<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Admin\DashboardController as AdminDashboardController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\Admin\BookController as AdminBookController;
use App\Http\Controllers\Admin\ResourceController as AdminResourceController;
use App\Http\Controllers\Admin\BorrowingController as AdminBorrowingController;
use App\Http\Controllers\Admin\ReportController;
use App\Http\Controllers\Librarian\DashboardController as LibrarianDashboardController;
use App\Http\Controllers\Librarian\BookController as LibrarianBookController;
use App\Http\Controllers\Librarian\CirculationController;
use App\Http\Controllers\Librarian\ReservationController;
use App\Http\Controllers\Librarian\ResourceController as LibrarianResourceController;
use App\Http\Controllers\Librarian\FineController;
use App\Http\Controllers\Staff\DashboardController as StaffDashboardController;
use App\Http\Controllers\Staff\BookController as StaffBookController;
use App\Http\Controllers\Staff\ResourceController as StaffResourceController;
use App\Http\Controllers\Staff\ContributionController;
use App\Http\Controllers\Staff\BorrowingController as StaffBorrowingController;
use App\Http\Controllers\Student\DashboardController as StudentDashboardController;
use App\Http\Controllers\Student\BookController as StudentBookController;
use App\Http\Controllers\Student\ResourceController as StudentResourceController;
use App\Http\Controllers\Student\BorrowingController as StudentBorrowingController;

Route::get('/', function () {
    return view('welcome');
})->name('home');

// Authentication Routes
Route::get('/login', [LoginController::class, 'showLoginForm'])->name('login');
Route::post('/login', [LoginController::class, 'login']);
Route::post('/logout', [LoginController::class, 'logout'])->name('logout');

// Admin Routes
Route::middleware(['auth', 'role:admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/dashboard', [AdminDashboardController::class, 'index'])->name('dashboard');
    
    // Users
    Route::resource('users', UserController::class);
    Route::post('users/{user}/toggle-status', [UserController::class, 'toggleStatus'])->name('users.toggle-status');
    Route::post('users/{user}/reset-password', [UserController::class, 'resetPassword'])->name('users.reset-password');
    
    // Books
    Route::resource('books', AdminBookController::class);
    
    // Resources
    Route::get('resources', [AdminResourceController::class, 'index'])->name('resources.index');
    Route::get('resources/{resource}', [AdminResourceController::class, 'show'])->name('resources.show');
    Route::post('resources/{resource}/approve', [AdminResourceController::class, 'approve'])->name('resources.approve');
    Route::post('resources/{resource}/reject', [AdminResourceController::class, 'reject'])->name('resources.reject');
    Route::delete('resources/{resource}', [AdminResourceController::class, 'destroy'])->name('resources.destroy');
    
    // Borrowings
    Route::get('borrowings', [AdminBorrowingController::class, 'index'])->name('borrowings.index');
    Route::get('borrowings/{borrowing}', [AdminBorrowingController::class, 'show'])->name('borrowings.show');
    Route::post('borrowings/{borrowing}/renew', [AdminBorrowingController::class, 'renew'])->name('borrowings.renew');
    
    // Reports
    Route::get('reports', [ReportController::class, 'index'])->name('reports.index');
    Route::get('reports/borrowing', [ReportController::class, 'borrowingReport'])->name('reports.borrowing');
    Route::get('reports/user', [ReportController::class, 'userReport'])->name('reports.user');
    Route::get('reports/book-inventory', [ReportController::class, 'bookInventoryReport'])->name('reports.book-inventory');
    Route::get('reports/fine', [ReportController::class, 'fineReport'])->name('reports.fine');
    Route::get('reports/resource-usage', [ReportController::class, 'resourceUsageReport'])->name('reports.resource-usage');
});

// Librarian Routes
Route::middleware(['auth', 'role:librarian'])->prefix('librarian')->name('librarian.')->group(function () {
    Route::get('/dashboard', [LibrarianDashboardController::class, 'index'])->name('dashboard');
    
    // Books
    Route::resource('books', LibrarianBookController::class);
    Route::get('books/{book}/copies', [LibrarianBookController::class, 'manageCopies'])->name('books.copies');
    Route::post('books/{book}/copies', [LibrarianBookController::class, 'addCopy'])->name('books.add-copy');
    
    // Circulation
    Route::get('circulation/issue', [CirculationController::class, 'issueForm'])->name('circulation.issue-form');
    Route::post('circulation/issue', [CirculationController::class, 'issue'])->name('circulation.issue');
    Route::get('circulation/return', [CirculationController::class, 'returnForm'])->name('circulation.return-form');
    Route::post('circulation/return', [CirculationController::class, 'processReturn'])->name('circulation.return');
    Route::get('circulation/renewals', [CirculationController::class, 'renewals'])->name('circulation.renewals');
    Route::post('circulation/renewals/{borrowing}', [CirculationController::class, 'processRenewal'])->name('circulation.process-renewal');
    Route::get('circulation/search-user', [CirculationController::class, 'searchUser'])->name('circulation.search-user');
    Route::get('circulation/search-book', [CirculationController::class, 'searchBook'])->name('circulation.search-book');
    
    // Reservations
    Route::get('reservations', [ReservationController::class, 'index'])->name('reservations.index');
    Route::post('reservations/{reservation}/approve', [ReservationController::class, 'approve'])->name('reservations.approve');
    Route::post('reservations/{reservation}/reject', [ReservationController::class, 'reject'])->name('reservations.reject');
    Route::post('reservations/{reservation}/fulfill', [ReservationController::class, 'fulfill'])->name('reservations.fulfill');
    Route::post('reservations/{reservation}/cancel', [ReservationController::class, 'cancel'])->name('reservations.cancel');
    
    // Resources
    Route::resource('resources', LibrarianResourceController::class);
    Route::get('resources/pending', [LibrarianResourceController::class, 'pending'])->name('resources.pending');
    Route::post('resources/{resource}/approve', [LibrarianResourceController::class, 'approve'])->name('resources.approve');
    Route::post('resources/{resource}/reject', [LibrarianResourceController::class, 'reject'])->name('resources.reject');
    Route::get('resources/{resource}/download', [LibrarianResourceController::class, 'download'])->name('resources.download');
    
    // Fines
    Route::get('fines', [FineController::class, 'index'])->name('fines.index');
    Route::get('fines/{fine}', [FineController::class, 'show'])->name('fines.show');
    Route::post('fines/{fine}/payment', [FineController::class, 'recordPayment'])->name('fines.payment');
    Route::post('fines/{fine}/waive', [FineController::class, 'waive'])->name('fines.waive');
    Route::get('fines/overdue', [FineController::class, 'overdueBooks'])->name('fines.overdue');
});

// Staff Routes
Route::middleware(['auth', 'role:staff'])->prefix('staff')->name('staff.')->group(function () {
    Route::get('/dashboard', [StaffDashboardController::class, 'index'])->name('dashboard');
    
    // Books
    Route::get('books', [StaffBookController::class, 'index'])->name('books.index');
    Route::get('books/{book}', [StaffBookController::class, 'show'])->name('books.show');
    Route::post('books/{book}/borrow', [StaffBookController::class, 'borrow'])->name('books.borrow');
    Route::post('books/{book}/reserve', [StaffBookController::class, 'reserve'])->name('books.reserve');
    
    // Resources
    Route::get('resources', [StaffResourceController::class, 'index'])->name('resources.index');
    Route::get('resources/{resource}', [StaffResourceController::class, 'show'])->name('resources.show');
    Route::get('resources/{resource}/download', [StaffResourceController::class, 'download'])->name('resources.download');
    Route::get('resources/create', [StaffResourceController::class, 'create'])->name('resources.create');
    Route::post('resources', [StaffResourceController::class, 'store'])->name('resources.store');
    Route::delete('resources/{resource}', [StaffResourceController::class, 'destroy'])->name('resources.destroy');
    
    // Contributions
    Route::get('contributions', [ContributionController::class, 'index'])->name('contributions.index');
    Route::get('contributions/{contribution}', [ContributionController::class, 'show'])->name('contributions.show');
    
    // Borrowings
    Route::get('borrowings', [StaffBorrowingController::class, 'index'])->name('borrowings.index');
    Route::get('borrowings/active', [StaffBorrowingController::class, 'active'])->name('borrowings.active');
    Route::get('borrowings/history', [StaffBorrowingController::class, 'history'])->name('borrowings.history');
    Route::post('borrowings/{borrowing}/renew', [StaffBorrowingController::class, 'renew'])->name('borrowings.renew');
});

// Student Routes
Route::middleware(['auth', 'role:student'])->prefix('student')->name('student.')->group(function () {
    Route::get('/dashboard', [StudentDashboardController::class, 'index'])->name('dashboard');
    
    // Books
    Route::get('books', [StudentBookController::class, 'index'])->name('books.index');
    Route::get('books/{book}', [StudentBookController::class, 'show'])->name('books.show');
    Route::post('books/{book}/borrow', [StudentBookController::class, 'borrow'])->name('books.borrow');
    Route::post('books/{book}/reserve', [StudentBookController::class, 'reserve'])->name('books.reserve');
    
    // Resources
    Route::get('resources', [StudentResourceController::class, 'index'])->name('resources.index');
    Route::get('resources/{resource}', [StudentResourceController::class, 'show'])->name('resources.show');
    Route::get('resources/{resource}/download', [StudentResourceController::class, 'download'])->name('resources.download');
    
    // Borrowings
    Route::get('borrowings', [StudentBorrowingController::class, 'index'])->name('borrowings.index');
    Route::get('borrowings/active', [StudentBorrowingController::class, 'active'])->name('borrowings.active');
    Route::get('borrowings/history', [StudentBorrowingController::class, 'history'])->name('borrowings.history');
    Route::post('borrowings/{borrowing}/renew', [StudentBorrowingController::class, 'renew'])->name('borrowings.renew');
    Route::get('borrowings/fines', [StudentBorrowingController::class, 'fines'])->name('borrowings.fines');
    Route::get('borrowings/reservations', [StudentBorrowingController::class, 'reservations'])->name('borrowings.reservations');
    Route::post('borrowings/reservations/{reservation}/cancel', [StudentBorrowingController::class, 'cancelReservation'])->name('borrowings.cancel-reservation');
});
