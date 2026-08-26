<?php

namespace App\Http\Controllers;

use App\Models\SopDocument;
use App\Models\Team;
use App\Models\TeamActivity;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ArchiveController extends Controller
{
    public function index(Request $request): View
    {
        $teamId = $request->integer('team');
        $activityId = $request->integer('activity');
        $year = $request->integer('year');

        return view('archives.index', [
            'pageTitle' => 'Arsip Dokumen SOP',
            'teams' => Team::orderBy('display_name')->get(),
            'activities' => TeamActivity::when($teamId, fn ($query) => $query->where('team_id', $teamId))
                ->orderBy('name')
                ->get(),
            'selectedTeam' => $teamId,
            'selectedActivity' => $activityId,
            'selectedYear' => $year,
            'documents' => SopDocument::with(['team', 'activity', 'creator'])
                ->when($teamId, fn ($query) => $query->where('team_id', $teamId))
                ->when($activityId, fn ($query) => $query->where('team_activity_id', $activityId))
                ->when($year, fn ($query) => $query->where('year', $year))
                ->orderByDesc('year')
                ->orderBy('title')
                ->orderByDesc('revision_number')
                ->get(),
        ]);
    }
}
