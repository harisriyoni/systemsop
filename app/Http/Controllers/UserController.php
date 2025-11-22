<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Schema;

class UserController extends Controller
{
    /**
     * Role list (single source of truth).
     */
    private array $roles = ['admin','produksi','qa','logistik','operator'];

    /**
     * List user + search/filter.
     */
    public function index(Request $request)
    {
        $this->authorizeAdmin();

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

        // filter active/inactive (optional kalau kolom ada)
        if (Schema::hasColumn('users', 'is_active') && $request->filled('is_active')) {
            $q->where('is_active', (bool)$request->is_active);
        }

        // optional filter department / line kalau user punya kolom ini
        if (Schema::hasColumn('users','department') && $request->filled('department')) {
            $dept = trim($request->department);
            $q->where('department','like',"%{$dept}%");
        }
        if (Schema::hasColumn('users','line') && $request->filled('line')) {
            $line = trim($request->line);
            $q->where('line','like',"%{$line}%");
        }

        $users = $q->paginate(12)->withQueryString();

        return view('users.index', [
            'users' => $users,
            'roles' => $this->roles,
            'filters' => $request->only(['q','role','is_active','department','line'])
        ]);
    }

    /**
     * Form create user.
     */
    public function create()
    {
        $this->authorizeAdmin();
        return view('users.create', ['roles' => $this->roles]);
    }

    /**
     * Store user baru.
     */
    public function store(Request $request)
    {
        $this->authorizeAdmin();

        $data = $request->validate([
            'name'       => ['required','string','max:100'],
            'email'      => ['required','email','max:255','unique:users,email'],
            'role'       => ['required', Rule::in($this->roles)],
            'password'   => ['required','string','min:6','max:255'],

            // optional cols
            'department' => [Schema::hasColumn('users','department') ? 'nullable' : 'sometimes','string','max:100'],
            'line'       => [Schema::hasColumn('users','line') ? 'nullable' : 'sometimes','string','max:100'],
        ], [
            'role.in' => 'Role tidak valid.',
        ]);

        $data['password'] = Hash::make($data['password']);

        if (Schema::hasColumn('users','is_active')) {
            $data['is_active'] = true;
        }

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
        $this->authorizeAdmin();
        return view('users.edit', ['user' => $user, 'roles' => $this->roles]);
    }

    /**
     * Update data user.
     * Password opsional (kalau kosong tidak diubah).
     */
    public function update(Request $request, User $user)
    {
        $this->authorizeAdmin();

        $data = $request->validate([
            'name'     => ['required','string','max:100'],
            'email'    => ['required','email','max:255', Rule::unique('users','email')->ignore($user->id)],
            'role'     => ['required', Rule::in($this->roles)],
            'password' => ['nullable','string','min:6','max:255'],

            // optional cols
            'department' => [Schema::hasColumn('users','department') ? 'nullable' : 'sometimes','string','max:100'],
            'line'       => [Schema::hasColumn('users','line') ? 'nullable' : 'sometimes','string','max:100'],
            'is_active'  => [Schema::hasColumn('users','is_active') ? 'nullable' : 'sometimes','boolean'],
        ], [
            'role.in' => 'Role tidak valid.',
        ]);

        // safety: jangan bikin admin terakhir jadi non-admin
        if ($user->role === 'admin' && $data['role'] !== 'admin') {
            $adminCount = User::where('role','admin')->count();
            if ($adminCount <= 1) {
                return back()->with('error', 'Tidak bisa mengubah role admin terakhir.');
            }
        }

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
     * Quick reset password (opsional route kalau mau).
     * Bisa kamu panggil dari edit page pakai form kecil.
     */
    public function resetPassword(Request $request, User $user)
    {
        $this->authorizeAdmin();

        $data = $request->validate([
            'password' => ['required','string','min:6','max:255'],
        ]);

        $user->password = Hash::make($data['password']);
        $user->save();

        return back()->with('success', 'Password user berhasil direset.');
    }

    /**
     * Toggle active/inactive (opsional route).
     * Lebih aman daripada delete.
     */
    public function toggleActive(User $user)
    {
        $this->authorizeAdmin();

        if (!Schema::hasColumn('users','is_active')) {
            return back()->with('error', 'Kolom is_active belum ada.');
        }

        // safety: jangan matikan admin terakhir
        if ($user->role === 'admin' && $user->is_active) {
            $activeAdminCount = User::where('role','admin')->where('is_active',true)->count();
            if ($activeAdminCount <= 1) {
                return back()->with('error', 'Tidak bisa menonaktifkan admin terakhir.');
            }
        }

        $user->is_active = !$user->is_active;
        $user->save();

        return back()->with('success', 'Status user berhasil diubah.');
    }

    /**
     * Delete user (hard delete).
     */
    public function destroy(User $user)
    {
        $this->authorizeAdmin();

        // safety: admin gak boleh hapus diri sendiri
        if (auth()->id() === $user->id) {
            return back()->with('error', 'Tidak bisa menghapus akun sendiri.');
        }

        // safety: jangan hapus admin terakhir
        if ($user->role === 'admin') {
            $adminCount = User::where('role','admin')->count();
            if ($adminCount <= 1) {
                return back()->with('error', 'Tidak bisa menghapus admin terakhir.');
            }
        }

        $user->delete();

        return back()->with('success', 'User berhasil dihapus.');
    }

    // =========================
    // HELPER
    // =========================
    private function authorizeAdmin()
    {
        if (!auth()->user() || !auth()->user()->isRole(['admin'])) {
            abort(403, 'Hanya admin yang boleh mengelola user.');
        }
    }
}
