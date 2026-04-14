<?php

namespace Modules\Admin\Controllers;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Inertia\Inertia;
use Inertia\Response;
use Spatie\Permission\Models\Role;

class UserManagementController extends Controller
{
    public function index(): Response
    {
        $users = User::with('roles')
            ->orderBy('name')
            ->paginate(30);

        return Inertia::render('admin/Users', [
            'users' => $users,
            'roles' => Role::orderBy('name')->get(['id', 'name']),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'name'     => ['required', 'string', 'max:150'],
            'email'    => ['required', 'email', 'unique:users,email'],
            'password' => ['required', 'string', 'min:8'],
            'role'     => ['required', 'exists:roles,name'],
        ]);

        $user = User::create([
            'name'     => $data['name'],
            'email'    => $data['email'],
            'password' => Hash::make($data['password']),
        ]);

        $user->assignRole($data['role']);

        return back()->with('success', 'تم إنشاء المستخدم بنجاح.');
    }

    public function updateRole(Request $request, int $id): RedirectResponse
    {
        $user = User::findOrFail($id);

        $data = $request->validate([
            'role' => ['required', 'exists:roles,name'],
        ]);

        $user->syncRoles([$data['role']]);

        return back()->with('success', 'تم تحديث دور المستخدم بنجاح.');
    }
}
