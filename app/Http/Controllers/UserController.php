<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class UserController extends Controller
{
    /**
     * List user + search/filter.
     */
    public function index(Request $request)
    {
        $q = User::query()->latest();

        // search name/email
        if ($request->filled('q')) {
            $kw = trim($request->q);
            $q->where(function ($sub) use ($kw) {
                $sub->where('name', 'like', "%{$kw}%")
                    ->orWhere('email', 'like', "%{$kw}%");
            });
        }

        // filter role
        if ($request->filled('role')) {
            $q->where('role', $request->role);
        }

        $users = $q->paginate(12)->withQueryString();

        // list role buat dropdown di UI
        $roles = ['admin','produksi','qa','logistik','operator'];

        return view('users.index', compact('users','roles'));
    }

    /**
     * Form create user.
     */
    public function create()
    {
        $roles = ['admin','produksi','qa','logistik','operator'];
        return view('users.create', compact('roles'));
    }

    /**
     * Store user baru.
     */
    public function store(Request $request)
    {
        $roles = ['admin','produksi','qa','logistik','operator'];

        $data = $request->validate([
            'name'     => ['required','string','max:100'],
            'email'    => ['required','email','max:255','unique:users,email'],
            'role'     => ['required', Rule::in($roles)],
            'password' => ['required','string','min:6','max:255'],
        ], [
            'role.in' => 'Role tidak valid.',
        ]);

        $data['password'] = Hash::make($data['password']);

        User::create($data);

        return redirect()
            ->route('users.index')
            ->with('success', 'User berhasil dibuat.');
    }

    /**
     * Form edit.
     */
    public function edit(User $user)
    {
        $roles = ['admin','produksi','qa','logistik','operator'];
        return view('users.edit', compact('user','roles'));
    }

    /**
     * Update data user.
     * Password opsional (kalau kosong tidak diubah).
     */
    public function update(Request $request, User $user)
    {
        $roles = ['admin','produksi','qa','logistik','operator'];

        $data = $request->validate([
            'name'     => ['required','string','max:100'],
            'email'    => ['required','email','max:255', Rule::unique('users','email')->ignore($user->id)],
            'role'     => ['required', Rule::in($roles)],
            'password' => ['nullable','string','min:6','max:255'],
        ], [
            'role.in' => 'Role tidak valid.',
        ]);

        // kalau password diisi -> update
        if (!empty($data['password'])) {
            $data['password'] = Hash::make($data['password']);
        } else {
            unset($data['password']);
        }

        $user->update($data);

        return redirect()
            ->route('users.index')
            ->with('success', 'User berhasil diperbarui.');
    }

    /**
     * Delete user.
     */
    public function destroy(User $user)
    {
        // safety: admin gak boleh hapus diri sendiri
        if (auth()->id() === $user->id) {
            return back()->with('error', 'Tidak bisa menghapus akun sendiri.');
        }

        $user->delete();

        return back()->with('success', 'User berhasil dihapus.');
    }
}
