<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use App\Services\SystemAudit;

class AccountController extends Controller
{
    public function index(Request $request)
    {
        $status = $request->get('status', 'active');

        $users = User::with('roles')
            ->when(! $request->user()->hasRole('developer'), fn ($query) => $query->whereDoesntHave('roles', fn ($roles) => $roles->where('name', 'developer')))
            ->when(
                in_array($status, ['active', 'inactive', 'archived']),
                function ($query) use ($status) {
                    $query->where('account_status', $status);
                }
            )
            ->orderBy('name')
            ->get();

        return view('accounts.index', compact('users', 'status'));
    }

    public function create()
    {
        $user = auth()->user();

        $roles = [
            'designer',
            'cameraman',
        ];

        if ($user->hasRole('owner')) {
            array_unshift($roles, 'admin');
        }

        if ($user->hasRole('developer')) {
            $roles = ['owner', 'admin', 'designer', 'cameraman', 'developer'];
        }

        return view('accounts.create', compact('roles'));
    }

    public function store(Request $request, SystemAudit $audit)
    {
        $user = auth()->user();

        $allowedRoles = [
            'designer',
            'cameraman',
        ];

        if ($user->hasRole('owner')) {
            $allowedRoles[] = 'admin';
        }

        if ($user->hasRole('developer')) {
            $allowedRoles = ['owner', 'admin', 'designer', 'cameraman', 'developer'];
        }

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => [
                'required',
                'string',
                'lowercase',
                'email',
                'max:255',
                'unique:users,email',
            ],
            'phone' => ['nullable', 'string', 'max:30'],
            'role' => ['required', Rule::in($allowedRoles)],
            'password' => ['required', 'confirmed', 'min:8'],
        ]);

        $newUser = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'phone' => $validated['phone'] ?? null,
            'password' => Hash::make($validated['password']),
            'account_status' => 'active',
        ]);

        $newUser->assignRole($validated['role']);
        $audit->record('account_created', "Created {$validated['role']} account {$newUser->name} ({$newUser->email}).", $user);

        return redirect()
            ->route('accounts.index')
            ->with('success', 'Account created successfully.');
    }

    public function deactivate(User $user, SystemAudit $audit)
    {
        $this->protectDeveloperAccount($user);
        if ($user->id === auth()->id()) {
            return back()->with('error', 'You cannot deactivate your own account.');
        }

        abort_if($user->account_status !== 'active', 422, 'Only active accounts can be deactivated.');
        $this->protectLastActiveDeveloper($user);
        $user->update([
            'account_status' => 'inactive',
        ]);
        $audit->record('account_deactivated', "Deactivated {$user->name} ({$user->email}).", auth()->user());

        return back()->with('success', 'Account deactivated successfully.');
    }

    public function reactivate(User $user, SystemAudit $audit)
    {
        $this->protectDeveloperAccount($user);
        abort_if($user->account_status !== 'inactive', 422, 'Only inactive accounts can be activated.');
        $user->update([
            'account_status' => 'active',
        ]);
        $audit->record('account_activated', "Activated {$user->name} ({$user->email}).", auth()->user());

        return back()->with('success', 'Account reactivated successfully.');
    }

    public function archive(User $user, SystemAudit $audit)
    {
        $this->protectDeveloperAccount($user);
        if ($user->id === auth()->id()) {
            return back()->with('error', 'You cannot archive your own account.');
        }

        abort_if($user->account_status !== 'inactive', 422, 'Only inactive accounts can be archived.');
        $user->update([
            'account_status' => 'archived',
        ]);
        $audit->record('account_archived', "Archived {$user->name} ({$user->email}).", auth()->user());

        return back()->with('success', 'Account archived successfully.');
    }

public function restore(User $user, SystemAudit $audit)
{
    $this->protectDeveloperAccount($user);
    abort_if($user->account_status !== 'archived', 422, 'Only archived accounts can be restored.');
    $user->update([
        'account_status' => 'inactive',
    ]);
    $audit->record('account_restored', "Restored {$user->name} ({$user->email}) to inactive status.", auth()->user());

    return back()->with('success', 'Account restored successfully.');
}

    public function show(User $user)
    {
        $this->protectDeveloperAccount($user);
        return view('accounts.show', ['staff' => $user->load('roles')]);
    }

    public function updateRole(Request $request, User $user, SystemAudit $audit)
    {
        abort_unless($request->user()->hasRole('developer'), 403);
        abort_if($user->id === $request->user()->id, 403, 'You cannot change your own Developer role.');

        $role = $request->validate(['role' => ['required', Rule::in(['owner', 'admin', 'designer', 'cameraman', 'developer'])]])['role'];
        $previous = $user->getRoleNames()->implode(', ');
        if ($user->hasRole('developer') && $role !== 'developer') {
            $this->protectLastActiveDeveloper($user);
        }
        $user->syncRoles([$role]);
        $audit->record('role_changed', "Changed {$user->name} ({$user->email}) role from {$previous} to {$role}.", $request->user());

        return back()->with('success', 'Account role updated successfully.');
    }

    private function protectDeveloperAccount(User $user): void
    {
        abort_if($user->hasRole('developer') && ! auth()->user()?->hasRole('developer'), 403);
    }

    private function protectLastActiveDeveloper(User $user): void
    {
        abort_if(
            $user->hasRole('developer')
                && $user->account_status === 'active'
                && User::role('developer')->where('account_status', 'active')->count() <= 1,
            422,
            'The last active Developer account must remain available.'
        );
    }
}
