<?php

namespace App\Http\Controllers\Librarian;

use App\Http\Controllers\Controller;
use App\Models\DigitalResource;
use App\Models\ResourceCategory;
use App\Models\Department;
use App\Http\Requests\ResourceUploadRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

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

    public function create()
    {
        $categories = ResourceCategory::all();
        $departments = Department::all();
        
        return view('librarian.resources.create', compact('categories', 'departments'));
    }

    public function store(ResourceUploadRequest $request)
    {
        $validated = $request->validated();
        
        $filePath = null;
        $fileName = null;
        $fileMimeType = null;
        $fileSize = null;
        $thumbnailPath = null;

        // Handle file upload
        if ($request->hasFile('file')) {
            $file = $request->file('file');
            $fileName = $file->getClientOriginalName();
            $fileMimeType = $file->getMimeType();
            $fileSize = $file->getSize();
            
            // Generate unique filename
            $uniqueName = Str::random(20) . '_' . time() . '_' . $file->getClientOriginalName();
            $filePath = $file->storeAs('resources', $uniqueName, 'public');
        }

        // Handle external URL
        if ($request->filled('external_url')) {
            $filePath = $request->external_url;
            $fileName = 'external_link';
            $fileMimeType = 'external/link';
        }

        // Handle thumbnail upload
        if ($request->hasFile('thumbnail')) {
            $thumbnail = $request->file('thumbnail');
            $thumbnailName = Str::random(20) . '_' . time() . '_thumb.' . $thumbnail->extension();
            $thumbnailPath = $thumbnail->storeAs('thumbnails', $thumbnailName, 'public');
        }

        $validated['file_path'] = $filePath;
        $validated['file_name'] = $fileName;
        $validated['file_mime_type'] = $fileMimeType;
        $validated['file_size'] = $fileSize;
        $validated['thumbnail_path'] = $thumbnailPath;
        $validated['contributor_id'] = auth()->id();
        $validated['status'] = 'pending'; // Requires approval

        DigitalResource::create($validated);

        return redirect()->route('librarian.resources.index')
            ->with('success', 'Resource uploaded successfully and pending approval.');
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

    public function update(ResourceUploadRequest $request, DigitalResource $resource)
    {
        $validated = $request->validated();
        
        // Handle file upload if new file is provided
        if ($request->hasFile('file')) {
            // Delete old file
            if ($resource->file_path && strpos($resource->file_path, 'http') === false) {
                Storage::disk('public')->delete($resource->file_path);
            }
            
            $file = $request->file('file');
            $validated['file_name'] = $file->getClientOriginalName();
            $validated['file_mime_type'] = $file->getMimeType();
            $validated['file_size'] = $file->getSize();
            
            $uniqueName = Str::random(20) . '_' . time() . '_' . $file->getClientOriginalName();
            $validated['file_path'] = $file->storeAs('resources', $uniqueName, 'public');
        }

        // Handle thumbnail upload
        if ($request->hasFile('thumbnail')) {
            // Delete old thumbnail
            if ($resource->thumbnail_path) {
                Storage::disk('public')->delete($resource->thumbnail_path);
            }
            
            $thumbnail = $request->file('thumbnail');
            $thumbnailName = Str::random(20) . '_' . time() . '_thumb.' . $thumbnail->extension();
            $validated['thumbnail_path'] = $thumbnail->storeAs('thumbnails', $thumbnailName, 'public');
        }

        $resource->update($validated);

        return redirect()->route('librarian.resources.index')
            ->with('success', 'Resource updated successfully.');
    }

    public function destroy(DigitalResource $resource)
    {
        if ($resource->file_path && strpos($resource->file_path, 'http') === false) {
            Storage::disk('public')->delete($resource->file_path);
        }
        
        if ($resource->thumbnail_path) {
            Storage::disk('public')->delete($resource->thumbnail_path);
        }

        $resource->delete();

        return redirect()->route('librarian.resources.index')
            ->with('success', 'Resource deleted successfully.');
    }

    public function download(DigitalResource $resource)
    {
        // Check permissions
        if (!$resource->canDownload(auth()->user())) {
            abort(403, 'You do not have permission to download this resource.');
        }

        // Increment download count
        $resource->increment('download_count');

        // Track download
        $resource->downloads()->create([
            'user_id' => auth()->id(),
            'downloaded_at' => now(),
        ]);

        // Check if it's an external link
        if (strpos($resource->file_path, 'http') === 0) {
            return redirect($resource->file_path);
        }

        return Storage::disk('public')->download($resource->file_path, $resource->file_name);
    }
}
