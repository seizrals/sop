<?php

namespace App\Http\Controllers;

use App\Models\Team;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class UserManagementController extends Controller
{
    public function index(): View
    {
        return view('users.index', [
            'pageTitle' => 'Manajemen Pengguna',
            'teams' => Team::orderBy('display_name')->get(),
            'users' => User::with('team')->orderBy('role')->orderBy('name')->get(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email'],
            'nip' => ['nullable', 'string', 'max:255'],
            'position' => ['nullable', 'string', 'max:255'],
            'team_id' => ['nullable', 'exists:teams,id'],
            'role' => ['required', 'in:admin,ketua_tim,anggota_tim,kepala'],
            'password' => ['required', 'string', 'min:8'],
        ]);

        User::create($validated);

        return back()->with('success', 'Pengguna berhasil ditambahkan.');
    }

    public function update(Request $request, User $user): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email,' . $user->id],
            'nip' => ['nullable', 'string', 'max:255'],
            'position' => ['nullable', 'string', 'max:255'],
            'team_id' => ['nullable', 'exists:teams,id'],
            'role' => ['required', 'in:admin,ketua_tim,anggota_tim,kepala'],
            'is_active' => ['nullable', 'boolean'],
            'password' => ['nullable', 'string', 'min:8'],
        ]);

        if (! filled($validated['password'] ?? null)) {
            unset($validated['password']);
        }

        $validated['is_active'] = $request->boolean('is_active');

        $user->update($validated);

        return back()->with('success', 'Pengguna berhasil diperbarui.');
    }
}
