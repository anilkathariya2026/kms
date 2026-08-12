<?php

namespace App\Http\Controllers\Staff;

use App\Http\Controllers\Controller;
use App\Models\DigitalResource;
use App\Models\Book;
use App\Models\Borrowing;
use App\Models\Reservation;
use App\Models\ResourceCategory;
use App\Models\Department;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class ResourceController extends Controller
{
    public function index(Request $request)
    {
        $query = DigitalResource::with(['category', 'department', 'contributor'])
            ->where('status', 'approved');

        // Search
        if ($request->has('search') && $request->search) {
            $query->where(function($q) use ($request) {
                $q->where('title', 'like', '%' . $request->search . '%')
                  ->orWhere('author', 'like', '%' . $request->search . '%')
                  ->orWhere('description', 'like', '%' . $request->search . '%');
            });
        }

        // Filter by category
        if ($request->has('category') && $request->category) {
            $query->where('category_id', $request->category);
        }

        // Filter by resource type
        if ($request->has('resource_type') && $request->resource_type) {
            $query->where('resource_type', $request->resource_type);
        }

        $resources = $query->orderBy('created_at', 'desc')->paginate(12);
        $categories = ResourceCategory::all();

        return view('staff.resources.index', compact('resources', 'categories'));
    }

    public function show(DigitalResource $resource)
    {
        // Check access permissions
        if ($resource->access_level === 'restricted' && auth()->id() !== $resource->contributor_id) {
            abort(403, 'You do not have access to this resource.');
        }

        $resource->increment('view_count');

        $resource->load(['category', 'department', 'contributor']);
        return view('staff.resources.show', compact('resource'));
    }

    public function download(DigitalResource $resource)
    {
        // Check access permissions
        if ($resource->access_level === 'restricted' && auth()->id() !== $resource->contributor_id) {
            abort(403, 'You do not have access to this resource.');
        }

        if (!$resource->download_permission && auth()->id() !== $resource->contributor_id) {
            abort(403, 'Download not permitted for this resource.');
        }

        $resource->increment('download_count');

        return Storage::disk('public')->download($resource->file_path);
    }

    public function create()
    {
        $categories = ResourceCategory::all();
        $departments = Department::all();

        return view('staff.resources.create', compact('categories', 'departments'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'author' => 'nullable|string|max:255',
            'category_id' => 'required|exists:resource_categories,id',
            'department_id' => 'nullable|exists:departments,id',
            'resource_type' => 'required|in:ebook,research_paper,journal,thesis,lecture_notes,course_material,question_paper,assignment,presentation,article,institutional_document,video,external_link',
            'access_level' => 'required|in:public,institution,restricted',
            'download_permission' => 'required|boolean',
            'tags' => 'nullable|string',
            'file' => 'required|file|mimes:pdf,doc,docx,ppt,pptx,xls,xlsx,jpg,png,mp4|max:51200',
            'thumbnail' => 'nullable|image|max:2048',
        ]);

        // Store file
        $validated['file_path'] = $request->file('file')->store('resources', 'public');
        $validated['file_name'] = $request->file('file')->getClientOriginalName();
        $validated['file_size'] = $request->file('file')->getSize();
        $validated['file_mime'] = $request->file('file')->getMimeType();

        // Store thumbnail if provided
        if ($request->hasFile('thumbnail')) {
            $validated['thumbnail'] = $request->file('thumbnail')->store('thumbnails', 'public');
        }

        $validated['contributor_id'] = auth()->id();
        $validated['status'] = 'pending';

        DigitalResource::create($validated);

        return redirect()->route('staff.contributions.index')
            ->with('success', 'Resource submitted for approval.');
    }

    public function edit(DigitalResource $resource)
    {
        // Only allow editing own resources or if librarian/admin
        if ($resource->contributor_id !== auth()->id()) {
            abort(403, 'You can only edit your own resources.');
        }

        if ($resource->status !== 'pending') {
            abort(403, 'Cannot edit approved or rejected resources.');
        }

        $categories = ResourceCategory::all();
        $departments = Department::all();

        return view('staff.resources.edit', compact('resource', 'categories', 'departments'));
    }

    public function update(Request $request, DigitalResource $resource)
    {
        if ($resource->contributor_id !== auth()->id()) {
            abort(403, 'You can only edit your own resources.');
        }

        if ($resource->status !== 'pending') {
            abort(403, 'Cannot edit approved or rejected resources.');
        }

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'author' => 'nullable|string|max:255',
            'category_id' => 'required|exists:resource_categories,id',
            'department_id' => 'nullable|exists:departments,id',
            'resource_type' => 'required|in:ebook,research_paper,journal,thesis,lecture_notes,course_material,question_paper,assignment,presentation,article,institutional_document,video,external_link',
            'access_level' => 'required|in:public,institution,restricted',
            'download_permission' => 'required|boolean',
            'tags' => 'nullable|string',
            'file' => 'nullable|file|mimes:pdf,doc,docx,ppt,pptx,xls,xlsx,jpg,png,mp4|max:51200',
            'thumbnail' => 'nullable|image|max:2048',
        ]);

        if ($request->hasFile('file')) {
            if ($resource->file_path) {
                Storage::disk('public')->delete($resource->file_path);
            }
            $validated['file_path'] = $request->file('file')->store('resources', 'public');
            $validated['file_name'] = $request->file('file')->getClientOriginalName();
            $validated['file_size'] = $request->file('file')->getSize();
        }

        if ($request->hasFile('thumbnail')) {
            if ($resource->thumbnail) {
                Storage::disk('public')->delete($resource->thumbnail);
            }
            $validated['thumbnail'] = $request->file('thumbnail')->store('thumbnails', 'public');
        }

        $resource->update($validated);

        return redirect()->route('staff.contributions.index')
            ->with('success', 'Resource updated successfully.');
    }

    public function destroy(DigitalResource $resource)
    {
        if ($resource->contributor_id !== auth()->id()) {
            abort(403, 'You can only delete your own resources.');
        }

        if ($resource->status === 'approved') {
            abort(403, 'Cannot delete approved resources. Contact librarian.');
        }

        if ($resource->file_path) {
            Storage::disk('public')->delete($resource->file_path);
        }
        
        if ($resource->thumbnail) {
            Storage::disk('public')->delete($resource->thumbnail);
        }

        $resource->delete();

        return redirect()->route('staff.contributions.index')
            ->with('success', 'Resource deleted successfully.');
    }
}
