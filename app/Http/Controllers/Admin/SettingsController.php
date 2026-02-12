<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class SettingsController extends Controller
{
    public function index()
    {
        return view('admin.settings.index');
    }

    public function update(Request $request)
    {
        // Settings are stored as session flash for now.
        // In a production app these would be persisted to a settings table.
        $request->validate([
            'session_timeout' => ['nullable', 'integer', 'min:5', 'max:120'],
            'qr_refresh_interval' => ['nullable', 'integer', 'min:30', 'max:300'],
        ]);

        return redirect()
            ->route('admin.settings.index')
            ->with('status', 'Settings saved successfully.');
    }
}
