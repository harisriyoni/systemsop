<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

class ProfileController extends Controller
{
    /**
     * Tampilkan profile user yang sedang login.
     */
    public function show()
    {
        $user = Auth::user();
        return view('profile.show', compact('user'));
    }

    /**
     * Form edit profile sendiri.
     */
    public function edit()
    {
        $user = Auth::user();
        return view('profile.edit', compact('user'));
    }

    /**
     * Update data profile user sendiri.
     * Field sensitif (role, status, employee_code, notes, created_by, updated_by) TIDAK diubah di sini.
     */
    public function update(Request $request)
    {
        $user = Auth::user();

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:120'],

            // username optional, tapi kalau diisi harus unik
            'username' => [
                'nullable', 'string', 'max:60',
                Rule::unique('users', 'username')->ignore($user->id),
            ],

            // email optional diubah, kalau berubah reset verifikasi
            'email' => [
                'required', 'email', 'max:120',
                Rule::unique('users', 'email')->ignore($user->id),
            ],

            'phone' => ['nullable', 'string', 'max:30'],

            // silakan kalau mau mereka bisa isi sendiri; kalau enggak mau user ubah, tinggal hapus 4 field ini dari validate & assign
            'company' => ['nullable', 'string', 'max:120'],
            'department' => ['nullable', 'string', 'max:120'],
            'position' => ['nullable', 'string', 'max:120'],
            'site' => ['nullable', 'string', 'max:120'],
            'join_date' => ['nullable', 'date'],
        ]);

        // kalau email berubah -> reset verifikasi
        if ($validated['email'] !== $user->email) {
            $user->email_verified_at = null;
        }

        $user->fill([
            'name'       => $validated['name'],
            'username'   => $validated['username'] ?? $user->username,
            'email'      => $validated['email'],
            'phone'      => $validated['phone'] ?? $user->phone,
            'company'    => $validated['company'] ?? $user->company,
            'department' => $validated['department'] ?? $user->department,
            'position'   => $validated['position'] ?? $user->position,
            'site'       => $validated['site'] ?? $user->site,
            'join_date'  => $validated['join_date'] ?? $user->join_date,
        ]);

        // audit (kalau kolomnya ada)
        if ($user->isFillable('updated_by')) {
            $user->updated_by = $user->id;
        }

        $user->save();

        return redirect()
            ->route('profile.edit')
            ->with('success', 'Profile berhasil diperbarui.');
    }

    /**
     * Update password user sendiri.
     */
    public function updatePassword(Request $request)
    {
        $user = Auth::user();

        $request->validate([
            'current_password' => ['required'],
            'password' => ['required', 'string', 'min:8', 'confirmed'], 
            // butuh field password_confirmation
        ]);

        // cek password lama valid atau nggak
        if (!Hash::check($request->current_password, $user->password)) {
            return back()->withErrors([
                'current_password' => 'Password lama tidak sesuai.',
            ]);
        }

        // update password
        $user->password = Hash::make($request->password);

        if ($user->isFillable('updated_by')) {
            $user->updated_by = $user->id;
        }

        $user->save();

        return back()->with('success', 'Password berhasil diubah.');
    }

    /**
     * Upload / ganti avatar user sendiri.
     */
    public function updateAvatar(Request $request)
    {
        $user = Auth::user();

        $request->validate([
            'avatar' => [
                'required',
                'image',
                'mimes:jpg,jpeg,png,webp',
                'max:2048', // 2MB
            ]
        ]);

        // hapus avatar lama jika ada
        if ($user->avatar_path && Storage::disk('public')->exists($user->avatar_path)) {
            Storage::disk('public')->delete($user->avatar_path);
        }

        // simpan avatar baru
        // hasil path contoh: avatars/12345_abcd.webp
        $path = $request->file('avatar')->store('avatars', 'public');

        $user->avatar_path = $path;

        if ($user->isFillable('updated_by')) {
            $user->updated_by = $user->id;
        }

        $user->save();

        return back()->with('success', 'Avatar berhasil diperbarui.');
    }

    /**
     * Hapus avatar (balik default).
     */
    public function deleteAvatar()
    {
        $user = Auth::user();

        if ($user->avatar_path && Storage::disk('public')->exists($user->avatar_path)) {
            Storage::disk('public')->delete($user->avatar_path);
        }

        $user->avatar_path = null;

        if ($user->isFillable('updated_by')) {
            $user->updated_by = $user->id;
        }

        $user->save();

        return back()->with('success', 'Avatar berhasil dihapus.');
    }
}
