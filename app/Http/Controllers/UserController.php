<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class UserController extends Controller
{
    /**
     * Role list (single source of truth).
     */
    private array $roles = ['admin','produksi','qa','logistik','operator'];

    /**
     * Status list.
     */
    private array $statuses = ['active','inactive','suspended'];

    /**
     * List user + search/filter.
     */
    public function index(Request $request)
    {
        $this->authorizeAdmin();

        $q = User::query()->latest();

        // search name/email/username/employee_code/phone
        if ($request->filled('q')) {
            $kw = trim($request->q);
            $q->where(function ($sub) use ($kw) {
                $sub->where('name', 'like', "%{$kw}%")
                    ->orWhere('email', 'like', "%{$kw}%")
                    ->orWhere('username', 'like', "%{$kw}%")
                    ->orWhere('employee_code', 'like', "%{$kw}%")
                    ->orWhere('phone', 'like', "%{$kw}%");
            });
        }

        // filter role
        if ($request->filled('role')) {
            $q->where('role', $request->role);
        }

        // filter status
        if ($request->filled('status')) {
            $q->where('status', $request->status);
        }

        // filter department
        if ($request->filled('department')) {
            $dept = trim($request->department);
            $q->where('department','like',"%{$dept}%");
        }

        // filter company/site optional
        if ($request->filled('company')) {
            $cmp = trim($request->company);
            $q->where('company','like',"%{$cmp}%");
        }
        if ($request->filled('site')) {
            $site = trim($request->site);
            $q->where('site','like',"%{$site}%");
        }

        $users = $q->paginate(12)->withQueryString();

        return view('users.index', [
            'users'   => $users,
            'roles'   => $this->roles,
            'statuses'=> $this->statuses,
            'filters' => $request->only(['q','role','status','department','company','site'])
        ]);
    }

    /**
     * Form create user.
     */
    public function create()
    {
        $this->authorizeAdmin();
        return view('users.create', [
            'roles'    => $this->roles,
            'statuses' => $this->statuses,
        ]);
    }

    /**
     * Store user baru.
     */
    public function store(Request $request)
    {
        $this->authorizeAdmin();

        $data = $request->validate([
            'name'          => ['required','string','max:100'],
            'username'      => ['nullable','string','max:60','unique:users,username'],
            'email'         => ['required','email','max:255','unique:users,email'],
            'password'      => ['required','string','min:6','max:255'],

            'employee_code' => ['nullable','string','max:50'],
            'phone'         => ['nullable','string','max:30'],
            'company'       => ['nullable','string','max:120'],
            'department'    => ['nullable','string','max:120'],
            'position'      => ['nullable','string','max:120'],
            'site'          => ['nullable','string','max:120'],
            'join_date'     => ['nullable','date'],

            'role'          => ['required', Rule::in($this->roles)],
            'status'        => ['required', Rule::in($this->statuses)],

            'notes'         => ['nullable','string'],
            'avatar_path'   => ['nullable','string','max:255'], // kalau admin isi path manual
        ], [
            'role.in'   => 'Role tidak valid.',
            'status.in' => 'Status tidak valid.',
        ]);

        $data['password'] = Hash::make($data['password']);

        // audit
        $data['created_by'] = auth()->id();
        $data['updated_by'] = auth()->id();

        User::create($data);

        return redirect()
            ->route('users.index')
            ->with('success', 'User berhasil dibuat.');
    }

    /**
     * Form edit user.
     */
    public function edit(User $user)
    {
        $this->authorizeAdmin();
        return view('users.edit', [
            'user'     => $user,
            'roles'    => $this->roles,
            'statuses' => $this->statuses,
        ]);
    }

    /**
     * Update data user.
     * Password opsional (kalau kosong tidak diubah).
     */
    public function update(Request $request, User $user)
    {
        $this->authorizeAdmin();

        $data = $request->validate([
            'name'          => ['required','string','max:100'],
            'username'      => ['nullable','string','max:60', Rule::unique('users','username')->ignore($user->id)],
            'email'         => ['required','email','max:255', Rule::unique('users','email')->ignore($user->id)],
            'password'      => ['nullable','string','min:6','max:255'],

            'employee_code' => ['nullable','string','max:50'],
            'phone'         => ['nullable','string','max:30'],
            'company'       => ['nullable','string','max:120'],
            'department'    => ['nullable','string','max:120'],
            'position'      => ['nullable','string','max:120'],
            'site'          => ['nullable','string','max:120'],
            'join_date'     => ['nullable','date'],

            'role'          => ['required', Rule::in($this->roles)],
            'status'        => ['required', Rule::in($this->statuses)],

            'notes'         => ['nullable','string'],
            'avatar_path'   => ['nullable','string','max:255'],
        ], [
            'role.in'   => 'Role tidak valid.',
            'status.in' => 'Status tidak valid.',
        ]);

        // safety: jangan bikin admin terakhir jadi non-admin
        if ($user->role === 'admin' && $data['role'] !== 'admin') {
            $adminCount = User::where('role','admin')->count();
            if ($adminCount <= 1) {
                return back()->with('error', 'Tidak bisa mengubah role admin terakhir.');
            }
        }

        // safety: jangan bikin admin terakhir jadi inactive/suspended
        if ($user->role === 'admin' && in_array($data['status'], ['inactive','suspended'], true)) {
            $activeAdminCount = User::where('role','admin')->where('status','active')->count();
            if ($activeAdminCount <= 1) {
                return back()->with('error', 'Tidak bisa menonaktifkan admin terakhir.');
            }
        }

        // kalau password diisi -> update
        if (!empty($data['password'])) {
            $data['password'] = Hash::make($data['password']);
        } else {
            unset($data['password']);
        }

        // audit
        $data['updated_by'] = auth()->id();

        $user->update($data);

        return redirect()
            ->route('users.index')
            ->with('success', 'User berhasil diperbarui.');
    }

    /**
     * Quick reset password.
     */
    public function resetPassword(Request $request, User $user)
    {
        $this->authorizeAdmin();

        $data = $request->validate([
            'password' => ['required','string','min:6','max:255'],
        ]);

        $user->password = Hash::make($data['password']);
        $user->updated_by = auth()->id();
        $user->save();

        return back()->with('success', 'Password user berhasil direset.');
    }

    /**
     * Toggle status active <-> inactive.
     * (pengganti toggleActive versi lama)
     */
    public function toggleActive(User $user)
    {
        $this->authorizeAdmin();

        // safety: jangan matikan admin terakhir
        if ($user->role === 'admin' && $user->status === 'active') {
            $activeAdminCount = User::where('role','admin')->where('status','active')->count();
            if ($activeAdminCount <= 1) {
                return back()->with('error', 'Tidak bisa menonaktifkan admin terakhir.');
            }
        }

        $user->status = ($user->status === 'active') ? 'inactive' : 'active';
        $user->updated_by = auth()->id();
        $user->save();

        return back()->with('success', 'Status user berhasil diubah.');
    }

    /**
     * Delete user (soft delete karena model pakai SoftDeletes).
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
