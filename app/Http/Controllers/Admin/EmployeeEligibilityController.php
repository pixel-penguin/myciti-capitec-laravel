<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AdminAuditLog;
use App\Models\EmployeeEligibility;
use Illuminate\Http\Request;

class EmployeeEligibilityController extends Controller
{
    public function index()
    {
        $employees = EmployeeEligibility::query()
            ->orderByDesc('id')
            ->paginate(25);

        return view('admin.eligibility.index', [
            'employees' => $employees,
        ]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'email' => ['required', 'email'],
            'first_name' => ['nullable', 'string', 'max:100'],
            'last_name' => ['nullable', 'string', 'max:100'],
            'phone' => ['nullable', 'string', 'max:40'],
            'status' => ['required', 'in:active,suspended,left_company'],
        ]);

        $employee = EmployeeEligibility::updateOrCreate(
            ['email' => $data['email']],
            [
                'first_name' => $data['first_name'] ?? null,
                'last_name' => $data['last_name'] ?? null,
                'phone' => $data['phone'] ?? null,
                'status' => $data['status'],
                'source' => 'manual',
                'updated_by' => $request->user()->id,
                'created_by' => $request->user()->id,
            ]
        );

        $this->audit($request, 'eligibility.manual_upsert', $employee->id);

        return redirect()
            ->route('admin.eligibility.index')
            ->with('status', 'Employee eligibility saved.');
    }

    public function upload(Request $request)
    {
        $data = $request->validate([
            'file' => ['required', 'file', 'mimes:csv,txt'],
        ]);

        $handle = fopen($data['file']->getRealPath(), 'r');
        if (! $handle) {
            return redirect()->back()->withErrors(['file' => 'Unable to read file.']);
        }

        $header = fgetcsv($handle);
        $map = $this->normalizeHeader($header);
        $count = 0;

        while (($row = fgetcsv($handle)) !== false) {
            $payload = $this->rowToPayload($row, $map);
            if (! $payload['email']) {
                continue;
            }

            EmployeeEligibility::updateOrCreate(
                ['email' => $payload['email']],
                [
                    'first_name' => $payload['first_name'],
                    'last_name' => $payload['last_name'],
                    'phone' => $payload['phone'],
                    'status' => $payload['status'] ?? 'active',
                    'source' => 'csv',
                    'updated_by' => $request->user()->id,
                    'created_by' => $request->user()->id,
                ]
            );
            $count++;
        }

        fclose($handle);

        $this->audit($request, 'eligibility.csv_upload', null, ['rows' => $count]);

        return redirect()
            ->route('admin.eligibility.index')
            ->with('status', "Uploaded {$count} records.");
    }

    public function updateStatus(Request $request, EmployeeEligibility $employee)
    {
        $data = $request->validate([
            'status' => ['required', 'in:active,suspended,left_company'],
        ]);

        $employee->update([
            'status' => $data['status'],
            'updated_by' => $request->user()->id,
        ]);

        $this->audit($request, 'eligibility.status_update', $employee->id, [
            'status' => $data['status'],
        ]);

        return redirect()
            ->route('admin.eligibility.index')
            ->with('status', 'Status updated.');
    }

    private function normalizeHeader(?array $header): array
    {
        if (! $header) {
            return [];
        }

        $map = [];
        foreach ($header as $index => $value) {
            $key = strtolower(trim($value ?? ''));
            $map[$index] = $key;
        }

        return $map;
    }

    private function rowToPayload(array $row, array $map): array
    {
        $payload = [
            'email' => null,
            'first_name' => null,
            'last_name' => null,
            'phone' => null,
            'status' => null,
        ];

        if ($map === []) {
            $payload['email'] = $row[0] ?? null;
            $payload['first_name'] = $row[1] ?? null;
            $payload['last_name'] = $row[2] ?? null;
            $payload['phone'] = $row[3] ?? null;
            $payload['status'] = $row[4] ?? null;

            return $payload;
        }

        foreach ($row as $index => $value) {
            $column = $map[$index] ?? '';
            $value = is_string($value) ? trim($value) : $value;

            if ($column === 'email') {
                $payload['email'] = $value;
            } elseif (in_array($column, ['first_name', 'firstname'], true)) {
                $payload['first_name'] = $value;
            } elseif (in_array($column, ['last_name', 'surname', 'lastname'], true)) {
                $payload['last_name'] = $value;
            } elseif (in_array($column, ['phone', 'phone_number', 'mobile'], true)) {
                $payload['phone'] = $value;
            } elseif (in_array($column, ['status'], true)) {
                $payload['status'] = $value;
            }
        }

        return $payload;
    }

    private function audit(Request $request, string $action, ?int $targetId, array $metadata = null): void
    {
        AdminAuditLog::create([
            'actor_id' => $request->user()->id,
            'action' => $action,
            'target_type' => EmployeeEligibility::class,
            'target_id' => $targetId,
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'metadata' => $metadata,
        ]);
    }
}
