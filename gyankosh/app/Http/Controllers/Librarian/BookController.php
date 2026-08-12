<?php

namespace App\Http\Controllers\Librarian;

use App\Http\Controllers\Controller;
use App\Models\Book;
use App\Models\BookCopy;
use App\Models\Category;
use App\Models\Author;
use App\Models\Publisher;
use App\Models\Borrowing;
use App\Models\ReturnBook;
use App\Models\Reservation;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;

class BookController extends Controller
{
    public function index(Request $request)
    {
        $query = Book::with(['author', 'publisher', 'category']);

        if ($request->has('search') && $request->search) {
            $query->where(function($q) use ($request) {
                $q->where('title', 'like', '%' . $request->search . '%')
                  ->orWhere('isbn', 'like', '%' . $request->search . '%');
            });
        }

        $books = $query->orderBy('created_at', 'desc')->paginate(15);
        return view('librarian.books.index', compact('books'));
    }

    public function create()
    {
        $categories = Category::all();
        $authors = Author::all();
        $publishers = Publisher::all();
        return view('librarian.books.create', compact('categories', 'authors', 'publishers'));
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

        DB::transaction(function () use ($validated, $request) {
            $book = Book::create($validated);

            // Create book copies
            for ($i = 1; $i <= $validated['total_copies']; $i++) {
                BookCopy::create([
                    'book_id' => $book->id,
                    'barcode' => $this->generateBarcode($book->id, $i),
                    'copy_number' => $i,
                    'shelf' => $validated['shelf_number'] ?? '',
                    'rack' => $validated['rack_number'] ?? '',
                    'status' => 'available',
                ]);
            }
        });

        return redirect()->route('librarian.books.index')
            ->with('success', 'Book created successfully.');
    }

    public function show(Book $book)
    {
        $book->load(['author', 'publisher', 'category', 'copies']);
        return view('librarian.books.show', compact('book'));
    }

    public function edit(Book $book)
    {
        $categories = Category::all();
        $authors = Author::all();
        $publishers = Publisher::all();
        return view('librarian.books.edit', compact('book', 'categories', 'authors', 'publishers'));
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

        $book->update($validated);
        return redirect()->route('librarian.books.index')
            ->with('success', 'Book updated successfully.');
    }

    public function destroy(Book $book)
    {
        if ($book->cover_image) {
            Storage::disk('public')->delete($book->cover_image);
        }
        $book->delete();

        return redirect()->route('librarian.books.index')
            ->with('success', 'Book deleted successfully.');
    }

    public function manageCopies(Book $book)
    {
        $book->load('copies');
        return view('librarian.books.copies', compact('book'));
    }

    public function addCopy(Request $request, Book $book)
    {
        $validated = $request->validate([
            'barcode' => 'required|string|unique:book_copies,barcode',
            'shelf' => 'nullable|string|max:50',
            'rack' => 'nullable|string|max:50',
        ]);

        $copyNumber = $book->copies()->max('copy_number') + 1;

        BookCopy::create([
            'book_id' => $book->id,
            'barcode' => $validated['barcode'],
            'copy_number' => $copyNumber,
            'shelf' => $validated['shelf'] ?? '',
            'rack' => $validated['rack'] ?? '',
            'status' => 'available',
        ]);

        $book->increment('total_copies');
        $book->increment('available_copies');

        return redirect()->back()
            ->with('success', 'Book copy added successfully.');
    }

    private function generateBarcode($bookId, $copyNumber)
    {
        return 'BK' . str_pad($bookId, 6, '0', STR_PAD_LEFT) . '-' . str_pad($copyNumber, 3, '0', STR_PAD_LEFT);
    }
}
