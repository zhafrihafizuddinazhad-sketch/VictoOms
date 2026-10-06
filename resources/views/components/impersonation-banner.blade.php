@if(session('developer_impersonating'))
    <div class="impersonation-banner" role="status" aria-live="polite">
        <div class="impersonation-banner__message"><strong><i class="fas fa-user-secret mr-1"></i> Impersonation Mode</strong>
            <span>You are viewing VictoOMS as {{ auth()->user()->name }} — {{ ucfirst(session('developer_impersonated_role')) }}.</span>
        </div>
        <form method="POST" action="{{ route('developer.impersonation.stop') }}">
            @csrf
            <button type="submit" class="btn btn-dark"><i class="fas fa-arrow-left mr-1"></i> Return to Developer</button>
        </form>
    </div>
@endif
