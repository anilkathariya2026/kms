<?php

namespace App\Http\Controllers\Staff;

use App\Http\Controllers\Controller;
use App\Models\DigitalResource;
use Illuminate\Http\Request;

class ContributionController extends Controller
{
    public function index(Request $request)
    {
        $query = DigitalResource::where('contributor_id', auth()->id());

        // Filter by status
        if ($request->has('status') && $request->status) {
            $query->where('status', $request->status);
        }

        // Filter by resource type
        if ($request->has('resource_type') && $request->resource_type) {
            $query->where('resource_type', $request->resource_type);
        }

        $contributions = $query->orderBy('created_at', 'desc')->paginate(15);

        return view('staff.contributions.index', compact('contributions'));
    }

    public function show(DigitalResource $contribution)
    {
        // Only allow viewing own contributions
        if ($contribution->contributor_id !== auth()->id()) {
            abort(403, 'You can only view your own contributions.');
        }

        $contribution->load(['category', 'department']);
        return view('staff.contributions.show', compact('contribution'));
    }

    public function statistics()
    {
        $totalContributions = DigitalResource::where('contributor_id', auth()->id())->count();
        $approved = DigitalResource::where('contributor_id', auth()->id())
            ->where('status', 'approved')
            ->count();
        $pending = DigitalResource::where('contributor_id', auth()->id())
            ->where('status', 'pending')
            ->count();
        $rejected = DigitalResource::where('contributor_id', auth()->id())
            ->where('status', 'rejected')
            ->count();

        $recentContributions = DigitalResource::where('contributor_id', auth()->id())
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get();

        return view('staff.contributions.statistics', compact(
            'totalContributions',
            'approved',
            'pending',
            'rejected',
            'recentContributions'
        ));
    }
}
