<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            Admin Dashboard
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    <p class="mb-2">Welcome. This dashboard is the Phase 1 operations hub.</p>
                    <p class="text-sm text-gray-600">Role-based access is enforced for Capitec admins and City reporters.</p>
                </div>
            </div>

            <div class="mt-6 grid grid-cols-1 md:grid-cols-3 gap-4">
                @can('capitec-admin')
                    <a href="{{ route('admin.eligibility.index') }}" class="bg-white shadow sm:rounded-lg p-4 border border-transparent hover:border-slate-300">
                        <h3 class="font-semibold mb-1">Eligibility</h3>
                        <p class="text-sm text-slate-600">Upload employees and manage eligibility.</p>
                    </a>
                    <a href="{{ route('admin.access-requests.index') }}" class="bg-white shadow sm:rounded-lg p-4 border border-transparent hover:border-slate-300">
                        <h3 class="font-semibold mb-1">Access Requests</h3>
                        <p class="text-sm text-slate-600">Approve or decline access requests.</p>
                    </a>
                    <a href="{{ route('admin.schedules.index') }}" class="bg-white shadow sm:rounded-lg p-4 border border-transparent hover:border-slate-300">
                        <h3 class="font-semibold mb-1">Schedules</h3>
                        <p class="text-sm text-slate-600">Configure morning/evening windows.</p>
                    </a>
                @endcan
                <a href="{{ route('admin.reports.index') }}" class="bg-white shadow sm:rounded-lg p-4 border border-transparent hover:border-slate-300">
                    <h3 class="font-semibold mb-1">Reports</h3>
                    <p class="text-sm text-slate-600">Usage, validation, and tracking analytics.</p>
                </a>
                @can('capitec-admin')
                    <a href="{{ route('admin.users.index') }}" class="bg-white shadow sm:rounded-lg p-4 border border-transparent hover:border-slate-300">
                        <h3 class="font-semibold mb-1">Users</h3>
                        <p class="text-sm text-slate-600">Lock/unlock and manage registered users.</p>
                    </a>
                    <a href="{{ route('admin.service-messages.index') }}" class="bg-white shadow sm:rounded-lg p-4 border border-transparent hover:border-slate-300">
                        <h3 class="font-semibold mb-1">Service Messages</h3>
                        <p class="text-sm text-slate-600">Publish service status updates.</p>
                    </a>
                    <a href="{{ route('admin.audit-logs.index') }}" class="bg-white shadow sm:rounded-lg p-4 border border-transparent hover:border-slate-300">
                        <h3 class="font-semibold mb-1">Audit Logs</h3>
                        <p class="text-sm text-slate-600">Review admin actions and exports.</p>
                    </a>
                @endcan
            </div>
        </div>
    </div>
</x-app-layout>
