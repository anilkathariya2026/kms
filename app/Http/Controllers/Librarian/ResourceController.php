<?php

namespace App\Http\Controllers\Librarian;

use App\Http\Controllers\Controller;
use App\Models\DigitalResource;
use App\Models\ResourceCategory;
use App\Models\Department;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class ResourceController extends Controller
{
    public function index(Request $request)
    {
        $query = DigitalResource::with(['category', 'department', 'contributor']);

        // Search
        if ($request->has('search') && $request->search) {
            $query->where(function($q) use ($request) {
                $q->where('title', 'like', '%' . $request->search . '%')
                  ->orWhere('author', 'like', '%' . $request->search . '%');
            });
        }

        // Filter by status
        if ($request->has('status') && $request->status) {
            $query->where('status', $request->status);
        }

        // Filter by resource type
        if ($request->has('resource_type') && $request->resource_type) {
            $query->where('resource_type', $request->resource_type);
        }

        $resources = $query->orderBy('created_at', 'desc')->paginate(15);
        $categories = ResourceCategory::all();

        return view('librarian.resources.index', compact('resources', 'categories'));
    }

    public function pending()
    {
        $resources = DigitalResource::with(['category', 'department', 'contributor'])
            ->where('status', 'pending')
            ->orderBy('created_at', 'desc')
            ->paginate(15);

        return view('librarian.resources.pending', compact('resources'));
    }

    public function show(DigitalResource $resource)
    {
        $resource->load(['category', 'department', 'contributor']);
        return view('librarian.resources.show', compact('resource'));
    }

    public function approve(DigitalResource $resource)
    {
        $resource->update([
            'status' => 'approved',
            'approved_by' => auth()->id(),
            'approved_at' => now(),
        ]);

        return redirect()->back()
            ->with('success', 'Resource approved successfully.');
    }

    public function reject(Request $request, DigitalResource $resource)
    {
        $request->validate([
            'rejection_reason' => 'required|string|max:500',
        ]);

        $resource->update([
            'status' => 'rejected',
            'rejection_reason' => $request->rejection_reason,
            'rejected_by' => auth()->id(),
            'rejected_at' => now(),
        ]);

        return redirect()->back()
            ->with('success', 'Resource rejected.');
    }

    public function edit(DigitalResource $resource)
    {
        $categories = ResourceCategory::all();
        $departments = Department::all();

        return view('librarian.resources.edit', compact('resource', 'categories', 'departments'));
    }

    public function update(Request $request, DigitalResource $resource)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'author' => 'nullable|string|max:255',
            'category_id' => 'required|exists:resource_categories,id',
            'department_id' => 'nullable|exists:departments,id',
            'access_level' => 'required|in:public,institution,restricted',
            'download_permission' => 'required|boolean',
            'tags' => 'nullable|string',
        ]);

        $resource->update($validated);

        return redirect()->route('librarian.resources.index')
            ->with('success', 'Resource updated successfully.');
    }

    public function destroy(DigitalResource $resource)
    {
        if ($resource->file_path) {
            Storage::disk('public')->delete($resource->file_path);
        }
        
        if ($resource->thumbnail) {
            Storage::disk('public')->delete($resource->thumbnail);
        }

        $resource->delete();

        return redirect()->route('librarian.resources.index')
            ->with('success', 'Resource deleted successfully.');
    }
}
