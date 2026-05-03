<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Book;
use App\Models\BookCategory;
use App\Models\BookIssue;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Inertia\Response;

class LibraryController extends Controller
{
    public function index(Request $request): Response
    {
        $query = Book::with('category:id,name');

        if ($search = $request->input('search')) {
            $query->where(fn ($q) => $q->where('title', 'like', "%{$search}%")
                ->orWhere('author', 'like', "%{$search}%")
                ->orWhere('isbn', 'like', "%{$search}%"));
        }
        if ($categoryId = $request->input('category_id')) {
            $query->where('category_id', $categoryId);
        }

        return Inertia::render('admin/Library/Index', [
            'books' => $query->orderBy('title')->paginate(20),
            'categories' => BookCategory::withCount('books')->orderBy('name')->get(),
            'filters' => $request->only('search', 'category_id'),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'author' => 'required|string|max:255',
            'isbn' => 'nullable|string|max:20|unique:books,isbn',
            'publisher' => 'nullable|string|max:255',
            'year' => 'nullable|integer|min:1900|max:2100',
            'category_id' => 'nullable|exists:book_categories,id',
            'shelf' => 'nullable|string|max:20',
            'total_copies' => 'required|integer|min:1',
        ]);

        $validated['available_copies'] = $validated['total_copies'];

        Book::create($validated);

        return back()->with('flash', ['type' => 'success', 'message' => 'Book added.']);
    }

    public function update(Request $request, Book $book): RedirectResponse
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'author' => 'required|string|max:255',
            'isbn' => "nullable|string|max:20|unique:books,isbn,{$book->id}",
            'publisher' => 'nullable|string|max:255',
            'year' => 'nullable|integer|min:1900|max:2100',
            'category_id' => 'nullable|exists:book_categories,id',
            'shelf' => 'nullable|string|max:20',
            'total_copies' => 'required|integer|min:1',
        ]);

        $issuedCount = $book->total_copies - $book->available_copies;
        $validated['available_copies'] = max(0, $validated['total_copies'] - $issuedCount);

        $book->update($validated);

        return back()->with('flash', ['type' => 'success', 'message' => 'Book updated.']);
    }

    public function destroy(Book $book): RedirectResponse
    {
        $book->delete();

        return back()->with('flash', ['type' => 'success', 'message' => 'Book deleted.']);
    }

    // --- Issue / Return ---

    public function issues(Request $request): Response
    {
        $status = $request->input('status', 'issued');

        return Inertia::render('admin/Library/Issues', [
            'issues' => BookIssue::with(['book:id,title,author', 'user:id,name'])
                ->when($status !== 'all', fn ($q) => $q->where('status', $status))
                ->orderByDesc('issued_at')
                ->paginate(20)
                ->through(fn ($i) => [
                    'id' => $i->id,
                    'book_title' => $i->book->title,
                    'book_author' => $i->book->author,
                    'user_name' => $i->user->name,
                    'issued_at' => $i->issued_at->format('Y-m-d'),
                    'due_date' => $i->due_date->format('Y-m-d'),
                    'returned_at' => $i->returned_at?->format('Y-m-d'),
                    'fine' => $i->fine,
                    'status' => $i->status->value,
                    'is_overdue' => $i->isOverdue(),
                ]),
            'selectedStatus' => $status,
        ]);
    }

    public function issueBook(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'book_id' => 'required|exists:books,id',
            'user_id' => 'required|exists:users,id',
            'due_date' => 'required|date|after:today',
        ]);

        $book = Book::findOrFail($validated['book_id']);

        if (! $book->isAvailable()) {
            return back()->withErrors(['book_id' => 'No copies available.']);
        }

        DB::transaction(function () use ($book, $validated, $request) {
            BookIssue::create([
                'book_id' => $validated['book_id'],
                'user_id' => $validated['user_id'],
                'issued_at' => now(),
                'due_date' => $validated['due_date'],
                'status' => 'issued',
                'issued_by' => $request->user()->id,
            ]);

            $book->decrement('available_copies');
        });

        return back()->with('flash', ['type' => 'success', 'message' => 'Book issued.']);
    }

    public function returnBook(Request $request, BookIssue $bookIssue): RedirectResponse
    {
        $validated = $request->validate([
            'fine' => 'nullable|numeric|min:0',
        ]);

        DB::transaction(function () use ($bookIssue, $validated) {
            $bookIssue->update([
                'returned_at' => now(),
                'status' => 'returned',
                'fine' => $validated['fine'] ?? 0,
            ]);

            $bookIssue->book->increment('available_copies');
        });

        return back()->with('flash', ['type' => 'success', 'message' => 'Book returned.']);
    }

    // --- Categories ---
    public function storeCategory(Request $request): RedirectResponse
    {
        BookCategory::create($request->validate(['name' => 'required|string|max:100|unique:book_categories,name']));

        return back()->with('flash', ['type' => 'success', 'message' => 'Category created.']);
    }

    public function destroyCategory(BookCategory $bookCategory): RedirectResponse
    {
        $bookCategory->delete();

        return back()->with('flash', ['type' => 'success', 'message' => 'Category deleted.']);
    }
}
