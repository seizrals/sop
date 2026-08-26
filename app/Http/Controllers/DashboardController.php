<?php

namespace App\Http\Controllers;

use App\Models\SopDocument;
use App\Models\SopTemplate;
use App\Models\Team;
use App\Models\User;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(): View
    {
        return view('dashboard.index', [
            'pageTitle' => 'Dashboard',
            'teams' => Team::withCount(['activities', 'sops'])->orderBy('display_name')->get(),
            'latestDocuments' => SopDocument::with(['team', 'activity'])->latest()->limit(6)->get(),
            'statusCounts' => [
                'draft' => SopDocument::where('status', 'draft')->count(),
                'revisi' => SopDocument::where('status', 'revisi')->count(),
                'final' => SopDocument::where('status', 'final')->count(),
            ],
            'totals' => [
                'dokumen' => SopDocument::count(),
                'template' => SopTemplate::count(),
                'pengguna' => User::count(),
                'tim' => Team::count(),
            ],
            'archivesByYear' => SopDocument::query()
                ->selectRaw('year, COUNT(*) as total')
                ->groupBy('year')
                ->orderByDesc('year')
                ->limit(6)
                ->get(),
        ]);
    }
}
