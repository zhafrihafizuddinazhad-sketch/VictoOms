@if(session('developer_impersonating'))
    <div class="flex flex-wrap items-center justify-between gap-3 border-b border-amber-300 bg-amber-100 px-4 py-3 text-sm text-amber-950" role="status">
        <div><strong><i class="fas fa-user-secret mr-1"></i> Impersonation Mode</strong>
            You are viewing VictoOMS as {{ auth()->user()->name }} — {{ session('developer_impersonated_role') }}.
        </div>
        <form method="POST" action="{{ route('developer.impersonation.stop') }}">
            @csrf
            <button type="submit" class="rounded bg-slate-900 px-3 py-2 font-medium text-white hover:bg-slate-700 focus:outline-none focus:ring-2 focus:ring-slate-500">&larr; Return to Developer</button>
        </form>
    </div>
@endif
