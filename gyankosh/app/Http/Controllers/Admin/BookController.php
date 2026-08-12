<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Book;
use App\Models\BookCopy;
use App\Models\Category;
use App\Models\Author;
use App\Models\Publisher;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class BookController extends Controller
{
    public function index(Request $request)
    {
        $query = Book::with(['author', 'publisher', 'category']);

        // Search
        if ($request->has('search') && $request->search) {
            $query->where(function($q) use ($request) {
                $q->where('title', 'like', '%' . $request->search . '%')
                  ->orWhere('isbn', 'like', '%' . $request->search . '%')
                  ->orWhere('subject', 'like', '%' . $request->search . '%');
            });
        }

        // Filter by category
        if ($request->has('category') && $request->category) {
            $query->where('category_id', $request->category);
        }

        // Filter by status
        if ($request->has('status') && $request->status) {
            $query->where('status', $request->status);
        }

        $books = $query->orderBy('created_at', 'desc')->paginate(15);
        $categories = Category::all();
        $authors = Author::all();
        $publishers = Publisher::all();

        return view('admin.books.index', compact('books', 'categories', 'authors', 'publishers'));
    }

    public function create()
    {
        $categories = Category::all();
        $authors = Author::all();
        $publishers = Publisher::all();

        return view('admin.books.create', compact('categories', 'authors', 'publishers'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'isbn' => 'required|string|unique:books,isbn',
            'author_id' => 'required|exists:authors,id',
            'publisher_id' => 'required|exists:publishers,id',
            'category_id' => 'required|exists:categories,id',
            'subject' => 'nullable|string|max:255',
            'description' => 'nullable|string',
            'edition' => 'nullable|string|max:100',
            'publication_year' => 'nullable|integer|min:1900|max:' . date('Y'),
            'language' => 'nullable|string|max:50',
            'total_copies' => 'required|integer|min:1',
            'shelf_number' => 'nullable|string|max:50',
            'rack_number' => 'nullable|string|max:50',
            'location' => 'nullable|string|max:255',
            'status' => 'required|in:active,inactive',
            'cover_image' => 'nullable|image|max:2048',
        ]);

        if ($request->hasFile('cover_image')) {
            $validated['cover_image'] = $request->file('cover_image')->store('book_covers', 'public');
        }

        $validated['available_copies'] = $validated['total_copies'];

        Book::create($validated);

        return redirect()->route('admin.books.index')
            ->with('success', 'Book created successfully.');
    }

    public function show(Book $book)
    {
        $book->load(['author', 'publisher', 'category', 'copies']);
        return view('admin.books.show', compact('book'));
    }

    public function edit(Book $book)
    {
        $categories = Category::all();
        $authors = Author::all();
        $publishers = Publisher::all();

        return view('admin.books.edit', compact('book', 'categories', 'authors', 'publishers'));
    }

    public function update(Request $request, Book $book)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'isbn' => 'required|string|unique:books,isbn,' . $book->id,
            'author_id' => 'required|exists:authors,id',
            'publisher_id' => 'required|exists:publishers,id',
            'category_id' => 'required|exists:categories,id',
            'subject' => 'nullable|string|max:255',
            'description' => 'nullable|string',
            'edition' => 'nullable|string|max:100',
            'publication_year' => 'nullable|integer|min:1900|max:' . date('Y'),
            'language' => 'nullable|string|max:50',
            'total_copies' => 'required|integer|min:1',
            'shelf_number' => 'nullable|string|max:50',
            'rack_number' => 'nullable|string|max:50',
            'location' => 'nullable|string|max:255',
            'status' => 'required|in:active,inactive',
            'cover_image' => 'nullable|image|max:2048',
        ]);

        if ($request->hasFile('cover_image')) {
            if ($book->cover_image) {
                Storage::disk('public')->delete($book->cover_image);
            }
            $validated['cover_image'] = $request->file('cover_image')->store('book_covers', 'public');
        }

        // Update available copies based on total change
        $copyDifference = $validated['total_copies'] - $book->total_copies;
        $validated['available_copies'] = max(0, $book->available_copies + $copyDifference);

        $book->update($validated);

        return redirect()->route('admin.books.index')
            ->with('success', 'Book updated successfully.');
    }

    public function destroy(Book $book)
    {
        if ($book->cover_image) {
            Storage::disk('public')->delete($book->cover_image);
        }
        
        $book->delete();

        return redirect()->route('admin.books.index')
            ->with('success', 'Book deleted successfully.');
    }
}
