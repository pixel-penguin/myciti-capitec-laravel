<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AdminAuditLog;
use App\Models\ServiceMessage;
use Illuminate\Http\Request;

class ServiceMessageController extends Controller
{
    public function index()
    {
        $messages = ServiceMessage::query()
            ->orderByDesc('id')
            ->paginate(20);

        return view('admin.service-messages.index', [
            'messages' => $messages,
        ]);
    }

    public function store(Request $request)
    {
        $request->merge([
            'starts_at' => $request->input('starts_at') ?: null,
            'ends_at' => $request->input('ends_at') ?: null,
        ]);

        $data = $request->validate([
            'title' => ['required', 'string', 'max:150'],
            'body' => ['nullable', 'string', 'max:2000'],
            'level' => ['required', 'in:info,warning,alert'],
            'starts_at' => ['nullable', 'date'],
            'ends_at' => ['nullable', 'date', 'after_or_equal:starts_at'],
            'is_active' => ['sometimes', 'boolean'],
        ]);

        $message = ServiceMessage::create([
            'title' => $data['title'],
            'body' => $data['body'] ?? null,
            'level' => $data['level'],
            'starts_at' => $data['starts_at'] ?? null,
            'ends_at' => $data['ends_at'] ?? null,
            'is_active' => $request->boolean('is_active', true),
            'created_by' => $request->user()->id,
            'updated_by' => $request->user()->id,
        ]);

        $this->audit($request, 'service_message.create', $message->id);

        return redirect()
            ->route('admin.service-messages.index')
            ->with('status', 'Service message created.');
    }

    public function update(Request $request, ServiceMessage $serviceMessage)
    {
        $request->merge([
            'starts_at' => $request->input('starts_at') ?: null,
            'ends_at' => $request->input('ends_at') ?: null,
        ]);

        $data = $request->validate([
            'title' => ['required', 'string', 'max:150'],
            'body' => ['nullable', 'string', 'max:2000'],
            'level' => ['required', 'in:info,warning,alert'],
            'starts_at' => ['nullable', 'date'],
            'ends_at' => ['nullable', 'date', 'after_or_equal:starts_at'],
            'is_active' => ['sometimes', 'boolean'],
        ]);

        $serviceMessage->update([
            'title' => $data['title'],
            'body' => $data['body'] ?? null,
            'level' => $data['level'],
            'starts_at' => array_key_exists('starts_at', $data) ? $data['starts_at'] : $serviceMessage->starts_at,
            'ends_at' => array_key_exists('ends_at', $data) ? $data['ends_at'] : $serviceMessage->ends_at,
            'is_active' => $request->boolean('is_active', $serviceMessage->is_active),
            'updated_by' => $request->user()->id,
        ]);

        $this->audit($request, 'service_message.update', $serviceMessage->id);

        return redirect()
            ->route('admin.service-messages.index')
            ->with('status', 'Service message updated.');
    }

    private function audit(Request $request, string $action, int $targetId): void
    {
        AdminAuditLog::create([
            'actor_id' => $request->user()->id,
            'action' => $action,
            'target_type' => ServiceMessage::class,
            'target_id' => $targetId,
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
        ]);
    }
}
