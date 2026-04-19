<?php

namespace Modules\Admin\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class RoleController extends Controller
{
    public function index(): Response
    {
        $roles = Role::with('permissions')->orderBy('name')->get();
        $permissions = Permission::orderBy('name')->get();

        return Inertia::render('admin/Roles', [
            'roles' => $roles,
            'allPermissions' => $permissions,
        ]);
    }

    public function updatePermissions(Request $request, string $roleId): RedirectResponse
    {
        $data = $request->validate([
            'permissions' => ['nullable', 'array'],
            'permissions.*' => ['string', 'exists:permissions,name'],
        ]);

        $role = Role::findOrFail($roleId);
        $role->syncPermissions($data['permissions'] ?? []);

        return back()->with('success', "تم تحديث صلاحيات دور {$role->name} بنجاح.");
    }
}
