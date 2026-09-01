<?php

namespace App\Http\Controllers;

use App\Models\Team;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
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
            'display_name' => ['required', 'string', 'max:255'],
            'leader_name' => ['nullable', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:1000'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        $validated['is_active'] = $request->boolean('is_active', true);
        $validated['name'] = Str::slug($validated['display_name'], '_');
        $validated['code'] = $this->generateUniqueCode($validated['display_name']);

        Team::create($validated);

        return back()->with('success', 'Tim berhasil ditambahkan.');
    }

    public function update(Request $request, Team $team): RedirectResponse
    {
        $validated = $request->validate([
            'display_name' => ['required', 'string', 'max:255'],
            'leader_name' => ['nullable', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:1000'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        $validated['is_active'] = $request->boolean('is_active', true);

        if ($team->display_name !== $validated['display_name']) {
            $validated['name'] = Str::slug($validated['display_name'], '_');
            $validated['code'] = $this->generateUniqueCode($validated['display_name'], $team->id);
        }

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

    private function generateUniqueCode(string $displayName, ?int $ignoreId = null): string
    {
        $base = Str::slug($displayName);
        $candidate = $base;
        $counter = 2;

        while (true) {
            $query = Team::where('code', $candidate);
            if ($ignoreId !== null) {
                $query->where('id', '!=', $ignoreId);
            }
            if (! $query->exists()) {
                break;
            }
            $candidate = $base . '-' . $counter++;
        }

        return $candidate;
    }
}
