<?php

namespace Modules\Admin\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Inertia\Response;

class SettingsController extends Controller
{
    public function index(): Response
    {
        $settings = DB::table('settings')
            ->orderBy('group')
            ->orderBy('key')
            ->get()
            ->keyBy('key');

        return Inertia::render('admin/Settings', [
            'settings' => $settings,
        ]);
    }

    public function update(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'settings'              => ['required', 'array'],
            'settings.*.key'        => ['required', 'string'],
            'settings.*.value'      => ['nullable', 'string'],
        ]);

        foreach ($data['settings'] as $setting) {
            DB::table('settings')
                ->where('key', $setting['key'])
                ->update(['value' => $setting['value'] ?? '']);
        }

        return back()->with('success', 'تم حفظ الإعدادات بنجاح.');
    }
}
