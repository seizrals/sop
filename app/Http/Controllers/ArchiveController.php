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
        $search = trim((string) $request->query('q', ''));

        $query = SopDocument::with(['team', 'activity', 'creator', 'updater'])
            ->whereNotNull('signed_file_path')
            ->when($teamId, fn ($query) => $query->where('team_id', $teamId))
            ->when($activityId, fn ($query) => $query->where('team_activity_id', $activityId))
            ->when($year, fn ($query) => $query->where('year', $year))
            ->when(filled($search), function ($query) use ($search) {
                $query->where(function ($subQuery) use ($search) {
                    $subQuery->where('title', 'like', '%' . $search . '%')
                        ->orWhere('sop_number', 'like', '%' . $search . '%')
                        ->orWhere('notes', 'like', '%' . $search . '%')
                        ->orWhere('year', (string) $search);
                });
            })
            ->orderByDesc('year')
            ->orderBy('title')
            ->orderByDesc('revision_number');

        $page = (int) $request->query('page', 1);
        $perPage = 10;

        $allDocuments = $query->get();

        $groupKeys = $allDocuments
            ->map(fn ($doc) => $doc->root_document_id ?: $doc->id)
            ->filter()
            ->unique()
            ->values();

        $offset = ($page - 1) * $perPage;
        $paginatedKeys = $groupKeys->slice($offset, $perPage)->values();
        $totalGroups = $groupKeys->count();

        $paginator = new \Illuminate\Pagination\LengthAwarePaginator(
            $paginatedKeys,
            $totalGroups,
            $perPage,
            $page,
            [
                'path' => $request->url(),
                'query' => $request->query(),
            ]
        );

        $visibleDocuments = $allDocuments->filter(function (SopDocument $document) use ($paginatedKeys) {
            $rootKey = $document->root_document_id ?: $document->id;
            return $paginatedKeys->contains($rootKey);
        });

        return view('archives.index', [
            'pageTitle' => 'Arsip Dokumen SOP',
            'teams' => Team::orderBy('display_name')->get(),
            'activities' => TeamActivity::when($teamId, fn ($query) => $query->where('team_id', $teamId))
                ->orderBy('name')
                ->get(),
            'selectedTeam' => $teamId,
            'selectedActivity' => $activityId,
            'selectedYear' => $year,
            'search' => $search,
            'documents' => $visibleDocuments,
            'groups' => $paginator,
        ]);
    }
}
