<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AdminAuditLog;

class AuditLogController extends Controller
{
    public function index()
    {
        $logs = AdminAuditLog::query()
            ->with('actor')
            ->orderByDesc('id')
            ->paginate(30);

        return view('admin.audit-logs.index', [
            'logs' => $logs,
        ]);
    }
}
