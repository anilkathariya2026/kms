@extends('layouts.main')

@section('title', 'Student Dashboard')

@section('content')
<div class="space-y-6" x-data="{
    loading: false,
    refreshStats() {
        this.loading = true;
        setTimeout(() => {
            window.location.reload();
            this.loading = false;
        }, 500);
    }
}">

    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Student Dashboard</h1>
            <p class="text-gray-600 dark:text-gray-400 mt-1">Access learning resources and manage borrowings</p>
        </div>
        <button @click="refreshStats()" :disabled="loading"
            class="inline-flex items-center px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white rounded-lg transition-colors disabled:opacity-50">
            <svg x-show="!loading" class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
            </svg>
            <svg x-show="loading" class="animate-spin w-5 h-5 mr-2" fill="none" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
            </svg>
            Refresh
        </button>
    </div>

    <!-- Quick Actions -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        <a href="{{ route('student.search') }}" class="flex items-center p-4 bg-gradient-to-r from-indigo-500 to-purple-600 rounded-xl text-white hover:from-indigo-600 hover:to-purple-700 transition-all shadow-lg hover:shadow-xl">
            <div class="p-3 bg-white/20 rounded-lg">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                </svg>
            </div>
            <div class="ml-4">
                <p class="font-semibold">Search Resources</p>
                <p class="text-sm text-white/80">Find books & more</p>
            </div>
        </a>

        <a href="{{ route('student.books.index') }}" class="flex items-center p-4 bg-gradient-to-r from-blue-500 to-cyan-600 rounded-xl text-white hover:from-blue-600 hover:to-cyan-700 transition-all shadow-lg hover:shadow-xl">
            <div class="p-3 bg-white/20 rounded-lg">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/>
                </svg>
            </div>
            <div class="ml-4">
                <p class="font-semibold">Browse Books</p>
                <p class="text-sm text-white/80">Library catalog</p>
            </div>
        </a>

        <a href="{{ route('student.borrowings.index') }}" class="flex items-center p-4 bg-gradient-to-r from-green-500 to-emerald-600 rounded-xl text-white hover:from-green-600 hover:to-emerald-700 transition-all shadow-lg hover:shadow-xl">
            <div class="p-3 bg-white/20 rounded-lg">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                </svg>
            </div>
            <div class="ml-4">
                <p class="font-semibold">My Borrowings</p>
                <p class="text-sm text-white/80">{{ $stats['active_borrowings'] }} active</p>
            </div>
        </a>

        <a href="{{ route('student.favorites.index') }}" class="flex items-center p-4 bg-gradient-to-r from-pink-500 to-rose-600 rounded-xl text-white hover:from-pink-600 hover:to-rose-700 transition-all shadow-lg hover:shadow-xl">
            <div class="p-3 bg-white/20 rounded-lg">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"/>
                </svg>
            </div>
            <div class="ml-4">
                <p class="font-semibold">Favorites</p>
                <p class="text-sm text-white/80">{{ $stats['favorites_count'] }} saved</p>
            </div>
        </a>
    </div>

    <!-- Statistics Cards -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
        <!-- Active Borrowings -->
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm p-6 border border-gray-200 dark:border-gray-700">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-gray-600 dark:text-gray-400">Active Borrowings</p>
                    <p class="text-3xl font-bold text-gray-900 dark:text-white mt-2">{{ number_format($stats['active_borrowings']) }}</p>
                    <p class="text-sm text-yellow-600 mt-2 flex items-center">
                        <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                        Due Soon: {{ number_format($stats['due_soon']) }}
                    </p>
                </div>
                <div class="p-3 bg-blue-100 dark:bg-blue-900/30 rounded-lg">
                    <svg class="w-8 h-8 text-blue-600 dark:text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                    </svg>
                </div>
            </div>
        </div>

        <!-- Overdue Books -->
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm p-6 border border-gray-200 dark:border-gray-700">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-gray-600 dark:text-gray-400">Overdue Books</p>
                    <p class="text-3xl font-bold text-red-600 dark:text-red-400 mt-2">{{ number_format($stats['overdue_books']) }}</p>
                    <p class="text-sm text-gray-500 dark:text-gray-400 mt-2">
                        @if($stats['overdue_books'] > 0)
                            Fine: ₹{{ number_format($stats['total_fines'], 2) }}
                        @else
                            All good!
                        @endif
                    </p>
                </div>
                <div class="p-3 bg-red-100 dark:bg-red-900/30 rounded-lg">
                    <svg class="w-8 h-8 text-red-600 dark:text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                    </svg>
                </div>
            </div>
        </div>

        <!-- Reservations -->
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm p-6 border border-gray-200 dark:border-gray-700">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-gray-600 dark:text-gray-400">Reservations</p>
                    <p class="text-3xl font-bold text-gray-900 dark:text-white mt-2">{{ number_format($stats['reservations_count']) }}</p>
                    <p class="text-sm text-purple-600 mt-2 flex items-center">
                        <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 5a2 2 0 012-2h10a2 2 0 012 2v16l-7-3.5L5 21V5z"/>
                        </svg>
                        Ready: {{ number_format($stats['ready_reservations']) }}
                    </p>
                </div>
                <div class="p-3 bg-purple-100 dark:bg-purple-900/30 rounded-lg">
                    <svg class="w-8 h-8 text-purple-600 dark:text-purple-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 5a2 2 0 012-2h10a2 2 0 012 2v16l-7-3.5L5 21V5z"/>
                    </svg>
                </div>
            </div>
        </div>

        <!-- Favorites & Bookmarks -->
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm p-6 border border-gray-200 dark:border-gray-700">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-gray-600 dark:text-gray-400">Favorites</p>
                    <p class="text-3xl font-bold text-gray-900 dark:text-white mt-2">{{ number_format($stats['favorites_count']) }}</p>
                    <p class="text-sm text-gray-500 dark:text-gray-400 mt-2">
                        Bookmarks: {{ number_format($stats['bookmarks_count']) }}
                    </p>
                </div>
                <div class="p-3 bg-pink-100 dark:bg-pink-900/30 rounded-lg">
                    <svg class="w-8 h-8 text-pink-600 dark:text-pink-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"/>
                    </svg>
                </div>
            </div>
        </div>
    </div>

    <!-- Alert Banner for Overdue Books -->
    @if($stats['overdue_books'] > 0)
    <div class="bg-red-50 dark:bg-red-900/20 border-l-4 border-red-500 p-4 rounded-r-lg">
        <div class="flex items-start">
            <div class="flex-shrink-0">
                <svg class="h-5 w-5 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                </svg>
            </div>
            <div class="ml-3">
                <h3 class="text-sm font-medium text-red-800 dark:text-red-300">You have {{ $stats['overdue_books'] }} overdue book(s)</h3>
                <p class="text-sm text-red-700 dark:text-red-400 mt-1">Please return the overdue books to avoid additional fines. Total fine: ₹{{ number_format($stats['total_fines'], 2) }}</p>
                <div class="mt-2">
                    <a href="{{ route('student.borrowings.index') }}" class="text-sm font-medium text-red-800 dark:text-red-300 hover:text-red-700 dark:hover:text-red-200">
                        View Borrowings →
                    </a>
                </div>
            </div>
        </div>
    </div>
    @endif

    <!-- Main Content Grid -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <!-- My Borrowings -->
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700">
            <div class="p-6 border-b border-gray-200 dark:border-gray-700">
                <div class="flex items-center justify-between">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white">My Current Borrowings</h3>
                    <a href="{{ route('student.borrowings.index') }}" class="text-sm text-indigo-600 hover:text-indigo-700 dark:text-indigo-400">View All</a>
                </div>
            </div>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                    <thead class="bg-gray-50 dark:bg-gray-900/50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Book</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Due Date</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Days Left</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                        @forelse($currentBorrowings as $borrowing)
                        <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/50">
                            <td class="px-6 py-4">
                                <div class="text-sm font-medium text-gray-900 dark:text-white">{{ $borrowing->bookCopy->book->title ?? 'N/A' }}</div>
                                <div class="text-sm text-gray-500 dark:text-gray-400">Barcode: {{ $borrowing->bookCopy->barcode ?? 'N/A' }}</div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm {{ $borrowing->isOverdue() ? 'text-red-600 font-semibold' : 'text-gray-500 dark:text-gray-400' }}">
                                {{ $borrowing->due_date->format('M d, Y') }}
                                @if($borrowing->isOverdue())
                                    <span class="text-xs block">(Overdue)</span>
                                @endif
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm">
                                @php
                                    $daysLeft = now()->diffInDays($borrowing->due_date, false);
                                @endphp
                                @if($daysLeft >= 0)
                                    <span class="text-green-600">{{ $daysLeft }} days</span>
                                @else
                                    <span class="text-red-600 font-semibold">{{ abs($daysLeft) }} days late</span>
                                @endif
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                <div class="flex space-x-2">
                                    @if(!$borrowing->isOverdue())
                                    <form action="{{ route('student.borrowings.renew', $borrowing->id) }}" method="POST" class="inline">
                                        @csrf
                                        <button type="submit" class="text-indigo-600 hover:text-indigo-900 dark:text-indigo-400" title="Renew">
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                                            </svg>
                                        </button>
                                    </form>
                                    @endif
                                    <a href="{{ route('student.books.show', $borrowing->bookCopy->book_id) }}" class="text-gray-600 hover:text-gray-900 dark:text-gray-400" title="View Details">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                        </svg>
                                    </a>
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="4" class="px-6 py-8 text-center text-gray-500 dark:text-gray-400">
                                <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                                </svg>
                                <p class="mt-2">No active borrowings</p>
                                <a href="{{ route('student.books.index') }}" class="text-indigo-600 hover:text-indigo-700 mt-2 inline-block">Browse Books</a>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Recent Activity -->
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700">
            <div class="p-6 border-b border-gray-200 dark:border-gray-700">
                <div class="flex items-center justify-between">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Recent Activity</h3>
                    <a href="{{ route('student.history') }}" class="text-sm text-indigo-600 hover:text-indigo-700 dark:text-indigo-400">View All</a>
                </div>
            </div>
            <div class="p-6 space-y-4">
                @forelse($recentActivity as $activity)
                <div class="flex items-start space-x-3 p-3 rounded-lg hover:bg-gray-50 dark:hover:bg-gray-700/50 transition-colors">
                    <div class="flex-shrink-0 w-10 h-10 rounded-full 
                        @if($activity->type === 'borrow') bg-blue-100 dark:bg-blue-900/30
                        @elseif($activity->type === 'return') bg-green-100 dark:bg-green-900/30
                        @elseif($activity->type === 'reservation') bg-purple-100 dark:bg-purple-900/30
                        @else bg-gray-100 dark:bg-gray-900/30
                        @endif flex items-center justify-center">
                        @if($activity->type === 'borrow')
                            <svg class="w-5 h-5 text-blue-600 dark:text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                            </svg>
                        @elseif($activity->type === 'return')
                            <svg class="w-5 h-5 text-green-600 dark:text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                            </svg>
                        @else
                            <svg class="w-5 h-5 text-purple-600 dark:text-purple-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 5a2 2 0 012-2h10a2 2 0 012 2v16l-7-3.5L5 21V5z"/>
                            </svg>
                        @endif
                    </div>
                    <div class="flex-1 min-w-0">
                        <p class="text-sm font-medium text-gray-900 dark:text-white">{{ $activity->description }}</p>
                        <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">{{ $activity->created_at->diffForHumans() }}</p>
                    </div>
                </div>
                @empty
                <div class="text-center py-8">
                    <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                    <p class="mt-2 text-gray-500 dark:text-gray-400">No recent activity</p>
                </div>
                @endforelse
            </div>
        </div>
    </div>

    <!-- Recommended Resources -->
    <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700">
        <div class="p-6 border-b border-gray-200 dark:border-gray-700">
            <div class="flex items-center justify-between">
                <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Recommended For You</h3>
                <a href="{{ route('student.resources.index') }}" class="text-sm text-indigo-600 hover:text-indigo-700 dark:text-indigo-400">Browse All</a>
            </div>
        </div>
        <div class="p-6 grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
            @forelse($recommendedResources as $resource)
            <div class="border border-gray-200 dark:border-gray-700 rounded-lg p-4 hover:shadow-md transition-shadow">
                <div class="flex items-start space-x-3">
                    <div class="flex-shrink-0 w-12 h-12 bg-indigo-100 dark:bg-indigo-900/30 rounded-lg flex items-center justify-center">
                        <svg class="w-6 h-6 text-indigo-600 dark:text-indigo-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                        </svg>
                    </div>
                    <div class="flex-1 min-w-0">
                        <p class="text-sm font-medium text-gray-900 dark:text-white truncate">{{ $resource->title }}</p>
                        <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">{{ str_replace('_', ' ', ucfirst($resource->resource_type)) }}</p>
                    </div>
                </div>
                <div class="mt-3 flex items-center justify-between">
                    <a href="{{ route('student.resources.show', $resource->id) }}" class="text-sm text-indigo-600 hover:text-indigo-700 dark:text-indigo-400">View</a>
                    <form action="{{ route('student.favorites.toggle', $resource->id) }}" method="POST" class="inline">
                        @csrf
                        <button type="submit" class="text-gray-400 hover:text-pink-600">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"/>
                            </svg>
                        </button>
                    </form>
                </div>
            </div>
            @empty
            <div class="col-span-full text-center py-8">
                <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/>
                </svg>
                <p class="mt-2 text-gray-500 dark:text-gray-400">Start browsing to get personalized recommendations</p>
            </div>
            @endforelse
        </div>
    </div>
</div>
@endsection
