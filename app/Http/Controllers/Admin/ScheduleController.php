<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Schedule;
use Illuminate\Http\Request;

class ScheduleController extends Controller
{
    public function index()
    {
        $schedules = Schedule::query()
            ->orderBy('starts_at')
            ->get();

        return view('admin.schedules.index', [
            'schedules' => $schedules,
        ]);
    }

    public function create()
    {
        return view('admin.schedules.create');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:80'],
            'starts_at' => ['required', 'date_format:H:i'],
            'ends_at' => ['required', 'date_format:H:i'],
            'active' => ['sometimes', 'boolean'],
        ]);

        Schedule::create([
            'name' => $data['name'],
            'starts_at' => $data['starts_at'],
            'ends_at' => $data['ends_at'],
            'active' => $request->boolean('active'),
        ]);

        return redirect()
            ->route('admin.schedules.index')
            ->with('status', 'Schedule created.');
    }

    public function edit(Schedule $schedule)
    {
        return view('admin.schedules.edit', [
            'schedule' => $schedule,
        ]);
    }

    public function update(Request $request, Schedule $schedule)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:80'],
            'starts_at' => ['required', 'date_format:H:i'],
            'ends_at' => ['required', 'date_format:H:i'],
            'active' => ['sometimes', 'boolean'],
        ]);

        $schedule->update([
            'name' => $data['name'],
            'starts_at' => $data['starts_at'],
            'ends_at' => $data['ends_at'],
            'active' => $request->boolean('active'),
        ]);

        return redirect()
            ->route('admin.schedules.index')
            ->with('status', 'Schedule updated.');
    }
}
