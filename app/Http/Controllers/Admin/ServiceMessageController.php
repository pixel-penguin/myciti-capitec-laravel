<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AdminAuditLog;
use App\Models\ServiceMessage;
use Aws\S3\S3Client;
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

    public function presignedUpload(Request $request)
    {
        $request->validate([
            'content_type' => ['required', 'string', 'regex:/^image\//'],
            'filename' => ['required', 'string'],
        ]);

        $extension = pathinfo($request->input('filename'), PATHINFO_EXTENSION) ?: 'jpg';
        $key = 'notices/' . uniqid() . '.' . $extension;

        /** @var S3Client $client */
        $client = Storage::disk('s3')->getClient();
        $bucket = config('filesystems.disks.s3.bucket');

        $command = $client->getCommand('PutObject', [
            'Bucket' => $bucket,
            'Key' => $key,
            'ContentType' => $request->input('content_type'),
        ]);

        $presigned = $client->createPresignedRequest($command, '+10 minutes');

        return response()->json([
            'url' => (string) $presigned->getUri(),
            'key' => $key,
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
            'image_key' => ['nullable', 'string', 'regex:/^notices\//'],
            'level' => ['required', 'in:info,warning,alert'],
            'starts_at' => ['nullable', 'date'],
            'ends_at' => ['nullable', 'date', 'after_or_equal:starts_at'],
            'is_active' => ['sometimes', 'boolean'],
        ]);

        $message = ServiceMessage::create([
            'title' => $data['title'],
            'body' => $data['body'] ?? null,
            'image_path' => $data['image_key'] ?? null,
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
            'image_key' => ['nullable', 'string', 'regex:/^notices\//'],
            'level' => ['required', 'in:info,warning,alert'],
            'starts_at' => ['nullable', 'date'],
            'ends_at' => ['nullable', 'date', 'after_or_equal:starts_at'],
            'is_active' => ['sometimes', 'boolean'],
        ]);

        $imagePath = $serviceMessage->image_path;

        if (! empty($data['image_key'])) {
            // New image uploaded via presigned URL — delete old one
            if ($serviceMessage->image_path) {
                Storage::disk('s3')->delete($serviceMessage->image_path);
            }
            $imagePath = $data['image_key'];
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
            'is_active' => $request->boolean('is_active'),
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
