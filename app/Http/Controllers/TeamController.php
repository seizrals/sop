<?php

namespace App\Http\Controllers;

use App\Models\Team;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class TeamController extends Controller
{
    public function index(): View
    {
        return view('teams.index', [
            'pageTitle' => 'Kelola Tim',
            'teams' => Team::withCount(['users', 'activities', 'sops'])
                ->orderBy('display_name')
                ->get(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'code' => ['required', 'string', 'max:50', 'unique:teams,code', 'alpha_dash'],
            'name' => ['required', 'string', 'max:255'],
            'display_name' => ['required', 'string', 'max:255'],
            'leader_name' => ['nullable', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:1000'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        $validated['is_active'] = $request->boolean('is_active', true);

        Team::create($validated);

        return back()->with('success', 'Tim berhasil ditambahkan.');
    }

    public function update(Request $request, Team $team): RedirectResponse
    {
        $validated = $request->validate([
            'code' => ['required', 'string', 'max:50', 'unique:teams,code,' . $team->id, 'alpha_dash'],
            'name' => ['required', 'string', 'max:255'],
            'display_name' => ['required', 'string', 'max:255'],
            'leader_name' => ['nullable', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:1000'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        $validated['is_active'] = $request->boolean('is_active', true);

        $team->update($validated);

        return back()->with('success', 'Tim berhasil diperbarui.');
    }

    public function destroy(Team $team): RedirectResponse
    {
        $hasUsers = $team->users()->exists();
        $hasActivities = $team->activities()->exists();
        $hasSops = $team->sops()->exists();

        if ($hasUsers || $hasActivities || $hasSops) {
            $relations = [];
            if ($hasUsers) {
                $relations[] = $team->users_count . ' pengguna';
            }
            if ($hasActivities) {
                $relations[] = $team->activities_count . ' kegiatan';
            }
            if ($hasSops) {
                $relations[] = $team->sops_count . ' dokumen SOP';
            }

            return back()->with('error', 'Tim tidak dapat dihapus karena masih memiliki: ' . implode(', ', $relations) . '.');
        }

        $team->delete();

        return back()->with('success', 'Tim berhasil dihapus.');
    }
}
