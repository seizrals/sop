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

        $perPage = 5;

        $documents = SopDocument::with(['team', 'activity', 'creator', 'updater', 'rootDocument'])
            ->whereNotNull('signed_file_path')
            ->when($teamId, fn ($query) => $query->where('team_id', $teamId))
            ->when($activityId, fn ($query) => $query->where('team_activity_id', $activityId))
            ->when($year, fn ($query) => $query->where('year', $year))
            ->when(filled($search), function ($query) use ($search) {
                $query->where(function ($subQuery) use ($search) {
                    $subQuery->where('title', 'like', '%' . $search . '%')
                        ->orWhere('sop_number', 'like', '%' . $search . '%')
                        ->orWhereHas('team', fn ($q) => $q->where('display_name', 'like', '%' . $search . '%'))
                        ->orWhereHas('activity', fn ($q) => $q->where('name', 'like', '%' . $search . '%'))
                        ->orWhere('year', (string) $search);
                });
            })
            ->orderByDesc('year')
            ->orderBy('title')
            ->orderByDesc('revision_number')
            ->get();

        $normalizedGroups = $documents
            ->map(function ($document) {
                preg_match('/(\d{4})(?!.*\d)/', (string) $document->sop_number, $matches);
                $displayYear = $matches[1] ?? $document->year;
                $updatedAt = $document->updated_at?->timestamp ?? $document->created_at?->timestamp ?? 0;
                $updaterName = $document->updater?->name ?? $document->creator?->name ?? '-';
                $updaterInitials = str($updaterName)->trim()->explode(' ')->filter()->take(2)->map(fn ($p) => str($p)->substr(0, 1)->upper())->implode('') ?: '-';

                return [
                    'model' => $document,
                    'title' => $document->title,
                    'team' => $document->team?->display_name ?: '-',
                    'activity' => $document->activity?->name ?: '-',
                    'year' => $displayYear ?: '-',
                    'status' => $document->status,
                    'sop_number' => $document->sop_number ?: '-',
                    'revision' => $document->status === 'revisi' ? ($document->revision_number ?: 1) : '-',
                    'revision_number' => (int) ($document->revision_number ?? 0),
                    'group_key' => $document->root_document_id ?: $document->id,
                    'updated_at' => $updatedAt,
                    'updater_name' => $updaterName,
                    'updater_initials' => $updaterInitials,
                    'signed_at' => $document->signed_at?->timestamp ?? 0,
                ];
            })
            ->groupBy('group_key')
            ->map(function ($versions) {
                $sorted = $versions->sortByDesc(function ($document) {
                    return [
                        $document['status'] === 'revisi' ? 2 : 1,
                        $document['revision_number'],
                        $document['updated_at'],
                        $document['model']->id,
                    ];
                })->values();

                $latestCandidate = $sorted->first();
                $historyCandidates = $sorted->slice(1)->values();

                $latest = $latestCandidate;
                $history = $historyCandidates;
                if (($latestCandidate['status'] ?? null) !== 'final') {
                    $historyFinal = $historyCandidates->firstWhere('status', 'final');
                    if ($historyFinal) {
                        $latest = $historyFinal;
                        $history = $historyCandidates
                            ->reject(fn ($row) => (int) $row['model']->id === (int) $historyFinal['model']->id)
                            ->values()
                            ->push($latestCandidate)
                            ->values();
                    }
                }

                return [
                    'latest' => $latest,
                    'history' => $history,
                    '_sort' => strtolower(($latest['activity'] ?? '-') . '|' . ($latest['title'] ?? '-')),
                ];
            })
            ->filter(function ($group) {
                return ($group['latest']['status'] ?? null) === 'final';
            })
            ->sortBy('_sort')
            ->values();

        $totalGroups = $normalizedGroups->count();
        $page = (int) $request->query('page', 1);
        $offset = ($page - 1) * $perPage;
        $paginatedGroups = $normalizedGroups->slice($offset, $perPage)->values();

        $paginator = new \Illuminate\Pagination\LengthAwarePaginator(
            $paginatedGroups,
            $totalGroups,
            $perPage,
            $page,
            [
                'path' => $request->url(),
                'query' => $request->query(),
            ]
        );

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
            'totalCount' => $totalGroups,
            'documents' => $documents,
            'groups' => $paginatedGroups,
            'paginator' => $paginator,
        ]);
    }
}
