<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AdminAuditLog;
use App\Models\EmployeeEligibility;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class EmployeeEligibilityController extends Controller
{
    public function index(Request $request)
    {
        $query = EmployeeEligibility::query()
            ->with(['registeredUser' => function ($q) {
                $q->select('id', 'employee_eligibility_id', 'employee_id', 'department');
            }]);

        // Search
        if ($search = $request->input('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('email', 'like', "%{$search}%")
                  ->orWhere('first_name', 'like', "%{$search}%")
                  ->orWhere('last_name', 'like', "%{$search}%");
            });
        }

        // Status filter
        if ($status = $request->input('status')) {
            $query->where('status', $status);
        }

        $employees = $query->orderByDesc('id')->paginate(25)->withQueryString();

        // Stats
        $totalEligible = EmployeeEligibility::where('status', 'active')->count();
        $registered = User::whereNotNull('employee_eligibility_id')->count();
        $notRegistered = $totalEligible - $registered;
        $lockedSuspended = EmployeeEligibility::whereIn('status', ['suspended', 'left_company'])->count();

        // Build a map of registered employee_eligibility_ids for badge display
        $registeredEligibilityIds = User::whereNotNull('employee_eligibility_id')
            ->pluck('employee_eligibility_id')
            ->flip()
            ->all();

        return view('admin.eligibility.index', [
            'employees' => $employees,
            'totalEligible' => $totalEligible,
            'registered' => $registered,
            'notRegistered' => max(0, $notRegistered),
            'lockedSuspended' => $lockedSuspended,
            'registeredEligibilityIds' => $registeredEligibilityIds,
            'search' => $search,
            'currentStatus' => $status,
        ]);
    }

    public function downloadTemplate()
    {
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="employee_upload_template.csv"',
        ];

        $content = "email,first_name,last_name,phone,status\n";
        $content .= "jane.doe@example.com,Jane,Doe,0821234567,active\n";

        return response($content, 200, $headers);
    }

    public function create()
    {
        return view('admin.eligibility.create');
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

    public function edit(EmployeeEligibility $employee)
    {
        return view('admin.eligibility.edit', [
            'employee' => $employee,
        ]);
    }

    public function update(Request $request, EmployeeEligibility $employee)
    {
        $data = $request->validate([
            'email' => ['required', 'email', Rule::unique('employee_eligibilities', 'email')->ignore($employee->id)],
            'first_name' => ['nullable', 'string', 'max:100'],
            'last_name' => ['nullable', 'string', 'max:100'],
            'phone' => ['nullable', 'string', 'max:40'],
            'status' => ['required', 'in:active,suspended,left_company'],
        ]);

        $employee->update([
            'email' => $data['email'],
            'first_name' => $data['first_name'] ?? null,
            'last_name' => $data['last_name'] ?? null,
            'phone' => $data['phone'] ?? null,
            'status' => $data['status'],
            'updated_by' => $request->user()->id,
        ]);

        $this->audit($request, 'eligibility.update', $employee->id, [
            'status' => $data['status'],
        ]);

        return redirect()
            ->route('admin.eligibility.index')
            ->with('status', 'Employee updated.');
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

    private function audit(Request $request, string $action, ?int $targetId, ?array $metadata = null): void
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
