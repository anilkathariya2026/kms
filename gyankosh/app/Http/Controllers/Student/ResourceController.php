<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\DigitalResource;
use App\Models\ResourceCategory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class ResourceController extends Controller
{
    public function index(Request $request)
    {
        $query = DigitalResource::with(['category', 'department', 'contributor'])
            ->where('status', 'approved')
            ->whereIn('access_level', ['public', 'institution']);

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

        return view('student.resources.index', compact('resources', 'categories'));
    }

    public function show(DigitalResource $resource)
    {
        // Check access permissions
        if ($resource->access_level === 'restricted') {
            abort(403, 'You do not have access to this resource.');
        }

        $resource->increment('view_count');

        $resource->load(['category', 'department', 'contributor']);
        return view('student.resources.show', compact('resource'));
    }

    public function download(DigitalResource $resource)
    {
        // Check access permissions
        if ($resource->access_level === 'restricted') {
            abort(403, 'You do not have access to this resource.');
        }

        if (!$resource->download_permission) {
            abort(403, 'Download not permitted for this resource.');
        }

        $resource->increment('download_count');

        return Storage::disk('public')->download($resource->file_path);
    }
}
