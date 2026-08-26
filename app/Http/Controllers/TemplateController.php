<?php

namespace App\Http\Controllers;

use App\Models\SopTemplate;
use App\Models\Team;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class TemplateController extends Controller
{
    public function index(Request $request): View
    {
        $teamId = $request->integer('team');

        return view('templates.index', [
            'pageTitle' => 'Template SOP',
            'teams' => Team::orderBy('display_name')->get(),
            'selectedTeam' => $teamId,
            'templates' => SopTemplate::with(['team', 'activity', 'sourceSop'])
                ->when($teamId, fn ($query) => $query->where('team_id', $teamId))
                ->latest()
                ->get(),
        ]);
    }

    public function destroy(SopTemplate $template): RedirectResponse
    {
        $template->delete();

        return back()->with('success', 'Template SOP berhasil dihapus.');
    }
}
