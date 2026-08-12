<?php

namespace App\Http\Controllers\Admin;

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
                  ->orWhere('author', 'like', '%' . $request->search . '%')
                  ->orWhere('description', 'like', '%' . $request->search . '%');
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

        // Filter by resource type
        if ($request->has('resource_type') && $request->resource_type) {
            $query->where('resource_type', $request->resource_type);
        }

        $resources = $query->orderBy('created_at', 'desc')->paginate(15);
        $categories = ResourceCategory::all();
        $departments = Department::all();

        return view('admin.resources.index', compact('resources', 'categories', 'departments'));
    }

    public function show(DigitalResource $resource)
    {
        $resource->load(['category', 'department', 'contributor']);
        return view('admin.resources.show', compact('resource'));
    }

    public function approve(DigitalResource $resource)
    {
        $resource->update([
            'status' => 'approved',
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
            'rejected_at' => now(),
        ]);

        return redirect()->back()
            ->with('success', 'Resource rejected.');
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

        return redirect()->route('admin.resources.index')
            ->with('success', 'Resource deleted successfully.');
    }
}
