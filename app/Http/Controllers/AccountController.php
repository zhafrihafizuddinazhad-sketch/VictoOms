<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class AccountController extends Controller
{
    public function index(Request $request)
    {
        $status = $request->get('status', 'active');

        $users = User::with('roles')
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

        return view('accounts.create', compact('roles'));
    }

    public function store(Request $request)
    {
        $user = auth()->user();

        $allowedRoles = [
            'designer',
            'cameraman',
        ];

        if ($user->hasRole('owner')) {
            $allowedRoles[] = 'admin';
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

        return redirect()
            ->route('accounts.index')
            ->with('success', 'Account created successfully.');
    }

    public function deactivate(User $user)
    {
        if ($user->id === auth()->id()) {
            return back()->with('error', 'You cannot deactivate your own account.');
        }

        $user->update([
            'account_status' => 'inactive',
        ]);

        return back()->with('success', 'Account deactivated successfully.');
    }

    public function reactivate(User $user)
    {
        $user->update([
            'account_status' => 'active',
        ]);

        return back()->with('success', 'Account reactivated successfully.');
    }

    public function archive(User $user)
    {
        if ($user->id === auth()->id()) {
            return back()->with('error', 'You cannot archive your own account.');
        }

        $user->update([
            'account_status' => 'archived',
        ]);

        return back()->with('success', 'Account archived successfully.');
    }

    public function restore(User $user)
{
    $user->update([
        'account_status' => 'inactive',
    ]);

    return back()->with('success', 'Account restored successfully.');
}
}