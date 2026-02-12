<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AdminAuditLog;
use App\Models\ServiceMessage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class ServiceMessageController extends Controller
{
    public function index()
    {
        $messages = ServiceMessage::query()
            ->orderByDesc('id')
            ->paginate(20);

        $now = now();
        $activeCount = ServiceMessage::where('is_active', true)
            ->where(function ($q) use ($now) {
                $q->whereNull('starts_at')->orWhere('starts_at', '<=', $now);
            })
            ->where(function ($q) use ($now) {
                $q->whereNull('ends_at')->orWhere('ends_at', '>=', $now);
            })
            ->count();

        $scheduledCount = ServiceMessage::where('is_active', true)
            ->where('starts_at', '>', $now)
            ->count();

        $expiredCount = ServiceMessage::where(function ($q) use ($now) {
            $q->where('is_active', false)
              ->orWhere(function ($q2) use ($now) {
                  $q2->whereNotNull('ends_at')->where('ends_at', '<', $now);
              });
        })->count();

        return view('admin.service-messages.index', [
            'messages' => $messages,
            'activeCount' => $activeCount,
            'scheduledCount' => $scheduledCount,
            'expiredCount' => $expiredCount,
        ]);
    }

    public function create()
    {
        return view('admin.service-messages.create');
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
            'image' => ['nullable', 'image', 'max:5120'],
            'level' => ['required', 'in:info,warning,alert'],
            'starts_at' => ['nullable', 'date'],
            'ends_at' => ['nullable', 'date', 'after_or_equal:starts_at'],
            'is_active' => ['sometimes', 'boolean'],
        ]);

        $imagePath = null;
        if ($request->hasFile('image')) {
            $imagePath = $this->uploadImage($request->file('image'));
        }

        $message = ServiceMessage::create([
            'title' => $data['title'],
            'body' => $data['body'] ?? null,
            'image_path' => $imagePath,
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

    public function edit(ServiceMessage $serviceMessage)
    {
        return view('admin.service-messages.edit', [
            'message' => $serviceMessage,
        ]);
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
            'image' => ['nullable', 'image', 'max:5120'],
            'level' => ['required', 'in:info,warning,alert'],
            'starts_at' => ['nullable', 'date'],
            'ends_at' => ['nullable', 'date', 'after_or_equal:starts_at'],
            'is_active' => ['sometimes', 'boolean'],
        ]);

        $imagePath = $serviceMessage->image_path;

        if ($request->hasFile('image')) {
            // Delete old image if exists
            if ($serviceMessage->image_path) {
                Storage::disk('s3')->delete($serviceMessage->image_path);
            }
            $imagePath = $this->uploadImage($request->file('image'));
        } elseif ($request->boolean('remove_image')) {
            if ($serviceMessage->image_path) {
                Storage::disk('s3')->delete($serviceMessage->image_path);
            }
            $imagePath = null;
        }

        $serviceMessage->update([
            'title' => $data['title'],
            'body' => $data['body'] ?? null,
            'image_path' => $imagePath,
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

    public function destroy(Request $request, ServiceMessage $serviceMessage)
    {
        if ($serviceMessage->image_path) {
            Storage::disk('s3')->delete($serviceMessage->image_path);
        }

        $this->audit($request, 'service_message.delete', $serviceMessage->id);

        $serviceMessage->delete();

        return redirect()
            ->route('admin.service-messages.index')
            ->with('status', 'Service message deleted.');
    }

    private function uploadImage($file): string
    {
        $filename = 'notices/' . uniqid() . '.' . $file->getClientOriginalExtension();

        Storage::disk('s3')->put(
            $filename,
            file_get_contents($file->getRealPath())
        );

        return $filename;
    }

    public static function imageUrl(?string $path): ?string
    {
        if (! $path) {
            return null;
        }

        return Storage::disk('s3')->url($path);
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
