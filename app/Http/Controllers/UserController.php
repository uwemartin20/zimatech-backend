<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{
    public function profile()
    {
        $user = Auth::user();

        return view('user.profile.index', compact('user'));
    }

    /**
     * Update the authenticated user's profile.
     */
    public function updateProfile(Request $request)
    {
        $user = Auth::user();

        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users,email,'.$user->id,
            'company' => 'nullable|string|in:ZF,ZT',
            'password' => 'nullable|string|min:6|confirmed',
        ]);

        $data = $request->only('name', 'email', 'company');
        if ($request->filled('password')) {
            $data['password'] = Hash::make($request->password);
        }

        $user->update($data);

        return redirect()
            ->route('profile')
            ->with('success', 'Profile updated successfully.');
    }
}
