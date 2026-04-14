<?php

namespace Modules\Admin\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Inertia\Response;

class ActivityLogController extends Controller
{
    public function index(): Response
    {
        $filters = request()->only(['module', 'user_id', 'from', 'to', 'search']);

        $logs = DB::table('activity_logs')
            ->leftJoin('users', 'activity_logs.user_id', '=', 'users.id')
            ->select('activity_logs.*', 'users.name as user_name')
            ->when($filters['module'] ?? null,  fn ($q, $v) => $q->where('activity_logs.module', $v))
            ->when($filters['user_id'] ?? null, fn ($q, $v) => $q->where('activity_logs.user_id', $v))
            ->when($filters['from']   ?? null,  fn ($q, $v) => $q->whereDate('activity_logs.created_at', '>=', $v))
            ->when($filters['to']     ?? null,  fn ($q, $v) => $q->whereDate('activity_logs.created_at', '<=', $v))
            ->when($filters['search'] ?? null,  fn ($q, $v) => $q->where('activity_logs.description', 'like', "%{$v}%"))
            ->orderByDesc('activity_logs.created_at')
            ->paginate(50);

        $users   = DB::table('users')->select('id', 'name')->orderBy('name')->get();
        $modules = DB::table('activity_logs')->distinct()->orderBy('module')->pluck('module');

        return Inertia::render('admin/ActivityLog', [
            'logs'    => $logs,
            'users'   => $users,
            'modules' => $modules,
            'filters' => $filters,
        ]);
    }
}
