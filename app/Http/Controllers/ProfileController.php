<?php

namespace App\Http\Controllers;

use App\Http\Requests\ProfileUpdateRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Str;
use Illuminate\View\View;

class ProfileController extends Controller
{
    /**
     * Display the user's profile form.
     */
    public function edit(Request $request): View
    {
        return view('profile.edit', [
            'pageTitle' => 'Profil',
            'user' => $request->user(),
        ]);
    }

    /**
     * Update the user's profile information.
     */
    public function update(ProfileUpdateRequest $request): RedirectResponse
    {
        $user = $request->user();
        $validated = $request->validated();

        $user->fill(collect($validated)->except(['profile_photo', 'remove_photo'])->all());

        if ($user->isDirty('email')) {
            $user->email_verified_at = null;
        }

        if ($request->boolean('remove_photo')) {
            $this->deleteProfilePhoto($user->profile_photo_path);
            $user->profile_photo_path = null;
        }

        if ($request->hasFile('profile_photo')) {
            $this->deleteProfilePhoto($user->profile_photo_path);

            $directory = public_path('uploads/profile-photos');

            if (! File::exists($directory)) {
                File::makeDirectory($directory, 0755, true);
            }

            $extension = $request->file('profile_photo')->getClientOriginalExtension();
            $fileName = Str::uuid() . '.' . strtolower($extension);
            $request->file('profile_photo')->move($directory, $fileName);
            $user->profile_photo_path = 'uploads/profile-photos/' . $fileName;
        }

        $user->save();

        return Redirect::route('profile.edit')->with('status', 'profile-updated');
    }

    /**
     * Delete the user's account.
     */
    public function destroy(Request $request): RedirectResponse
    {
        $request->validateWithBag('userDeletion', [
            'password' => ['required', 'current_password'],
        ]);

        $user = $request->user();

        Auth::logout();

        $this->deleteProfilePhoto($user->profile_photo_path);
        $user->delete();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return Redirect::to('/');
    }

    private function deleteProfilePhoto(?string $relativePath): void
    {
        if (! $relativePath) {
            return;
        }

        $absolutePath = public_path($relativePath);

        if (File::exists($absolutePath)) {
            File::delete($absolutePath);
        }
    }
}
